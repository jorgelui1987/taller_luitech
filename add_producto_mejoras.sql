ALTER TABLE productos ADD COLUMN proveedor_id BIGINT UNSIGNED NULL AFTER marca_id;
ALTER TABLE productos ADD COLUMN codigo_barras VARCHAR(100) NULL AFTER codigo;
ALTER TABLE productos ADD COLUMN garantia_dias INT NULL DEFAULT 0 AFTER descripcion;
ALTER TABLE productos ADD FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL;