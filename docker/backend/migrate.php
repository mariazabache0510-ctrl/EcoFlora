<?php

require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '5432');
$database = env('DB_DATABASE', 'ecoflora_db');
$username = env('DB_USERNAME', 'postgres');
$password = env('DB_PASSWORD', 'postgres');
$dsn = "pgsql:host={$host};port={$port};dbname={$database}";

$pdo = null;
$lastError = 'sin intento de conexión';

for ($attempt = 1; $attempt <= 30; $attempt++) {
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        break;
    } catch (PDOException $e) {
        $lastError = $e->getMessage();
        fwrite(STDERR, "Esperando la base de datos ({$attempt}/30): {$lastError}\n");
        sleep(2);
    }
}

if (!$pdo instanceof PDO) {
    fwrite(STDERR, "No se pudo conectar a PostgreSQL: {$lastError}\n");
    exit(1);
}

$schemaPath = dirname(__DIR__, 2) . '/database/schema.sql';
$sql = file_get_contents($schemaPath);

if ($sql === false) {
    fwrite(STDERR, "No se encontró el esquema en {$schemaPath}\n");
    exit(1);
}

$statements = splitSqlStatements($sql);

try {
    $pdo->beginTransaction();

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Error al aplicar el esquema: " . $e->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "Esquema de EcoFlora aplicado.\n");

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $inSingleQuote = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if (!$inSingleQuote && $char === '-' && $next === '-') {
            $lineEnd = strpos($sql, "\n", $i);
            $i = $lineEnd === false ? $length : $lineEnd;
            continue;
        }

        if ($char === "'") {
            if ($inSingleQuote && $next === "'") {
                $buffer .= "''";
                $i++;
                continue;
            }

            $inSingleQuote = !$inSingleQuote;
            $buffer .= $char;
            continue;
        }

        if (!$inSingleQuote && $char === ';') {
            $statement = trim($buffer);

            if ($statement !== '') {
                $statements[] = $statement;
            }

            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $tail = trim($buffer);

    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}
