-- Fotos das cientistas: arquivo (no volume de fotos) e crédito/licença.
ALTER TABLE cientistas
    ADD COLUMN foto VARCHAR(80) NULL AFTER fonte,
    ADD COLUMN foto_credito VARCHAR(300) NULL AFTER foto,
    ADD COLUMN foto_fonte VARCHAR(300) NULL AFTER foto_credito;
