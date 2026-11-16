-- Versão final do esquema: símbolo e ordem das constelações, registro da autorização (LGPD),
-- nota de revisão, destaque da homenageada e acervo de demonstração ampliado.

ALTER TABLE areas
    ADD COLUMN icone VARCHAR(20) NOT NULL DEFAULT 'estrela' AFTER cor,
    ADD COLUMN ordem SMALLINT NOT NULL DEFAULT 0 AFTER icone;

ALTER TABLE cientistas
    ADD COLUMN destaque TINYINT(1) NOT NULL DEFAULT 0 AFTER curiosidade,
    ADD COLUMN autorizacao_registro VARCHAR(300) NULL AFTER autorizacao_uso,
    ADD COLUMN nota_revisao VARCHAR(300) NULL AFTER status,
    ADD COLUMN atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE tentativas_login
    ADD COLUMN acao VARCHAR(20) NOT NULL DEFAULT 'login' AFTER ip,
    ADD INDEX idx_acao (acao, ip, criado_em);

-- ---------------------------------------------------------------- Constelações

UPDATE areas SET icone = 'telescopio', ordem = 1, cor = '#4cc9f0',
    descricao = 'O estudo dos astros, das galáxias e do universo.'
    WHERE nome = 'Astronomia';
UPDATE areas SET icone = 'atomo', ordem = 2, cor = '#b69cff',
    descricao = 'O estudo da matéria, da energia e das forças da natureza.'
    WHERE nome = 'Física';
UPDATE areas SET icone = 'celula', ordem = 3, cor = '#3ddc97',
    descricao = 'Biologia, saúde e agricultura: o estudo dos seres vivos.'
    WHERE nome = 'Ciências da Vida';
UPDATE areas SET icone = 'frasco', ordem = 4, cor = '#ffd23f',
    descricao = 'O estudo das substâncias e de suas transformações.'
    WHERE nome = 'Química';
UPDATE areas SET icone = 'pintura', ordem = 5, cor = '#ff8fd0',
    descricao = 'Arqueologia, antropologia e psicologia: o estudo das pessoas e das sociedades.'
    WHERE nome = 'Ciências Humanas';

INSERT INTO areas (nome, constelacao, descricao, cor, icone, ordem)
SELECT 'Matemática e Engenharia', 'Constelação do Compasso',
       'Os números, as formas e as construções que mudam o mundo.', '#ff8a3d', 'compasso', 6
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM areas x WHERE x.nome = 'Matemática e Engenharia');

-- ---------------------------------------------------------------- Acervo: revisão das 9 primeiras

UPDATE cientistas SET destaque = 1,
    instituicao = 'Catholic University of America / NASA Goddard',
    biografia = 'Astrônoma nascida em Jundiaí (SP), conhecida como “Mulher das Estrelas”. Em 1997 descobriu a supernova SN 1997D. É professora nos Estados Unidos, colabora com o centro Goddard da NASA em pesquisas sobre galáxias e se dedica a incentivar crianças e jovens a seguir carreira em ciência.',
    curiosidade = 'Com colegas, identificou as “bolhas azuis”: grupos de estrelas jovens que nasceram fora das galáxias, em meio ao gás arrancado por colisões entre elas.',
    fonte = 'NASA, Imagine the Universe! (imagine.gsfc.nasa.gov); Revista Pesquisa FAPESP, “Dança das galáxias”; CatholicU Magazine'
    WHERE nome = 'Duília de Mello';

UPDATE cientistas SET
    biografia = 'Astrofísica e professora da USP. Estuda a composição química das estrelas mais antigas da Via Láctea para entender como a nossa galáxia se formou.',
    curiosidade = 'Recebeu em 2009 o prêmio L’Oréal-UNESCO Para Mulheres na Ciência.',
    fonte = 'Instituto de Astronomia, Geofísica e Ciências Atmosféricas da USP; Agência Brasil'
    WHERE nome = 'Beatriz Barbuy';

UPDATE cientistas SET
    biografia = 'Física e professora da UFRGS. Pesquisa os comportamentos estranhos da água, que não age como a maioria dos líquidos, e como esse conhecimento pode ajudar, por exemplo, a tirar o sal da água do mar.',
    curiosidade = 'Recebeu em 2013 o prêmio L’Oréal-UNESCO Para Mulheres na Ciência e trabalha para que mais mulheres façam física.',
    fonte = 'Revista Pesquisa FAPESP, “O lado esquisito da água”; Instituto de Física da UFRGS'
    WHERE nome = 'Márcia Barbosa';

UPDATE cientistas SET
    biografia = 'Física e professora do ITA. Em 1989 concluiu o doutorado na Inglaterra e se tornou a primeira mulher negra brasileira doutora em Física. Pesquisa materiais semicondutores, usados em sensores.',
    curiosidade = 'Foi a primeira mulher negra a dar aulas no ITA, numa época em que a escola ainda nem aceitava alunas.',
    fonte = 'Forbes Brasil; Correio Braziliense; Instituto Tecnológico de Aeronáutica'
    WHERE nome = 'Sonia Guimarães';

UPDATE cientistas SET
    biografia = 'Biomédica baiana. Em 2020 fez parte da equipe que sequenciou o genoma do novo coronavírus no Brasil cerca de 48 horas depois da confirmação do primeiro caso no país. Antes disso, já havia participado do sequenciamento do vírus zika.',
    curiosidade = 'Em 2021 virou boneca Barbie, numa coleção em homenagem a mulheres que atuaram contra a pandemia.',
    fonte = 'CNN Brasil; Agência FAPESP; Instituto de Medicina Tropical da USP'
    WHERE nome = 'Jaqueline Goes de Jesus';

UPDATE cientistas SET
    instituicao = 'Embrapa Agrobiologia',
    biografia = 'Agrônoma nascida na antiga Tchecoslováquia que adotou o Brasil como país. Descobriu como bactérias do solo ajudam as plantas a obter nitrogênio, o que permitiu cultivar soja usando muito menos adubo químico.',
    curiosidade = 'Graças às suas descobertas, o Brasil economiza mais de um bilhão de dólares por ano em adubos.',
    fonte = 'Embrapa Agrobiologia; Revista Pesquisa FAPESP'
    WHERE nome = 'Johanna Döbereiner';

UPDATE cientistas SET
    biografia = 'Psiquiatra alagoana, formada médica em 1926, a única mulher de sua turma. Recusou os tratamentos agressivos da época e usou a pintura, a modelagem e o afeto para cuidar de pessoas com transtornos mentais.',
    curiosidade = 'Em 1952 fundou no Rio de Janeiro o Museu de Imagens do Inconsciente, com obras criadas por seus pacientes.',
    fonte = 'Museu de Imagens do Inconsciente (museuimagensdoinconsciente.org.br)'
    WHERE nome = 'Nise da Silveira';

UPDATE cientistas SET
    biografia = 'Química e professora da UNESP em Araraquara. Pesquisa substâncias produzidas por plantas da biodiversidade brasileira que podem dar origem a novos medicamentos.',
    curiosidade = 'Presidiu a Sociedade Brasileira de Química, que em 2019 criou um prêmio com o seu nome para reconhecer mulheres de destaque na área.',
    fonte = 'Agência FAPESP; Revista Pesquisa FAPESP'
    WHERE nome = 'Vanderlan Bolzani';

UPDATE cientistas SET
    biografia = 'Arqueóloga paulista. Liderou por décadas as pesquisas na Serra da Capivara, no Piauí, onde estudou centenas de sítios com pinturas rupestres, e lutou pela criação do parque nacional que protege a região.',
    curiosidade = 'O Parque Nacional da Serra da Capivara, criado em 1979, foi reconhecido pela UNESCO como Patrimônio Cultural da Humanidade em 1991.',
    fonte = 'Fundação Museu do Homem Americano (FUMDHAM); Correio Braziliense (2025)'
    WHERE nome = 'Niède Guidon';

-- ---------------------------------------------------------------- Acervo: novas cientistas

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Thaisa Storchi Bergmann', a.id, 'Universidade Federal do Rio Grande do Sul (UFRGS)',
 'Astrofísica e professora da UFRGS. Estuda os buracos negros gigantes que existem no centro das galáxias e como eles engolem e lançam matéria ao seu redor, mudando a vida da galáxia inteira.',
 'Recebeu em 2015 o prêmio L’Oréal-UNESCO Para Mulheres na Ciência, em Paris.',
 'Agência FAPESP; ONU News (2015)', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Astronomia'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Thaisa Storchi Bergmann');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Rosaly Lopes', a.id, 'Laboratório de Propulsão a Jato (JPL) da NASA',
 'Astrônoma e geóloga planetária nascida no Rio de Janeiro, pesquisadora do JPL, laboratório da NASA. É especialista em vulcões de outros mundos e estudou Io, lua de Júpiter, com dados da sonda Galileo.',
 'Descobriu 71 vulcões ativos em Io e, por isso, entrou para o Guinness, o livro dos recordes.',
 'NASA JPL (science.jpl.nasa.gov); Revista Pesquisa FAPESP', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Astronomia'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Rosaly Lopes');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Elisa Frota-Pessoa', a.id, 'Centro Brasileiro de Pesquisas Físicas (CBPF)',
 'Física carioca, uma das primeiras mulheres a se formar em Física no Brasil, em 1942. Em 1949 ajudou a fundar o Centro Brasileiro de Pesquisas Físicas (CBPF), onde pesquisou partículas e radioatividade.',
 'Entrou na faculdade contra a vontade do pai e nunca mais largou a ciência: trabalhou no CBPF até os 74 anos.',
 'Revista Pesquisa FAPESP; Centro Brasileiro de Pesquisas Físicas', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Física'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Elisa Frota-Pessoa');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Sonja Ashauer', a.id, 'Universidade de São Paulo (USP) / Universidade de Cambridge',
 'Física paulistana, formada pela USP em 1942. Estudou na Universidade de Cambridge, na Inglaterra, com o cientista Paul Dirac, ganhador do Nobel, e em 1948 se tornou a primeira brasileira doutora em Física.',
 'Morreu aos 25 anos, poucos meses depois de terminar o doutorado, e é lembrada como pioneira da física no Brasil.',
 'Revista Temporalidades (UFMG); Women in the History of Quantum Physics (Cambridge University Press)', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Física'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Sonja Ashauer');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Bertha Lutz', a.id, 'Museu Nacional',
 'Bióloga paulistana, especialista em anfíbios e pesquisadora do Museu Nacional, no Rio de Janeiro, por mais de 40 anos. Também liderou a luta pelo direito das mulheres ao voto no Brasil, conquistado em 1932.',
 'Em 1945 participou da conferência que criou a ONU e defendeu que os direitos das mulheres entrassem na Carta das Nações Unidas.',
 'Museu Nacional/UFRJ; Câmara dos Deputados', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Ciências da Vida'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Bertha Lutz');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Mayana Zatz', a.id, 'Universidade de São Paulo (USP)',
 'Geneticista e professora da USP, onde coordena o Centro de Estudos do Genoma Humano. Estuda doenças genéticas que enfraquecem os músculos, como as distrofias musculares, e orienta famílias afetadas por elas.',
 'Recebeu em 2001 o prêmio L’Oréal-UNESCO Para Mulheres na Ciência.',
 'Revista Pesquisa FAPESP; Agência FAPESP', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Ciências da Vida'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Mayana Zatz');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Eloisa Mano', a.id, 'Universidade Federal do Rio de Janeiro (UFRJ)',
 'Química e professora da UFRJ. Foi pioneira no estudo dos polímeros, materiais como os plásticos e as borrachas, e criou em 1968 o primeiro grupo de pesquisa dessa área no Brasil. Também foi uma das primeiras a pesquisar a reciclagem de plásticos.',
 'O Instituto de Macromoléculas da UFRJ, criado por iniciativa dela, hoje leva o seu nome.',
 'Instituto de Macromoléculas Professora Eloisa Mano (ima.ufrj.br); Sociedade Brasileira de Química', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Química'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Eloisa Mano');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Blanka Wladislaw', a.id, 'Universidade de São Paulo (USP)',
 'Química nascida em Varsóvia, na Polônia, que veio jovem para o Brasil. Professora da USP, ajudou a construir a pesquisa em química orgânica no país, estudando principalmente substâncias que contêm enxofre.',
 'Orientou dezenas de mestres e doutores e publicou mais de cem artigos científicos.',
 'Sociedade Brasileira de Química (Memórias SBQ); Revista Pesquisa FAPESP', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Química'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Blanka Wladislaw');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Yvonne Mascarenhas', a.id, 'Universidade de São Paulo (USP), São Carlos',
 'Química e física nascida em Pederneiras (SP), professora da USP em São Carlos. É pioneira da cristalografia no Brasil, técnica que usa raios X para descobrir como os átomos se organizam dentro dos materiais.',
 'Criou o Laboratório de Cristalografia de São Carlos nos anos 1960 e foi a primeira pessoa a dirigir o Instituto de Física de São Carlos.',
 'Revista Pesquisa FAPESP, “A senhora dos cristais”; Forbes Brasil', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Química'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Yvonne Mascarenhas');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Lélia Gonzalez', a.id, 'Pontifícia Universidade Católica do Rio de Janeiro (PUC-Rio)',
 'Antropóloga, filósofa e professora nascida em Belo Horizonte. Estudou a cultura brasileira a partir da vida das mulheres negras e foi uma das fundadoras do Movimento Negro Unificado, em 1978.',
 'Criou as palavras “amefricanidade” e “pretuguês” para mostrar a força da herança africana e indígena no Brasil.',
 'Brasil de Fato; Alma Preta', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Ciências Humanas'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Lélia Gonzalez');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Ruth Cardoso', a.id, 'Universidade de São Paulo (USP)',
 'Antropóloga e professora da USP. Pesquisou a vida nas favelas e periferias das cidades e os movimentos sociais criados por seus moradores.',
 'Em 1995 criou o programa Comunidade Solidária, que unia governo e sociedade no combate à pobreza.',
 'Revista Pesquisa FAPESP; Agência FAPESP', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Ciências Humanas'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Ruth Cardoso');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Carolina Bori', a.id, 'Universidade de São Paulo (USP)',
 'Psicóloga e professora da USP. Foi pioneira da psicologia experimental no Brasil, que estuda o comportamento com pesquisas e experimentos, e ajudou a organizar a profissão de psicóloga no país.',
 'Em 1986 se tornou a primeira mulher a presidir a Sociedade Brasileira para o Progresso da Ciência (SBPC).',
 'Revista Pesquisa FAPESP; Agência FAPESP; Instituto de Psicologia da USP', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Ciências Humanas'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Carolina Bori');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Maria Laura Mouzinho Leite Lopes', a.id, 'Universidade Federal do Rio de Janeiro (UFRJ)',
 'Matemática pernambucana e professora da UFRJ. Concluiu o doutorado em Matemática em 1949, quando quase nenhuma brasileira chegava a esse título, e dedicou boa parte da vida a melhorar o ensino de matemática nas escolas.',
 'Criou grupos de estudo e projetos, como o Projeto Fundão, que formaram muitas gerações de professores de matemática.',
 'Sociedade Brasileira de Matemática; Boletim GEPEM', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Matemática e Engenharia'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Maria Laura Mouzinho Leite Lopes');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Marília Chaves Peixoto', a.id, 'Escola Nacional de Engenharia (atual UFRJ)',
 'Matemática e engenheira gaúcha, professora da Escola Nacional de Engenharia, no Rio de Janeiro. Seus estudos sobre sistemas dinâmicos, que descrevem coisas que mudam com o tempo, ajudaram a chegar a um resultado famoso da matemática, o Teorema de Peixoto.',
 'Em 1951 foi eleita para a Academia Brasileira de Ciências, uma das primeiras mulheres a receber esse reconhecimento.',
 'Academia Brasileira de Ciências; Revista Pesquisa FAPESP', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Matemática e Engenharia'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Marília Chaves Peixoto');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Enedina Alves Marques', a.id, 'Governo do Estado do Paraná',
 'Engenheira civil curitibana. Formou-se em 1945 e foi a primeira mulher negra a se tornar engenheira no Brasil. Trabalhou em grandes obras do Paraná, como a Usina Capivari-Cachoeira.',
 'Antes de ser engenheira, foi professora em escolas do Paraná.',
 'Confea; Crea-PR; CBIC', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Matemática e Engenharia'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Enedina Alves Marques');

INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status)
SELECT 'Carolina Araujo', a.id, 'Instituto de Matemática Pura e Aplicada (IMPA)',
 'Matemática e pesquisadora do Instituto de Matemática Pura e Aplicada (IMPA), no Rio de Janeiro. Estuda geometria algébrica, área que usa equações para entender formas e espaços.',
 'Recebeu em 2020 o Prêmio Ramanujan, dado a jovens matemáticos de países em desenvolvimento, e trabalha para aumentar a presença de mulheres na matemática.',
 'ICTP (ictp.it); IMPA', 1, 'aprovado'
FROM areas a WHERE a.nome = 'Matemática e Engenharia'
  AND NOT EXISTS (SELECT 1 FROM cientistas x WHERE x.nome = 'Carolina Araujo');

-- ---------------------------------------------------------------- Registro da autorização (LGPD)
-- Pessoas falecidas: dados públicos. Cientistas vivas: o acervo é de DEMONSTRAÇÃO e a equipe
-- precisa registrar a autorização de cada uma antes do uso público (o painel sinaliza quais).

UPDATE cientistas SET autorizacao_registro = 'Pessoa falecida; informações de fontes públicas.'
WHERE autorizacao_registro IS NULL AND nome IN (
    'Elisa Frota-Pessoa', 'Sonja Ashauer', 'Johanna Döbereiner', 'Nise da Silveira', 'Bertha Lutz',
    'Eloisa Mano', 'Blanka Wladislaw', 'Niède Guidon', 'Lélia Gonzalez', 'Ruth Cardoso', 'Carolina Bori',
    'Maria Laura Mouzinho Leite Lopes', 'Marília Chaves Peixoto', 'Enedina Alves Marques');

UPDATE cientistas SET autorizacao_registro = 'Demonstração: informações públicas de divulgação científica. Registrar a autorização da cientista antes do uso público.'
WHERE autorizacao_registro IS NULL AND status = 'aprovado';
