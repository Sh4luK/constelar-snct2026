-- Temas de pesquisa (relação N:N com cientistas) e índices para a busca do acervo.

CREATE TABLE IF NOT EXISTS temas (
    id    SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome  VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cientista_tema (
    cientista_id INT UNSIGNED NOT NULL,
    tema_id      SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (cientista_id, tema_id),
    INDEX idx_tema (tema_id),
    CONSTRAINT fk_ct_cientista FOREIGN KEY (cientista_id) REFERENCES cientistas(id) ON DELETE CASCADE,
    CONSTRAINT fk_ct_tema      FOREIGN KEY (tema_id)      REFERENCES temas(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE cientistas ADD INDEX idx_nome (nome), ADD INDEX idx_criado (criado_em);

INSERT IGNORE INTO temas (nome) VALUES
('Pioneirismo'), ('Prêmios internacionais'), ('Universo'), ('Saúde'), ('Genética'),
('Agricultura e ambiente'), ('Biodiversidade'), ('Materiais'), ('Direitos e sociedade'),
('Educação'), ('Arte e cultura'), ('Divulgação científica');

INSERT IGNORE INTO cientista_tema (cientista_id, tema_id)
SELECT c.id, t.id
FROM (
    SELECT 'Duília de Mello' AS n, 'Universo' AS t UNION ALL
    SELECT 'Duília de Mello', 'Divulgação científica' UNION ALL
    SELECT 'Duília de Mello', 'Educação' UNION ALL
    SELECT 'Beatriz Barbuy', 'Universo' UNION ALL
    SELECT 'Beatriz Barbuy', 'Prêmios internacionais' UNION ALL
    SELECT 'Thaisa Storchi Bergmann', 'Universo' UNION ALL
    SELECT 'Thaisa Storchi Bergmann', 'Prêmios internacionais' UNION ALL
    SELECT 'Rosaly Lopes', 'Universo' UNION ALL
    SELECT 'Márcia Barbosa', 'Prêmios internacionais' UNION ALL
    SELECT 'Márcia Barbosa', 'Direitos e sociedade' UNION ALL
    SELECT 'Sonia Guimarães', 'Pioneirismo' UNION ALL
    SELECT 'Sonia Guimarães', 'Materiais' UNION ALL
    SELECT 'Sonia Guimarães', 'Direitos e sociedade' UNION ALL
    SELECT 'Elisa Frota-Pessoa', 'Pioneirismo' UNION ALL
    SELECT 'Sonja Ashauer', 'Pioneirismo' UNION ALL
    SELECT 'Jaqueline Goes de Jesus', 'Saúde' UNION ALL
    SELECT 'Jaqueline Goes de Jesus', 'Genética' UNION ALL
    SELECT 'Johanna Döbereiner', 'Agricultura e ambiente' UNION ALL
    SELECT 'Nise da Silveira', 'Saúde' UNION ALL
    SELECT 'Nise da Silveira', 'Pioneirismo' UNION ALL
    SELECT 'Nise da Silveira', 'Arte e cultura' UNION ALL
    SELECT 'Bertha Lutz', 'Biodiversidade' UNION ALL
    SELECT 'Bertha Lutz', 'Direitos e sociedade' UNION ALL
    SELECT 'Mayana Zatz', 'Saúde' UNION ALL
    SELECT 'Mayana Zatz', 'Genética' UNION ALL
    SELECT 'Mayana Zatz', 'Prêmios internacionais' UNION ALL
    SELECT 'Vanderlan Bolzani', 'Biodiversidade' UNION ALL
    SELECT 'Vanderlan Bolzani', 'Saúde' UNION ALL
    SELECT 'Eloisa Mano', 'Materiais' UNION ALL
    SELECT 'Eloisa Mano', 'Pioneirismo' UNION ALL
    SELECT 'Eloisa Mano', 'Agricultura e ambiente' UNION ALL
    SELECT 'Blanka Wladislaw', 'Pioneirismo' UNION ALL
    SELECT 'Yvonne Mascarenhas', 'Materiais' UNION ALL
    SELECT 'Yvonne Mascarenhas', 'Pioneirismo' UNION ALL
    SELECT 'Niède Guidon', 'Arte e cultura' UNION ALL
    SELECT 'Niède Guidon', 'Agricultura e ambiente' UNION ALL
    SELECT 'Lélia Gonzalez', 'Direitos e sociedade' UNION ALL
    SELECT 'Lélia Gonzalez', 'Arte e cultura' UNION ALL
    SELECT 'Ruth Cardoso', 'Direitos e sociedade' UNION ALL
    SELECT 'Carolina Bori', 'Pioneirismo' UNION ALL
    SELECT 'Carolina Bori', 'Saúde' UNION ALL
    SELECT 'Maria Laura Mouzinho Leite Lopes', 'Educação' UNION ALL
    SELECT 'Maria Laura Mouzinho Leite Lopes', 'Pioneirismo' UNION ALL
    SELECT 'Marília Chaves Peixoto', 'Pioneirismo' UNION ALL
    SELECT 'Enedina Alves Marques', 'Pioneirismo' UNION ALL
    SELECT 'Enedina Alves Marques', 'Direitos e sociedade' UNION ALL
    SELECT 'Carolina Araujo', 'Prêmios internacionais' UNION ALL
    SELECT 'Carolina Araujo', 'Direitos e sociedade'
) m
JOIN cientistas c ON c.nome = m.n
JOIN temas t ON t.nome = m.t;
