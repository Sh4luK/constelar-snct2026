// Constelar — núcleo do jogo: constantes, progresso salvo e modelo do mundo.

export const COR = {
  ceu: 0x170b3b, ceu2: 0x1f1050, deque: 0x2b1569, roxo: 0x6a2cff,
  papel: 0xf2efe7, tinta: 0x141414, amarelo: 0xffd23f,
};
export const R_TERRITORIO = 300;   // raio de cada constelação no mundo
export const ESCALA_FIGURA = 200;  // figuras.json (-1..1) → pixels
export const R_HUB = 290;
export const DPR = Math.min(window.devicePixelRatio || 1, 2);
export const PARAMS = new URLSearchParams(location.search);
export const MODO_FEIRA = PARAMS.get('feira') === '1';
export const REDUZIR = matchMedia('(prefers-reduced-motion: reduce)').matches;

export const hex = (cor) => parseInt(String(cor).replace('#', ''), 16) || 0xffffff;
export const primeiroNome = (nome) => String(nome).split(' ')[0];

/* ---------------------------------------------------------------- Progresso (só no navegador) */
const CHAVE = 'constelar.v2';

export const estado = {
  descobertas: new Set(),
  introVista: false,
  som: true,
  musica: true,
  quizMelhor: 0,
  nome: '',

  carregar() {
    try {
      const d = JSON.parse(localStorage.getItem(CHAVE) || 'null');
      if (d) {
        this.descobertas = new Set((d.descobertas || []).filter(Number.isInteger));
        this.introVista = !!d.introVista;
        this.som = d.som !== false;
        this.musica = d.musica !== false;
        this.quizMelhor = d.quizMelhor | 0;
        this.nome = typeof d.nome === 'string' ? d.nome.slice(0, 40) : '';
      } else {
        const antigo = JSON.parse(localStorage.getItem('constelar.descobertas.v1') || '[]');
        if (Array.isArray(antigo)) this.descobertas = new Set(antigo.filter(Number.isInteger));
      }
    } catch { /* sem armazenamento: o progresso vale só nesta visita */ }
  },
  salvar() {
    try {
      localStorage.setItem(CHAVE, JSON.stringify({
        descobertas: [...this.descobertas], introVista: this.introVista,
        som: this.som, musica: this.musica, quizMelhor: this.quizMelhor, nome: this.nome,
      }));
    } catch { /* idem */ }
  },
  reiniciar() {
    this.descobertas.clear();
    this.introVista = false;
    this.quizMelhor = 0;
    this.nome = '';
    this.salvar();
  },
};

/* ---------------------------------------------------------------- Modelo do mundo */

/** Mesma regra de src/arte.php: pontos da figura; cientistas extras formam um anel. */
export function geometriaFigura(figuras, icone, n) {
  const fig = figuras[icone] || figuras.estrela;
  const pontos = fig.pontos.map((p) => [...p]);
  const arestas = fig.arestas.map((a) => [...a]);
  const base = pontos.length;
  const extras = Math.max(0, n - base);
  for (let j = 0; j < extras; j++) {
    const ang = (-54 + (j * 360) / extras) * Math.PI / 180;
    const p = [1.28 * Math.cos(ang), 1.28 * Math.sin(ang)];
    let perto = 0, menor = Infinity;
    for (let k = 0; k < base; k++) {
      const d = Math.hypot(pontos[k][0] - p[0], pontos[k][1] - p[1]);
      if (d < menor) { menor = d; perto = k; }
    }
    pontos.push(p);
    arestas.push([base + j, perto]);
  }
  return { pontos, arestas };
}

export function montarMundo(api, figuras) {
  const n = api.areas.length;
  const fator = Math.max(1, (n * 760) / (2 * Math.PI * 780));
  const a = 1080 * fator, b = 780 * fator;
  const margem = R_TERRITORIO + 240;
  const largura = 2 * (a + margem), altura = 2 * (b + margem);
  const centro = { x: largura / 2, y: altura / 2 };

  const areas = api.areas.map((ar, i) => {
    const ang = -Math.PI / 2 + (i * 2 * Math.PI) / Math.max(n, 1);
    const cx = centro.x + Math.cos(ang) * a, cy = centro.y + Math.sin(ang) * b;
    const lista = api.cientistas.filter((c) => c.area_id === ar.id);
    const g = geometriaFigura(figuras, ar.icone, lista.length);
    const pontos = g.pontos.map(([px, py]) => ({ x: cx + px * ESCALA_FIGURA, y: cy + py * ESCALA_FIGURA }));
    const estrelas = lista.map((c, k) => ({ c, area: null, indice: k, x: pontos[k].x, y: pontos[k].y }));
    const area = { ...ar, angulo: ang, centro: { x: cx, y: cy }, pontos, arestas: g.arestas, estrelas, normal: g.pontos };
    estrelas.forEach((e) => { e.area = area; });
    return area;
  });

  const estrelas = areas.flatMap((ar) => ar.estrelas);
  return { areas, estrelas, centro, largura, altura, total: estrelas.length };
}

export const acesasNaArea = (area) => area.estrelas.filter((e) => estado.descobertas.has(e.c.id)).length;
export const areaCompleta = (area) => area.estrelas.length > 0 && acesasNaArea(area) === area.estrelas.length;
export const totalAcesas = (mundo) => mundo.estrelas.filter((e) => estado.descobertas.has(e.c.id)).length;

/** Divide a biografia em falas curtas. */
export function paginar(texto, max = 210) {
  const frases = String(texto).match(/[^.!?]+[.!?]+["”»)]?\s*|[^.!?]+$/g) || [String(texto)];
  const paginas = [];
  let atual = '';
  for (const f of frases) {
    if (atual && (atual + f).length > max) { paginas.push(atual.trim()); atual = ''; }
    atual += f;
  }
  if (atual.trim()) paginas.push(atual.trim());
  return paginas;
}

/* ---------------------------------------------------------------- Texturas a partir dos desenhos SVG da página */
let defsXml = null;

/** Rasteriza um símbolo de src/sprites.php em uma textura do Phaser. */
export async function texturaSvg(cena, chave, simbolo, { altura, css = '', adesivo = true }) {
  if (cena.textures.exists(chave)) return;
  const defs = document.getElementById('sprites-defs');
  defsXml ??= new XMLSerializer().serializeToString(defs);
  const [, , vw, vh] = document.getElementById(simbolo).getAttribute('viewBox').split(/\s+/).map(Number);
  const pad = adesivo ? 9 : 2;
  const escala = (altura * 2) / (vh + pad * 2);
  const w = Math.ceil((vw + pad * 2) * escala), h = Math.ceil((vh + pad * 2) * escala);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="${-pad} ${-pad} ${vw + pad * 2} ${vh + pad * 2}">`
    + `<style>.c-cor{fill:#ffd23f}.t-cor{stroke:#ffd23f}.c-papel{fill:#f2efe7}.t-papel{stroke:#f2efe7}${css}</style>${defsXml}`
    + `<use href="#${simbolo}" width="${vw}" height="${vh}"${adesivo ? ' filter="url(#f-adesivo)"' : ''}/></svg>`;
  const img = new Image();
  img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
  await img.decode();
  const cv = document.createElement('canvas');
  cv.width = w; cv.height = h;
  cv.getContext('2d').drawImage(img, 0, 0, w, h);
  cena.textures.addCanvas(chave, cv);
}

export const cssCor = (cor) => `.c-cor{fill:${cor}}.t-cor{stroke:${cor}}`;
export const CSS_APAGADA = '.c-cor{fill:#5a4c9c}.t-cor{stroke:#5a4c9c}.c-papel{fill:#9b90c9}.t-papel{stroke:#9b90c9}';
