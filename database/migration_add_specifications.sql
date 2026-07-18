-- migration_add_specifications.sql
-- Run this ONLY if you already imported thesparks_schema.sql before and have
-- existing data. It safely adds the new `specifications` column to the
-- `products` table without touching any existing rows or data.
--
-- Usage: mysql -u root thesparks_db < migration_add_specifications.sql
-- (or run it inside phpMyAdmin's SQL tab)

ALTER TABLE products
    ADD COLUMN specifications TEXT NULL AFTER description;
