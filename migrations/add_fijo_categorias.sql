-- Agrega columna "fijo" para fijar categorías destacadas en el inicio
ALTER TABLE categorias ADD COLUMN fijo TINYINT(1) NOT NULL DEFAULT 0;
