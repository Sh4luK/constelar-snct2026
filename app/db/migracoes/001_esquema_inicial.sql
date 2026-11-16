SET NAMES utf8mb4;

CREATE TABLE areas (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(80)  NOT NULL UNIQUE,
    constelacao VARCHAR(80)  NOT NULL,
    descricao   VARCHAR(500) NOT NULL,
    cor         CHAR(7)      NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(120) NOT NULL,
    email      VARCHAR(190) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    papel      ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cientistas (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(150)  NOT NULL,
    area_id          SMALLINT UNSIGNED NOT NULL,
    instituicao      VARCHAR(200)  NULL,
    biografia        VARCHAR(2000) NOT NULL,
    curiosidade      VARCHAR(500)  NULL,
    fonte            VARCHAR(500)  NOT NULL,
    -- LGPD: só exibe com autorização registrada (ou dado público de pessoa falecida, validado pelo admin).
    autorizacao_uso  TINYINT(1)    NOT NULL DEFAULT 0,
    status           ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
    enviado_por      INT UNSIGNED  NULL,
    revisado_por     INT UNSIGNED  NULL,
    revisado_em      TIMESTAMP     NULL,
    criado_em        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cient_area    FOREIGN KEY (area_id)      REFERENCES areas(id),
    CONSTRAINT fk_cient_envio   FOREIGN KEY (enviado_por)  REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_cient_revisor FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_publicadas (status, autorizacao_uso, area_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tentativas_login (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip        VARBINARY(16) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_tempo (ip, criado_em)
) ENGINE=InnoDB;

INSERT INTO areas (nome, constelacao, descricao, cor) VALUES
('Astronomia',            'Constelação do Telescópio', 'Estudo dos astros, galáxias e do universo.',                   '#7dd3fc'),
('Física',                'Constelação do Átomo',      'Estudo da matéria, da energia e de suas interações.',          '#c4b5fd'),
('Ciências da Vida',      'Constelação da Célula',     'Biologia, saúde e agricultura: o estudo dos seres vivos.',     '#86efac'),
('Química',               'Constelação do Frasco',     'Estudo das substâncias e de suas transformações.',             '#fcd34d'),
('Ciências Humanas',      'Constelação da Pintura',    'Arqueologia, história e o estudo das sociedades humanas.',     '#fda4af');

-- Dados de demonstração compilados de fontes públicas. Em produção, registrar a autorização de cada cientista viva.
INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status) VALUES
('Duília de Mello', 1, 'Catholic University of America / NASA Goddard',
 'Astrônoma nascida em Jundiaí (SP), conhecida como "Mulher das Estrelas". Em 1997 descobriu a supernova SN 1997D. Colabora com o centro Goddard da NASA em pesquisas sobre galáxias e se dedica à divulgação científica para incentivar crianças e jovens a seguir carreira em ciência.',
 'Junto com colegas, identificou as "bolhas azuis": aglomerados de estrelas jovens nascidos fora das galáxias, em meio a gás arrancado por colisões galácticas.',
 'NASA, Imagine the Universe! (imagine.gsfc.nasa.gov); CatholicU Magazine (communications.catholic.edu); Revista Pesquisa FAPESP', 1, 'aprovado'),
('Beatriz Barbuy', 1, 'Universidade de São Paulo (USP)',
 'Astrofísica e professora da USP. Estuda a composição química das estrelas mais antigas da Via Láctea para entender como a nossa galáxia se formou.',
 'Recebeu em 2009 o prêmio L''Oréal-UNESCO Para Mulheres na Ciência.',
 'Instituto de Astronomia, Geofísica e Ciências Atmosféricas da USP', 1, 'aprovado'),
('Márcia Barbosa', 2, 'Universidade Federal do Rio Grande do Sul (UFRGS)',
 'Física e professora da UFRGS. Pesquisa as propriedades incomuns da água, que se comporta de forma diferente da maioria dos líquidos, com aplicações em temas como dessalinização.',
 'Recebeu em 2013 o prêmio L''Oréal-UNESCO Para Mulheres na Ciência e atua pela participação de mulheres na física.',
 'Instituto de Física da UFRGS', 1, 'aprovado'),
('Sonia Guimarães', 2, 'Instituto Tecnológico de Aeronáutica (ITA)',
 'Física e professora do ITA. Foi a primeira mulher negra brasileira a obter doutorado em Física, concluído em 1989 na Inglaterra. Pesquisa materiais semicondutores usados em sensores.',
 'Também foi uma das primeiras professoras negras do ITA e atua no combate ao racismo na ciência.',
 'Instituto Tecnológico de Aeronáutica', 1, 'aprovado'),
('Jaqueline Goes de Jesus', 3, 'Universidade de São Paulo (USP)',
 'Biomédica baiana. Em 2020 integrou a equipe que sequenciou o genoma do novo coronavírus no Brasil cerca de 48 horas após a confirmação do primeiro caso no país.',
 'Virou inspiração para uma boneca Barbie da série que homenageia mulheres inspiradoras.',
 'Instituto de Medicina Tropical da USP', 1, 'aprovado'),
('Johanna Döbereiner', 3, 'Embrapa',
 'Agrônoma nascida na antiga Tchecoslováquia e naturalizada brasileira. Pesquisou a fixação biológica de nitrogênio por bactérias, o que permitiu cultivar soja no Brasil usando muito menos adubo químico.',
 'Seu trabalho trouxe grande economia para a agricultura brasileira e reduziu impactos ambientais.',
 'Embrapa Agrobiologia', 1, 'aprovado'),
('Nise da Silveira', 3, 'Museu de Imagens do Inconsciente',
 'Psiquiatra alagoana, uma das primeiras mulheres a se formar em medicina no Brasil. Recusou tratamentos agressivos da época e usou a arte como forma de cuidado de pessoas com transtornos mentais.',
 'Fundou no Rio de Janeiro o Museu de Imagens do Inconsciente, com obras criadas por pacientes.',
 'Museu de Imagens do Inconsciente', 1, 'aprovado'),
('Vanderlan Bolzani', 4, 'Universidade Estadual Paulista (UNESP)',
 'Química e professora da UNESP em Araraquara. Pesquisa substâncias produzidas por plantas da biodiversidade brasileira que podem dar origem a novos medicamentos.',
 'Presidiu a Sociedade Brasileira de Química e defende o uso sustentável da biodiversidade.',
 'Instituto de Química da UNESP', 1, 'aprovado'),
('Niède Guidon', 5, 'Fundação Museu do Homem Americano',
 'Arqueóloga paulista. Liderou as pesquisas na Serra da Capivara, no Piauí, região com milhares de pinturas rupestres, e lutou pela criação do Parque Nacional da Serra da Capivara.',
 'O parque é Patrimônio Mundial da UNESCO e reúne uma das maiores concentrações de arte rupestre das Américas.',
 'Fundação Museu do Homem Americano (FUMDHAM)', 1, 'aprovado');
