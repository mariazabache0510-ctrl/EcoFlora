<?php

class CartService
{
    public function __construct(private PDO $connection)
    {
    }

    public function getItemCount(int $userId): int
    {
        $stmt = $this->connection->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }
}
