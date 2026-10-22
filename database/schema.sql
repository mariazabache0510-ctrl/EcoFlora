SET client_encoding TO 'UTF8';

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    usuario VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(30),
    direccion VARCHAR(255),
    ciudad VARCHAR(100),
    rol VARCHAR(20) NOT NULL DEFAULT 'cliente',
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    icono VARCHAR(80)
);

CREATE TABLE IF NOT EXISTS plantas (
    id_planta SERIAL PRIMARY KEY,
    id_categoria INTEGER NOT NULL REFERENCES categorias (id_categoria),
    nombre VARCHAR(150) NOT NULL,
    nombre_cientifico VARCHAR(150),
    descripcion TEXT,
    cuidados TEXT,
    precio NUMERIC(12, 2) NOT NULL DEFAULT 0,
    imagen VARCHAR(255),
    stock INTEGER NOT NULL DEFAULT 0,
    destacada SMALLINT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS carrito (
    id_carrito SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    id_planta INTEGER NOT NULL REFERENCES plantas (id_planta) ON DELETE CASCADE,
    cantidad INTEGER NOT NULL DEFAULT 1,
    UNIQUE (id_usuario, id_planta)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuarios (id_usuario),
    total NUMERIC(12, 2) NOT NULL,
    direccion_envio VARCHAR(255) NOT NULL,
    ciudad_envio VARCHAR(100),
    telefono_contacto VARCHAR(30),
    notas TEXT,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pedido_detalle (
    id_detalle SERIAL PRIMARY KEY,
    id_pedido INTEGER NOT NULL REFERENCES pedidos (id_pedido) ON DELETE CASCADE,
    id_planta INTEGER NOT NULL REFERENCES plantas (id_planta),
    cantidad INTEGER NOT NULL,
    precio_unitario NUMERIC(12, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS contactos (
    id_contacto SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30),
    asunto VARCHAR(150),
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS historial_navegacion (
    id_historial SERIAL PRIMARY KEY,
    id_usuario INTEGER REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    ip_address VARCHAR(45),
    pagina VARCHAR(150),
    url TEXT,
    fecha_visita TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_historial_usuario ON historial_navegacion (id_usuario, fecha_visita DESC);
CREATE INDEX IF NOT EXISTS idx_pedidos_usuario ON pedidos (id_usuario, fecha_pedido DESC);
CREATE INDEX IF NOT EXISTS idx_carrito_usuario ON carrito (id_usuario);

INSERT INTO usuarios (nombre, usuario, email, password, telefono, direccion, ciudad, rol)
SELECT
    'Administrador',
    'admin',
    'admin@ecoflora.local',
    '$2y$10$RiS6Iaz5zsOl.svy0PS.seuoRUrb5GbqAdIuDS1fhNRau.M64.zMm',
    '3053344789',
    'Calle 123 #45-67',
    'Villavicencio',
    'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE usuario = 'admin' OR email = 'admin@ecoflora.local'
);

INSERT INTO categorias (nombre, icono)
SELECT v.nombre, v.icono
FROM (
    VALUES
        ('Interior', 'bi-house'),
        ('Exterior', 'bi-tree'),
        ('Suculentas', 'bi-brightness-high'),
        ('Aromáticas', 'bi-flower1'),
        ('Floración', 'bi-flower2')
) AS v(nombre, icono)
WHERE NOT EXISTS (SELECT 1 FROM categorias);

INSERT INTO plantas (
    id_categoria,
    nombre,
    nombre_cientifico,
    descripcion,
    cuidados,
    precio,
    imagen,
    stock,
    destacada
)
SELECT
    c.id_categoria,
    v.nombre,
    v.nombre_cientifico,
    v.descripcion,
    v.cuidados,
    v.precio::numeric,
    v.imagen,
    v.stock::integer,
    v.destacada::smallint
FROM (
    VALUES
        (
            'Interior',
            'Monstera Deliciosa',
            'Monstera deliciosa',
            'Planta de interior con hojas grandes y fenestradas. Ideal para espacios luminosos de la casa.',
            'Luz indirecta, riego cuando el sustrato esté seco y humedad ambiental media.',
            45000,
            'monstera.jpg',
            12,
            1
        ),
        (
            'Interior',
            'Lengua de suegra',
            'Sansevieria trifasciata',
            'Muy resistente y de bajo mantenimiento. Purifica el ambiente y tolera poca luz.',
            'Riego escaso y luz indirecta. Evita encharcar el sustrato.',
            28000,
            'sansevieria.jpg',
            20,
            1
        ),
        (
            'Exterior',
            'Helecho Boston',
            'Nephrolepis exaltata',
            'Follaje frondoso para balcones y jardines con sombra parcial.',
            'Mantén el sustrato húmedo y protégelo del sol directo.',
            22000,
            'helecho.jpg',
            15,
            0
        ),
        (
            'Suculentas',
            'Echeveria',
            'Echeveria elegans',
            'Suculenta de roseta compacta, perfecta para macetas pequeñas.',
            'Sol directo suave y riego solo cuando el sustrato esté completamente seco.',
            15000,
            'echeveria.jpg',
            30,
            1
        ),
        (
            'Aromáticas',
            'Lavanda',
            'Lavandula angustifolia',
            'Aromática de flores violetas. Atrae polinizadores y perfuma el jardín.',
            'Sol pleno, suelo bien drenado y poda después de la floración.',
            18000,
            'lavanda.jpg',
            18,
            0
        ),
        (
            'Floración',
            'Orquídea Phalaenopsis',
            'Phalaenopsis amabilis',
            'Floración prolongada para interiores luminosos.',
            'Luz indirecta, riego semanal y buena ventilación en las raíces.',
            65000,
            'orquidea.jpg',
            8,
            1
        )
) AS v(
    categoria,
    nombre,
    nombre_cientifico,
    descripcion,
    cuidados,
    precio,
    imagen,
    stock,
    destacada
)
JOIN categorias c ON c.nombre = v.categoria
WHERE NOT EXISTS (SELECT 1 FROM plantas);
