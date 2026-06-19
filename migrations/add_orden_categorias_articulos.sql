-- Agrega columna "orden" para controlar el orden de visualización en el índice
ALTER TABLE categorias ADD COLUMN orden SMALLINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE articulos  ADD COLUMN orden SMALLINT UNSIGNED NOT NULL DEFAULT 0;

-- Inicializar orden según fecha de creación existente (más nuevo = orden más bajo = aparece primero)
SET @rank := 0;
UPDATE categorias SET orden = (@rank := @rank + 1) ORDER BY creado_en DESC, id DESC;

SET @rank := 0;
UPDATE articulos SET orden = (@rank := @rank + 1) ORDER BY creado_en DESC, id DESC;
