USE precios_comparados;

-- 1. CITY
CREATE TABLE IF NOT EXISTS city (
  city_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  region VARCHAR(200) NULL,
  country VARCHAR(2) NOT NULL DEFAULT 'ES',
  CONSTRAINT uq_city UNIQUE (name, country, region)
);

-- 2. CHAIN (Cadena: Mercadona, DIA)
CREATE TABLE IF NOT EXISTS chain (
  chain_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL UNIQUE
);

-- 3. STORE (Tienda física o online)
CREATE TABLE IF NOT EXISTS store (
  store_id INT AUTO_INCREMENT PRIMARY KEY,
  chain_id INT NOT NULL,
  city_id INT, -- Puede ser NULL si es online genérico
  name VARCHAR(200) NULL,
  address VARCHAR(300) NULL,
  latitude FLOAT NULL,
  longitude FLOAT NULL,
  FOREIGN KEY (chain_id) REFERENCES chain(chain_id),
  FOREIGN KEY (city_id) REFERENCES city(city_id),
  CONSTRAINT uq_store UNIQUE (chain_id, city_id, name, address)
);

-- 4. CATEGORY
CREATE TABLE IF NOT EXISTS category (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL UNIQUE,
  parent_id INT NULL,
  FOREIGN KEY (parent_id) REFERENCES category(category_id)
);

-- 5. UOM (Unidades de Medida)
CREATE TABLE IF NOT EXISTS uom (
  uom_code VARCHAR(20) PRIMARY KEY,
  to_base DECIMAL(18,6) NOT NULL,
  base_type VARCHAR(10) NOT NULL,
  CONSTRAINT ck_uom_base_type CHECK (base_type IN ('mass', 'volume', 'unit'))
);

-- 6. PRODUCT (Producto Maestro)
CREATE TABLE IF NOT EXISTS product (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  ean VARCHAR(50) NULL,
  name VARCHAR(300) NOT NULL,
  brand VARCHAR(200) NULL,
  category_id INT NULL,
  uom_code VARCHAR(20) NOT NULL,
  pack_qty DECIMAL(18,6) NOT NULL DEFAULT 1,
  is_private_label BOOLEAN NOT NULL DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES category(category_id),
  FOREIGN KEY (uom_code) REFERENCES uom(uom_code),
  CONSTRAINT uq_product UNIQUE (ean, name, brand, uom_code, pack_qty)
);

-- 7. PRICE_OBSERVATION (Precios)
CREATE TABLE IF NOT EXISTS price_observation (
  price_obs_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  store_id INT NOT NULL,
  price_total DECIMAL(10,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'EUR',
  observed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  source VARCHAR(200) NULL,
  FOREIGN KEY (product_id) REFERENCES product(product_id),
  FOREIGN KEY (store_id) REFERENCES store(store_id)
);

-- VISTA: Precio Enriquecido (Calcula precio por unidad base)
CREATE OR REPLACE VIEW price_obs_enriched AS
SELECT
  po.price_obs_id,
  po.product_id,
  po.store_id,
  po.price_total,
  po.currency,
  po.observed_at,
  po.source,
  p.uom_code,
  p.pack_qty,
  u.base_type,
  u.to_base,
  CASE
    WHEN u.base_type IN ('mass', 'volume')
      THEN po.price_total / NULLIF(p.pack_qty * u.to_base, 0)
    ELSE po.price_total / NULLIF(p.pack_qty, 0)
  END AS price_per_base
FROM price_observation po
JOIN product p ON p.product_id = po.product_id
JOIN uom u ON u.uom_code = p.uom_code;