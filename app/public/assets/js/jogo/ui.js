// Constelar — interface em HTML sobre o canvas: HUD, diálogo, álbum, mapa, menu e celebrações.
import { estado, acesasNaArea, areaCompleta, totalAcesas, REDUZIR, MODO_FEIRA } from './nucleo.js';
import { som } from './audio.js';

const $ = (id) => document.getElementById(id);
const SVG = 'http://www.w3.org/2000/svg';

function el(tag, props = {}, ...filhos) {
  const e = document.createElement(tag);
  for (const [k, v] of Object.entries(props)) {
    if (k === 'class') e.className = v;
    else if (k === 'cor') e.style.setProperty('--cor', v);
    else if (k.startsWith('on')) e.addEventListener(k.slice(2), v);
    else if (v !== false && v != null) e.setAttribute(k, v === true ? '' : v);
  }
  e.append(...filhos.filter((f) => f != null));
  return e;
}
function svg(tag, attrs = {}, ...filhos) {
  const e = document.createElementNS(SVG, tag);
  for (const [k, v] of Object.entries(attrs)) e.setAttribute(k, v);
  e.append(...filhos);
  return e;
}
const usar = (id, classe = '') => svg('svg', { class: classe, 'aria-hidden': 'true' }, svg('use', { href: '#' + id }));

/** Desenho de uma constelação (linhas e estrelas), com o progresso atual. */
function figuraSvg(area, classe = 'figura carta-figura') {
  const s = svg('svg', { class: classe, viewBox: '-140 -140 280 280', 'aria-hidden': 'true' });
  const acesa = (i) => i >= area.estrelas.length ? areaCompleta(area) : estado.descobertas.has(area.estrelas[i].c.id);
  for (const [a, b] of area.arestas) {
    s.append(svg('line', {
      class: 'fg-linha', x1: area.normal[a][0] * 100, y1: area.normal[a][1] * 100, x2: area.normal[b][0] * 100, y2: area.normal[b][1] * 100,
    }));
  }
  area.normal.forEach(([x, y], i) => {
    if (i < area.estrelas.length && acesa(i)) s.append(svg('use', { class: 'fg-estrela', href: '#u-estrela', x: x * 100 - 15, y: y * 100 - 15, width: 30, height: 30 }));
    else s.append(svg('circle', { class: 'fg-ponto', cx: x * 100, cy: y * 100, r: i < area.estrelas.length ? 6 : 4 }));
  });
  return s;
}

export function criarUI(ctx) {
  const { mundo } = ctx;
  const d = {
    hud: $('hud'), num: $('hud-num'), total: $('hud-total'), contador: document.querySelector('.hud-contador'),
    local: $('local'), dica: $('dica'), toasts: $('toasts'),
    dlg: $('dialogo'), retrato: $('dlg-retrato'), sub: $('dlg-sub'), titulo: $('dlg-titulo'), corpo: $('dlg-corpo'),
    opcoes: $('dlg-opcoes'), pag: $('dlg-pag'), btn: $('dlg-btn'),
    painel: $('painel'), pTitulo: $('painel-titulo'), pCorpo: $('painel-corpo'),
    capa: $('capa'), carregando: $('carregando'),
  };
  const toque = matchMedia('(pointer: coarse)').matches;
  let fala = null;          // diálogo em andamento
  let timerLetra = 0, timerLocal = 0;

  const ui = {
    get aberto() { return !!fala || d.painel.open || !d.capa.classList.contains('fora'); },
    get dialogoAberto() { return !!fala; },

    /* ------------------------------------------------ capa */
    mostrarCapa() {
      const feitas = totalAcesas(mundo);
      $('capa-jogar').textContent = feitas ? `Continuar (${feitas}/${mundo.total})` : 'Começar';
      $('capa-novo').hidden = !feitas;
      const teclas = toque
        ? [['', 'Toque na tela para andar'], ['', 'Toque numa estrela para conversar']]
        : [['WASD', 'ou setas para andar'], ['E', 'conversar'], ['M', 'mapa'], ['B', 'álbum']];
      $('capa-teclas').replaceChildren(...teclas.map(([k, t]) => el('li', {}, k ? el('kbd', {}, k) : null, t)));
      d.capa.hidden = false;
      d.capa.classList.remove('fora');
      d.carregando.classList.add('fora');
      d.hud.hidden = true;
      $('capa-jogar').focus({ preventScroll: true });
    },
    fecharCapa() {
      d.capa.classList.add('fora');
      d.hud.hidden = false;
      ui.atualizarHud();
      ui.dicaInicial();
    },

    /* ------------------------------------------------ HUD */
    atualizarHud(pulo = false) {
      d.num.textContent = totalAcesas(mundo);
      d.total.textContent = '/' + mundo.total;
      if (pulo) { d.contador.classList.remove('pulo'); void d.contador.offsetWidth; d.contador.classList.add('pulo'); }
    },
    dicaInicial() {
      d.dica.replaceChildren(...(toque
        ? ['Toque na tela para andar · toque numa estrela para conversar']
        : [el('kbd', {}, 'WASD'), ' ou setas para andar · ', el('kbd', {}, 'E'), ' conversar · ', el('kbd', {}, 'M'), ' mapa']));
      d.dica.hidden = false;
      d.dica.classList.remove('fora');
      setTimeout(() => ui.esconderDica(), 14000);
    },
    esconderDica() { d.dica.classList.add('fora'); },
    toast(msg) {
      const t = el('p', { class: 'toast' }, msg);
      d.toasts.append(t);
      setTimeout(() => t.remove(), 3500);
    },
    local(titulo, sub, cor) {
      clearTimeout(timerLocal);
      d.local.replaceChildren(el('strong', {}, titulo), el('span', {}, sub));
      d.local.style.setProperty('--cor', cor);
      d.local.classList.add('visivel');
      timerLocal = setTimeout(() => d.local.classList.remove('visivel'), 3600);
    },

    /* ------------------------------------------------ diálogo
     * cfg: { titulo, sub, cor, retrato: {lume} | {variante, apagada}, paginas: [texto | {texto, opcoes, correta}] }
     * Resolve com a lista de respostas (índice escolhido ou null) de cada página. */
    dialogo(cfg) {
      return new Promise((resolver) => {
        fala = { cfg, i: 0, respostas: [], resolver, digitando: false, travado: false };
        d.titulo.textContent = cfg.titulo;
        d.sub.textContent = cfg.sub || '';
        d.dlg.style.setProperty('--cor', cfg.cor || '#ffd23f');
        const r = cfg.retrato || { lume: true };
        d.retrato.style.setProperty('--cor', r.lume ? '#6a2cff' : (r.apagada ? '#5a4c9c' : cfg.cor));
        d.retrato.classList.toggle('apagada', !!r.apagada);
        d.retrato.replaceChildren(usar(r.lume ? 's-lume' : 's-ep-' + (r.apagada ? 0 : r.variante), r.lume ? 'lume' : ''));
        d.dlg.hidden = false;
        som.tocar('abrir');
        ui.esconderDica();
        ui._pagina();
      });
    },
    _pagina() {
      const p = fala.cfg.paginas[fala.i];
      const texto = typeof p === 'string' ? p : p.texto;
      const total = fala.cfg.paginas.length;
      fala.opcoes = typeof p === 'string' ? null : p.opcoes;
      fala.correta = typeof p === 'string' ? undefined : p.correta;
      fala.travado = false;
      d.pag.textContent = total > 1 ? `${fala.i + 1} / ${total}` : '';
      d.btn.textContent = fala.i === total - 1 ? 'Fechar' : 'Continuar';
      d.btn.hidden = !!fala.opcoes;
      d.opcoes.replaceChildren();
      clearInterval(timerLetra);
      const fim = () => {
        clearInterval(timerLetra);
        fala.digitando = false;
        d.corpo.textContent = texto;
        d.retrato.classList.remove('falando');
        if (fala.opcoes) {
          d.opcoes.replaceChildren(...fala.opcoes.map((o, k) => el('button', {
            class: 'dlg-opcao', type: 'button', tabindex: '-1',
            onclick: (ev) => { ev.stopPropagation(); ui.escolher(k); },
          }, el('kbd', {}, String(k + 1)), o)));
        }
      };
      fala.fim = fim;
      if (REDUZIR) { fim(); return; }
      let n = 0;
      fala.digitando = true;
      d.corpo.textContent = '';
      d.retrato.classList.add('falando');
      timerLetra = setInterval(() => {
        n += 2;
        d.corpo.textContent = texto.slice(0, n);
        if (n % 6 === 0) som.tocar('letra');
        if (n >= texto.length) fim();
      }, 22);
    },
    /** Avança a fala (tecla E/Espaço/Enter, clique ou toque). */
    avancar() {
      if (!fala || fala.travado) return;
      if (fala.digitando) { fala.fim(); return; }
      if (fala.opcoes) return;
      fala.respostas.push(null);
      ui._proxima();
    },
    escolher(k) {
      if (!fala || fala.travado || fala.digitando || !fala.opcoes || k >= fala.opcoes.length) return;
      fala.respostas.push(k);
      if (fala.correta === undefined) { ui._proxima(); return; }
      fala.travado = true;
      const botoes = d.opcoes.children;
      botoes[fala.correta].classList.add('certa');
      if (k !== fala.correta) botoes[k].classList.add('errada');
      som.tocar(k === fala.correta ? 'certo' : 'errado');
      setTimeout(() => fala && ui._proxima(), 1100);
    },
    _proxima() {
      if (++fala.i < fala.cfg.paginas.length) { ui._pagina(); return; }
      const f = fala;
      fala = null;
      clearInterval(timerLetra);
      d.dlg.hidden = true;
      som.tocar('fechar');
      f.resolver(f.respostas);
    },

    /* ------------------------------------------------ painel modal */
    abrirPainel(titulo, corpo, estreito = false) {
      d.pTitulo.replaceChildren(...[].concat(titulo));
      d.pCorpo.replaceChildren(...[].concat(corpo));
      d.painel.classList.toggle('painel--estreito', estreito);
      if (!d.painel.open) { d.painel.showModal(); som.tocar('abrir'); }
      d.pCorpo.scrollTop = 0;
    },
    fecharPainel() { if (d.painel.open) { d.painel.close(); } },

    album() {
      const corpo = mundo.areas.map((a) => el('section', { class: 'album-area area-' + a.id, cor: a.cor },
        el('h3', {}, a.constelacao, el('small', {}, `${a.nome} · ${acesasNaArea(a)}/${a.estrelas.length}`)),
        el('div', { class: 'album-grade' }, ...a.estrelas.map((e) => {
          const num = String(e.c.numero).padStart(2, '0');
          if (!estado.descobertas.has(e.c.id)) {
            return el('div', { class: 'figurinha figurinha--vazia' },
              el('span', { class: 'fig-num' }, num),
              el('span', { class: 'fig-arte' }, usar('u-cadeado')),
              el('span', { class: 'fig-texto' }, el('strong', { class: 'fig-nome' }, '???')));
          }
          return el('button', { class: 'figurinha', type: 'button', cor: a.cor, onclick: () => ui.ficha(e) },
            el('span', { class: 'fig-num' }, num),
            el('span', { class: 'fig-arte' }, usar('u-estrela', 'fig-brilho'), usar('s-ep-' + e.c.variante, 'fig-personagem')),
            el('span', { class: 'fig-texto' }, el('strong', { class: 'fig-nome' }, e.c.nome)));
        }))));
      const medalha = estado.quizMelhor ? el('p', { class: 'mapa-legenda' }, `Melhor resultado no Desafio da Lume: ${estado.quizMelhor}/5`) : null;
      ui.abrirPainel(['Álbum ', el('em', {}, `${totalAcesas(mundo)}/${mundo.total}`)], [...corpo, medalha]);
    },
    ficha(e) {
      const c = e.c;
      ui.abrirPainel(c.nome, el('div', { class: 'ficha', cor: e.area.cor },
        c.instituicao ? el('p', { class: 'ficha-inst' }, c.instituicao) : null,
        el('p', {}, c.biografia),
        c.curiosidade ? el('aside', { class: 'nota' }, el('span', { class: 'nota-rotulo' }, 'Você sabia?'), el('p', {}, c.curiosidade)) : null,
        el('div', { class: 'grupo-botoes' },
          el('button', { class: 'botao botao--claro', type: 'button', onclick: () => ui.album() }, '← Álbum'),
          el('button', { class: 'botao botao--roxo', type: 'button', onclick: () => { ui.fecharPainel(); ctx.irPara(e.area, e); } }, 'Ir até ela'))));
    },

    mapa() {
      const s = svg('svg', { class: 'mapa', viewBox: `0 0 ${mundo.largura} ${mundo.altura}`, role: 'img', 'aria-label': 'Mapa do céu' });
      mundo.areas.forEach((a) => s.append(svg('line', {
        class: 'mapa-linha', x1: mundo.centro.x, y1: mundo.centro.y, x2: a.centro.x, y2: a.centro.y, 'stroke-dasharray': '4 22',
      })));
      s.append(svg('circle', { class: 'mapa-hub', cx: mundo.centro.x, cy: mundo.centro.y, r: 120 }));
      mundo.areas.forEach((a) => {
        const g = svg('g', { class: 'mapa-terr area-' + a.id + (areaCompleta(a) ? ' completa' : ''), tabindex: '0', role: 'button', 'aria-label': 'Ir para ' + a.constelacao });
        g.style.setProperty('--cor', a.cor);
        g.append(svg('circle', { class: 'fundo', cx: a.centro.x, cy: a.centro.y, r: 300 }));
        const acesa = (i) => i >= a.estrelas.length ? areaCompleta(a) : estado.descobertas.has(a.estrelas[i].c.id);
        for (const [i, j] of a.arestas) {
          g.append(svg('line', { class: 'mapa-linha' + (acesa(i) && acesa(j) ? ' acesa' : ''), x1: a.pontos[i].x, y1: a.pontos[i].y, x2: a.pontos[j].x, y2: a.pontos[j].y }));
        }
        a.pontos.forEach((p, i) => g.append(svg('circle', { class: 'mapa-ponto' + (acesa(i) ? ' acesa' : ''), cx: p.x, cy: p.y, r: i < a.estrelas.length ? 18 : 9 })));
        const nome = svg('text', { class: 'mapa-nome', x: a.centro.x, y: a.centro.y + 372 }); nome.textContent = a.constelacao.replace(/^Constelação d[aeo]s? /, '');
        const prog = svg('text', { class: 'mapa-prog', x: a.centro.x, y: a.centro.y + 428 }); prog.textContent = `${acesasNaArea(a)}/${a.estrelas.length}`;
        g.append(nome, prog);
        const ir = () => { ui.fecharPainel(); ctx.irPara(a); };
        g.addEventListener('click', ir);
        g.addEventListener('keydown', (ev) => { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); ir(); } });
        s.append(g);
      });
      const j = ctx.posicaoJogadora();
      s.append(svg('circle', { class: 'mapa-voce', cx: j.x, cy: j.y, r: 34 }));
      ui.abrirPainel(['Mapa ', el('em', {}, 'do céu')], [s, el('p', { class: 'mapa-legenda' }, 'O ponto amarelo é você. Toque numa constelação para ir até ela.')]);
    },

    menu() {
      const item = (rotulo, valor, acao) => el('button', { class: 'botao botao--claro', type: 'button', onclick: acao }, rotulo, el('strong', {}, valor));
      const refazer = () => ui.menu();
      const lista = el('div', { class: 'menu-lista' },
        item('Sons', estado.som ? 'ligados' : 'desligados', () => { estado.som = !estado.som; estado.salvar(); som.ajustarMusica(); refazer(); }),
        item('Música', estado.musica ? 'ligada' : 'desligada', () => { estado.musica = !estado.musica; estado.salvar(); som.ajustarMusica(); refazer(); }),
        item('Gráficos', document.documentElement.classList.contains('leve') ? 'leves' : 'completos', () => { ctx.alternarLeve(); refazer(); }),
        document.fullscreenEnabled ? item('Tela cheia', document.fullscreenElement ? 'sair' : 'entrar', () => {
          (document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()).catch(() => {}).finally?.(refazer);
        }) : null,
        el('button', { class: 'botao botao--tinta', type: 'button', onclick: () => { if (confirm('Apagar o progresso e começar um novo jogo?')) ctx.novoJogo(); } }, 'Novo jogo', usar('u-reiniciar', 'ic')),
        MODO_FEIRA ? null : el('a', { class: 'botao botao--tinta', href: '/' }, 'Sair para o site', usar('u-seta', 'ic')));
      const teclas = el('ul', { class: 'menu-teclas' },
        el('li', {}, el('kbd', {}, 'WASD'), 'ou setas: andar (ou clique/toque no chão)'),
        el('li', {}, el('kbd', {}, 'E'), el('kbd', {}, 'Espaço'), 'conversar e avançar'),
        el('li', {}, el('kbd', {}, 'M'), 'mapa', el('kbd', {}, 'B'), 'álbum', el('kbd', {}, 'Esc'), 'menu'));
      ui.abrirPainel('Menu', [lista, teclas], true);
    },

    /** Constelação completa. */
    festaConstelacao(area) {
      som.tocar('completa');
      ui.abrirPainel('', el('div', { class: 'festa area-' + area.id, cor: area.cor },
        figuraSvg(area),
        el('p', { class: 'festa-rotulo' }, area.constelacao),
        el('p', { class: 'serifa' }, 'constelação completa!'),
        el('p', {}, `Você acendeu as ${area.estrelas.length} estrelas de ${area.nome}. Faltam ${mundo.total - totalAcesas(mundo)} no céu inteiro.`),
        el('div', { class: 'grupo-botoes' }, el('button', { class: 'botao botao--roxo botao--grande', type: 'button', onclick: () => ui.fecharPainel() }, 'Continuar explorando'))), true);
    },

    /** Céu inteiro aceso: certificado. */
    festaFinal() {
      som.tocar('completa');
      const campo = el('input', { type: 'text', maxlength: '40', placeholder: 'Seu nome (opcional)', value: estado.nome, 'aria-label': 'Seu nome' });
      const imprimir = () => {
        estado.nome = campo.value.trim().slice(0, 40); estado.salvar();
        document.querySelector('.certificado')?.remove();
        document.body.append(el('section', { class: 'certificado' },
          usar('s-lume'),
          el('small', {}, 'Observatório Duília de Mello · Constelar · SNCT 2026'),
          el('h1', {}, 'Certificado ', el('em', {}, 'de exploração')),
          el('p', {}, 'O céu do Constelar foi aceso por'),
          el('p', { class: 'cert-nome' }, estado.nome || 'uma pessoa curiosa'),
          el('p', {}, `que conheceu as histórias de ${mundo.total} cientistas brasileiras e completou ${mundo.areas.length} constelações.`),
          el('small', {}, new Date().toLocaleDateString('pt-BR'))));
        window.print();
      };
      ui.abrirPainel('', el('div', { class: 'festa' },
        usar('s-lume', 'carregando-lume'),
        el('p', { class: 'festa-rotulo' }, 'O céu está aceso!'),
        el('p', { class: 'serifa' }, `${mundo.total} estrelas · ${mundo.areas.length} constelações`),
        el('p', {}, 'Você conheceu as histórias de todas as cientistas do observatório. Agora elas brilham porque alguém sabe quem são.'),
        campo,
        el('div', { class: 'grupo-botoes' },
          el('button', { class: 'botao botao--roxo botao--grande', type: 'button', onclick: imprimir }, 'Imprimir certificado'),
          el('button', { class: 'botao botao--claro botao--grande', type: 'button', onclick: () => ui.fecharPainel() }, 'Continuar'))), true);
    },
  };

  /* ------------------------------------------------ eventos fixos */
  d.dlg.addEventListener('click', () => ui.avancar());
  $('btn-mapa').addEventListener('click', () => ui.mapa());
  $('btn-album').addEventListener('click', () => ui.album());
  $('btn-menu').addEventListener('click', () => ui.menu());
  $('painel-fechar').addEventListener('click', () => ui.fecharPainel());
  d.painel.addEventListener('close', () => som.tocar('fechar'));
  d.painel.addEventListener('click', (ev) => { if (ev.target === d.painel) ui.fecharPainel(); });
  $('capa-jogar').addEventListener('click', () => ctx.comecar());
  $('capa-novo').addEventListener('click', () => { if (confirm('Apagar o progresso e começar um novo jogo?')) ctx.novoJogo(); });
  if (MODO_FEIRA) document.querySelector('.hud-sair').hidden = true;

  return ui;
}
