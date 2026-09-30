-- Permite que una categoría tenga una categoría padre (un solo nivel de anidado).
-- Una categoría con categoria_padre_id = NULL es de nivel superior.
-- Una categoría con categoria_padre_id definido es una subcategoría.
ALTER TABLE categorias
  ADD COLUMN categoria_padre_id INT UNSIGNED NULL AFTER id,
  ADD KEY idx_cat_padre (categoria_padre_id),
  ADD CONSTRAINT fk_cat_padre FOREIGN KEY (categoria_padre_id)
      REFERENCES categorias (id) ON DELETE CASCADE ON UPDATE CASCADE;
