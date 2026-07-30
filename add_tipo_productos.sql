-- Agregar columna tipo a productos (para diferenciar celular, accesorio, otro)
ALTER TABLE productos ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'celular' AFTER id;