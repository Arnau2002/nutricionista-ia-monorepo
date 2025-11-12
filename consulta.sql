USE precios_comparados;
-- Tabla 1: El índice universal que usará la IA
-- Almacena los productos de forma genérica, sin marcas ni tiendas.
CREATE TABLE Maestro_Estandar (
    id_maestro INT AUTO_INCREMENT PRIMARY KEY,variable
    nombre_estandar VARCHAR(255) NOT NULL UNIQUE,
    categoria VARCHAR(100),
    unidad_base VARCHAR(20) -- ej. 'kg', 'litro', 'unidad'
);

-- Tabla 2: El comparador de precios
-- Almacena los precios específicos de cada tienda, vinculados al maestro.
CREATE TABLE Precios_Tiendas (
    id_precio INT AUTO_INCREMENT PRIMARY KEY,
    id_maestro INT NOT NULL,           -- Clave foránea que apunta a Maestro_Estandar
    tienda VARCHAR(50) NOT NULL,       -- ej. 'Mercadona', 'DIA'
    nombre_tienda VARCHAR(255),        -- El nombre real del producto en esa tienda
    precio_actual DECIMAL(10, 2),      -- El precio del paquete/unidad
    precio_por_unidad_base DECIMAL(10, 2), -- El precio normalizado (ej. €/kg)
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clave foránea para asegurar la integridad de los datos
    FOREIGN KEY (id_maestro) REFERENCES Maestro_Estandar(id_maestro)
);