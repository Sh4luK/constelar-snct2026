// Constelar — arranque do jogo.
import { DPR, PARAMS, MODO_FEIRA, estado, montarMundo, totalAcesas } from './nucleo.js';
import { som } from './audio.js';
import { criarUI } from './ui.js';
import { Preparo, Fundo, Mundo, Bussola } from './mundo.js';

const OCIOSO_FEIRA_MS = 120000;
const raiz = document.documentElement;

function falhar(e) {
  console.error(e);
  const c = document.getElementById('carregando');
  c.classList.remove('fora');
  c.querySelector('p').textContent = 'Não foi possível abrir o observatório. Recarregue a página.';
}

/**
 * WebGL por software (sem placa de vídeo) rende bem menos que o Canvas comum.
 * Nesses casos, e quando o jogo mede poucos quadros por segundo, usa-se o Canvas.
 */
const CHAVE_RENDER = 'constelar.render';
function tipoDeRender() {
  const forcado = PARAMS.get('render') || (() => { try { return sessionStorage.getItem(CHAVE_RENDER); } catch { return null; } })();
  if (forcado === 'canvas') return Phaser.CANVAS;
  if (forcado === 'webgl') return Phaser.WEBGL;
  try {
    const gl = document.createElement('canvas').getContext('webgl', { failIfMajorPerformanceCaveat: true });
    if (!gl) return Phaser.CANVAS;
    const info = gl.getExtension('WEBGL_debug_renderer_info');
    const placa = info ? String(gl.getParameter(info.UNMASKED_RENDERER_WEBGL)) : '';
    return /swiftshader|llvmpipe|software|softpipe/i.test(placa) ? Phaser.CANVAS : Phaser.AUTO;
  } catch { return Phaser.CANVAS; }
}

async function iniciar() {
  estado.carregar();
  const json = (url) => fetch(url, { headers: { Accept: 'application/json' } }).then((r) => { if (!r.ok) throw new Error(url + ' ' + r.status); return r.json(); });
  const [api, figuras] = await Promise.all([
    json('/api/cientistas.php'), json('/assets/dados/figuras.json'),
    document.fonts?.load('900 20px "Archivo Cond"'), document.fonts?.load('italic 20px "Instrument Serif"'), document.fonts?.load('700 16px "Archivo"'),
  ]);
  const mundo = montarMundo(api, figuras);
  let cena = null;
  let ultimoGesto = performance.now();

  const ctx = {
    mundo,
    ui: null,
    falha: falhar,
    mundoPronto(c) { cena = c; ctx.ui.mostrarCapa(); medirDesempenho(); },
    comecar() {
      som.iniciar();
      ctx.ui.fecharCapa();
      if (!estado.introVista) setTimeout(() => cena.falarComLume(), 500);
    },
    novoJogo() {
      estado.reiniciar();
      location.reload();
    },
    irPara(area, estrela) { cena?.irPara(area, estrela); },
    posicaoJogadora() { return cena ? { x: cena.jog.x, y: cena.jog.y } : mundo.centro; },
    alternarLeve() { raiz.classList.toggle('leve'); },
  };
  ctx.ui = criarUI(ctx);

  const jogo = new Phaser.Game({
    type: tipoDeRender(),
    parent: 'jogo',
    backgroundColor: '#170b3b',
    scale: { mode: Phaser.Scale.NONE, width: Math.round(innerWidth * DPR), height: Math.round(innerHeight * DPR), zoom: 1 / DPR },
    physics: { default: 'arcade', arcade: { debug: PARAMS.get('debug') === 'fisica' } },
    render: { antialias: true, powerPreference: 'high-performance' },
    banner: false,
    scene: [Preparo, Fundo, Mundo, Bussola],
    callbacks: { preBoot: (g) => g.registry.merge({ mundo, ctx }) },
  });
  addEventListener('resize', () => jogo.scale.resize(Math.round(innerWidth * DPR), Math.round(innerHeight * DPR)));

  /** Computador lento: troca para Canvas e, se ainda faltar fôlego, desliga desfoques e partículas. */
  function medirDesempenho() {
    if (PARAMS.get('leve') === '1') raiz.classList.add('leve');
    setTimeout(() => {
      const fps = jogo.loop.actualFps;
      if (fps < 30 && jogo.config.renderType === Phaser.WEBGL && !PARAMS.get('render')) {
        try { sessionStorage.setItem(CHAVE_RENDER, 'canvas'); location.reload(); return; } catch { /* segue em WebGL */ }
      }
      if (fps < 42) raiz.classList.add('leve');
    }, 3500);
  }

  // Teclado: um único lugar decide o que cada tecla faz em cada estado.
  addEventListener('keydown', (ev) => {
    ultimoGesto = performance.now();
    if (ev.repeat || ev.ctrlKey || ev.metaKey || ev.altKey) return;
    const ui = ctx.ui, t = ev.key.toLowerCase();
    const acao = t === 'e' || t === ' ' || t === 'enter';
    if (ev.target instanceof HTMLInputElement) return;
    if (!document.getElementById('capa').classList.contains('fora')) {
      if (acao && !document.getElementById('capa').hidden) { ev.preventDefault(); ctx.comecar(); }
      return;
    }
    if (document.getElementById('painel').open) return;
    if (ui.dialogoAberto) {
      if (acao) { ev.preventDefault(); ui.avancar(); }
      else if (/^[1-9]$/.test(t)) ui.escolher(Number(t) - 1);
      return;
    }
    if (!cena || cena.ocupado) return;
    if (acao) { ev.preventDefault(); cena.interagir(); }
    else if (t === 'm') ui.mapa();
    else if (t === 'b') ui.album();
    // O próprio Esc fecharia o painel recém-aberto: abre depois que a tecla termina.
    else if (t === 'escape') { ev.preventDefault(); setTimeout(() => ui.menu(), 0); }
  });
  for (const tipo of ['pointerdown', 'pointermove', 'wheel']) addEventListener(tipo, () => { ultimoGesto = performance.now(); }, { passive: true });

  // Modo feira (?feira=1): depois de 2 minutos sem uso, recomeça para o próximo visitante.
  if (MODO_FEIRA) {
    setInterval(() => {
      const jogando = document.getElementById('capa').classList.contains('fora');
      if (performance.now() - ultimoGesto > OCIOSO_FEIRA_MS && (jogando || totalAcesas(mundo) > 0)) ctx.novoJogo();
    }, 5000);
  }

  // Apoio a testes (?debug=1): expõe o estado para automação.
  if (PARAMS.has('debug')) window.__constelar = { ctx, estado, get cena() { return cena; }, jogo };
}

iniciar().catch(falhar);
