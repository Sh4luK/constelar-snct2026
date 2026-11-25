// Constelar — cenas do Phaser: preparo das texturas, céu de fundo, mundo e bússola.
import {
  COR, DPR, R_TERRITORIO, R_HUB, REDUZIR, estado, hex, primeiroNome, paginar,
  texturaSvg, cssCor, CSS_APAGADA, acesasNaArea, areaCompleta, totalAcesas,
} from './nucleo.js';
import { som } from './audio.js';

const VEL = 300;
const RAIO_FALA = 104;
const FONTE = '"Archivo Cond", "Archivo", system-ui, sans-serif';
const leve = () => document.documentElement.classList.contains('leve');

/** Texto nítido em telas de alta densidade: desenha maior e reduz na mesma proporção. */
function texto(cena, x, y, conteudo, estilo) {
  const e = { ...estilo, fontSize: parseFloat(estilo.fontSize) * DPR + 'px' };
  if (e.padding) e.padding = { x: e.padding.x * DPR, y: e.padding.y * DPR };
  if (e.wordWrap) e.wordWrap = { width: e.wordWrap.width * DPR };
  return cena.add.text(x, y, conteudo, e).setScale(1 / DPR);
}

function rng(semente) {
  return () => {
    semente = (semente + 0x6d2b79f5) | 0;
    let t = Math.imul(semente ^ (semente >>> 15), 1 | semente);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

function estrela4(ctx, x, y, r) {
  ctx.beginPath();
  for (let i = 0; i < 8; i++) {
    const a = (i * Math.PI) / 4, rr = i % 2 ? r * 0.34 : r;
    ctx.lineTo(x + Math.cos(a) * rr, y + Math.sin(a) * rr);
  }
  ctx.closePath();
  ctx.fill();
}

/* ============================================================== Preparo */
export class Preparo extends Phaser.Scene {
  constructor() { super('Preparo'); }

  async create() {
    const { mundo, ctx } = this.registry.values;
    try {
      const tarefas = [
        texturaSvg(this, 'lume', 's-lume', { altura: 80 }),
        texturaSvg(this, 'jogadora', 's-astronauta', { altura: 86 }),
        texturaSvg(this, 'telescopio', 's-telescopio', { altura: 214 }),
        texturaSvg(this, 'ep-apagada', 's-ep-0', { altura: 98, css: CSS_APAGADA }),
        ...['planeta', 'lua', 'cometa', 'foguete', 'satelite'].map((d) => texturaSvg(this, 'd-' + d, 'd-' + d, { altura: 150, adesivo: false })),
      ];
      for (const a of mundo.areas) {
        tarefas.push(texturaSvg(this, 'ic-' + a.id, document.getElementById('i-' + a.icone) ? 'i-' + a.icone : 'i-estrela', { altura: 46, css: cssCor(a.cor) }));
        for (const v of new Set(a.estrelas.map((e) => e.c.variante))) {
          tarefas.push(texturaSvg(this, `ep-${a.id}-${v}`, 's-ep-' + v, { altura: 98, css: cssCor(a.cor) }));
        }
        this.particula('p-' + a.id, a.cor);
      }
      this.particula('p-papel', '#f2efe7');
      this.ceu('ceu1', 90, 11);
      this.ceu('ceu2', 46, 29);
      await Promise.all(tarefas);
    } catch (e) {
      ctx.falha(e);
      return;
    }
    this.scene.launch('Fundo');
    this.scene.launch('Mundo');
    this.scene.launch('Bussola');
    this.scene.stop();
  }

  particula(chave, cor) {
    const t = this.textures.createCanvas(chave, 40, 40);
    const c = t.getContext();
    c.fillStyle = cor;
    estrela4(c, 20, 20, 18);
    t.refresh();
    const p = this.textures.createCanvas(chave + '-o', 20, 20);
    const pc = p.getContext();
    pc.fillStyle = cor; pc.beginPath(); pc.arc(10, 10, 9, 0, Math.PI * 2); pc.fill();
    p.refresh();
  }

  /** Azulejo de céu: pontos e brilhos chapados, sem degradê. */
  ceu(chave, quantos, semente) {
    const T = 1024, t = this.textures.createCanvas(chave, T, T), c = t.getContext(), r = rng(semente);
    for (let i = 0; i < quantos; i++) {
      const x = r() * T, y = r() * T, s = r();
      c.fillStyle = s > 0.5 ? 'rgba(242,239,231,.75)' : 'rgba(182,156,255,.6)';
      if (s > 0.9) estrela4(c, x, y, 9 + r() * 6);
      else { c.beginPath(); c.arc(x, y, 1.6 + r() * 2.2, 0, Math.PI * 2); c.fill(); }
    }
    t.refresh();
  }
}

/* ============================================================== Fundo (paralaxe) */
export class Fundo extends Phaser.Scene {
  constructor() { super('Fundo'); }
  create() {
    this.cameras.main.setOrigin(0, 0).setZoom(DPR).setBackgroundColor(COR.ceu);
    this.a = this.add.tileSprite(0, 0, 10, 10, 'ceu1').setOrigin(0).setTileScale(0.5);
    this.b = this.add.tileSprite(0, 0, 10, 10, 'ceu2').setOrigin(0).setTileScale(0.5).setAlpha(0.8);
    this.ajustar();
    this.scale.on('resize', this.ajustar, this);
  }
  ajustar() {
    const w = this.scale.width / DPR, h = this.scale.height / DPR;
    this.a.setSize(w, h); this.b.setSize(w, h);
  }
  update() {
    const cam = this.scene.get('Mundo').cameras?.main;
    if (!cam) return;
    this.a.setTilePosition(cam.scrollX * 0.24, cam.scrollY * 0.24);
    this.b.setTilePosition(cam.scrollX * 0.5, cam.scrollY * 0.5);
  }
}

/* ============================================================== Mundo */
export class Mundo extends Phaser.Scene {
  constructor() { super('Mundo'); }

  create() {
    const { mundo, ctx } = this.registry.values;
    this.m = mundo;
    this.ctx = ctx;
    this.ui = ctx.ui;
    this.npcs = [];
    this.interativos = [];
    this.alvo = null;
    this.alvoNpc = null;
    this.perto = null;
    this.areaAtual = undefined;
    this.parado = 0;
    this.ocupado = false;

    this.physics.world.setBounds(0, 0, mundo.largura, mundo.altura);
    this.cameras.main.setBounds(0, 0, mundo.largura, mundo.altura);

    this.chao = this.add.graphics().setDepth(-20);
    this.figuras = new Map(mundo.areas.map((a) => [a.id, this.add.graphics().setDepth(-10)]));
    this.obstaculos = this.physics.add.staticGroup();

    this.criarEnfeites();
    this.desenharChao();
    this.criarHub();
    mundo.areas.forEach((a) => this.criarTerritorio(a));
    this.criarJogadora();

    this.aviso = texto(this, 0, 0, '', {
      fontFamily: FONTE, fontSize: '17px', color: '#141414', backgroundColor: '#ffd23f', padding: { x: 10, y: 6 },
    }).setOrigin(0.5, 1).setDepth(99999).setVisible(false).setInteractive({ useHandCursor: true });
    this.aviso.on('pointerdown', (p, x, y, ev) => { ev.stopPropagation(); this.interagir(); });

    this.teclas = this.input.keyboard.addKeys('W,A,S,D,UP,DOWN,LEFT,RIGHT');
    this.input.on('pointerdown', (p, sobre) => this.aoTocar(p, sobre));

    this.ajustar();
    this.scale.on('resize', this.ajustar, this);
    ctx.mundoPronto(this);
  }

  ajustar() {
    const w = this.scale.width / DPR, h = this.scale.height / DPR;
    this.zoomBase = Phaser.Math.Clamp(Math.min(w, h) / 760, 0.6, 1);
    this.cameras.main.setZoom(this.zoomBase * DPR);
  }

  /* ---------------------------------------------- cenário */
  criarEnfeites() {
    const { m } = this, r = rng(77);
    const nomes = ['d-planeta', 'd-lua', 'd-cometa', 'd-foguete', 'd-satelite'];
    let postos = 0;
    for (let t = 0; t < 400 && postos < 11; t++) {
      const x = 120 + r() * (m.largura - 240), y = 120 + r() * (m.altura - 240);
      const livre = Math.hypot(x - m.centro.x, y - m.centro.y) > R_HUB + 230
        && m.areas.every((a) => Math.hypot(x - a.centro.x, y - a.centro.y) > R_TERRITORIO + 200);
      if (!livre) continue;
      this.add.image(x, y, nomes[postos % nomes.length]).setDepth(-30).setScale(0.5 + r() * 0.35).setAngle(-25 + r() * 50).setScrollFactor(0.86);
      postos++;
    }
  }

  tracejado(g, x1, y1, x2, y2, traco, vao) {
    const d = Math.hypot(x2 - x1, y2 - y1), ux = (x2 - x1) / d, uy = (y2 - y1) / d;
    for (let p = 0; p < d; p += traco + vao) {
      const f = Math.min(p + traco, d);
      g.lineBetween(x1 + ux * p, y1 + uy * p, x1 + ux * f, y1 + uy * f);
    }
  }

  anelTracejado(g, cx, cy, r, traco = 0.07, vao = 0.06) {
    for (let a = 0; a < Math.PI * 2; a += traco + vao) {
      g.beginPath(); g.arc(cx, cy, r, a, a + traco); g.strokePath();
    }
  }

  desenharChao() {
    const g = this.chao, { m } = this;
    g.clear();
    for (const a of m.areas) {
      const completa = areaCompleta(a), cor = hex(a.cor);
      const ux = Math.cos(a.angulo), uy = Math.sin(a.angulo);
      const dist = Math.hypot(a.centro.x - m.centro.x, a.centro.y - m.centro.y);
      // trilha do observatório até a constelação
      g.lineStyle(completa ? 4 : 3, completa ? cor : COR.papel, completa ? 0.9 : 0.3);
      const x1 = m.centro.x + (a.centro.x - m.centro.x) * ((R_HUB + 26) / dist), y1 = m.centro.y + (a.centro.y - m.centro.y) * ((R_HUB + 26) / dist);
      const x2 = a.centro.x - (a.centro.x - m.centro.x) * ((R_TERRITORIO + 26) / dist), y2 = a.centro.y - (a.centro.y - m.centro.y) * ((R_TERRITORIO + 26) / dist);
      if (completa) g.lineBetween(x1, y1, x2, y2); else this.tracejado(g, x1, y1, x2, y2, 4, 20);
      // território
      g.fillStyle(COR.ceu2, 1).fillCircle(a.centro.x, a.centro.y, R_TERRITORIO);
      g.lineStyle(3, completa ? cor : COR.papel, completa ? 1 : 0.4);
      if (completa) g.strokeCircle(a.centro.x, a.centro.y, R_TERRITORIO); else this.anelTracejado(g, a.centro.x, a.centro.y, R_TERRITORIO);
      // marcas de carta celeste
      g.lineStyle(2, COR.papel, 0.35);
      for (let k = 0; k < 4; k++) {
        const an = (k * Math.PI) / 2 + Math.PI / 4;
        g.lineBetween(a.centro.x + Math.cos(an) * (R_TERRITORIO - 14), a.centro.y + Math.sin(an) * (R_TERRITORIO - 14),
          a.centro.x + Math.cos(an) * (R_TERRITORIO + 14), a.centro.y + Math.sin(an) * (R_TERRITORIO + 14));
      }
      void ux; void uy;
    }
    // observatório
    const c = m.centro;
    g.fillStyle(COR.deque, 1).fillCircle(c.x, c.y, R_HUB);
    g.lineStyle(4, COR.papel, 1).strokeCircle(c.x, c.y, R_HUB);
    g.lineStyle(2, COR.papel, 0.35); this.anelTracejado(g, c.x, c.y, R_HUB - 46, 0.05, 0.05);
    g.fillStyle(COR.roxo, 1).fillCircle(c.x, c.y - 10, 128);
    g.lineStyle(4, COR.papel, 1).strokeCircle(c.x, c.y - 10, 128);
  }

  criarHub() {
    const { m } = this, c = m.centro;
    this.add.image(c.x, c.y - 4, 'telescopio').setOrigin(0.5, 0.93).setScale(0.5).setDepth(c.y - 4);
    this.obstaculo(c.x, c.y - 26, 54);
    this.interativos.push({ x: c.x, y: c.y + 4, rotulo: 'ver o mapa do céu', acao: () => this.ui.mapa() });

    texto(this, c.x, c.y + 196, 'OBSERVATÓRIO DUÍLIA DE MELLO', { fontFamily: FONTE, fontSize: '21px', color: '#f2efe7' })
      .setOrigin(0.5).setDepth(-5);

    // placas: uma para cada constelação, na borda do deque
    for (const a of m.areas) {
      const x = c.x + Math.cos(a.angulo) * (R_HUB - 4), y = c.y + Math.sin(a.angulo) * (R_HUB - 4);
      this.add.image(x, y, 'ic-' + a.id).setScale(0.5).setDepth(-4);
    }

    // Lume, a guia
    const lx = c.x + 150, ly = c.y + 52;
    this.lume = this.add.image(lx, ly, 'lume').setOrigin(0.5, 0.9).setScale(0.5).setDepth(ly);
    if (!REDUZIR) this.tweens.add({ targets: this.lume, y: ly - 9, angle: 5, duration: 900, yoyo: true, repeat: -1, ease: 'Sine.inOut' });
    this.obstaculo(lx, ly - 18, 26);
    this.interativos.push({ x: lx, y: ly, rotulo: 'falar com a Lume', acao: () => this.falarComLume() });
  }

  obstaculo(x, y, r) {
    const z = this.add.zone(x, y, r * 2, r * 2);
    this.physics.add.existing(z, true);
    z.body.setCircle(r);
    this.obstaculos.add(z);
  }

  criarTerritorio(a) {
    const topo = a.centro.y - R_TERRITORIO;
    texto(this, a.centro.x, topo - 62, a.constelacao.toUpperCase(), { fontFamily: FONTE, fontSize: '34px', color: '#f2efe7' })
      .setOrigin(0.5).setDepth(-5);
    texto(this, a.centro.x, topo - 28, a.nome, { fontFamily: '"Instrument Serif", Georgia, serif', fontStyle: 'italic', fontSize: '24px', color: a.cor })
      .setOrigin(0.5).setDepth(-5);
    if (!a.estrelas.length) {
      texto(this, a.centro.x, a.centro.y, 'Aguardando as\nprimeiras estrelas', { fontFamily: '"Instrument Serif", Georgia, serif', fontStyle: 'italic', fontSize: '24px', color: '#f2efe7', align: 'center' })
        .setOrigin(0.5).setAlpha(0.7);
    }
    a.estrelas.forEach((e) => this.criarNpc(e));
    this.desenharFigura(a);
  }

  criarNpc(e) {
    const acesa = estado.descobertas.has(e.c.id);
    const img = this.add.image(e.x, e.y, acesa ? `ep-${e.area.id}-${e.c.variante}` : 'ep-apagada').setOrigin(0.5, 0.94).setScale(0.5).setDepth(e.y);
    const rotulo = texto(this, e.x, e.y + 12, '', { fontFamily: FONTE, fontSize: '15px', align: 'center', padding: { x: 7, y: 4 }, wordWrap: { width: 150 } })
      .setOrigin(0.5, 0).setDepth(e.y - 1);
    const zzz = texto(this, e.x + 26, e.y - 96, 'z z', { fontFamily: FONTE, fontSize: '18px', color: '#b69cff' }).setDepth(e.y);
    if (!REDUZIR) {
      this.tweens.add({ targets: img, scaleY: 0.515, duration: 1300 + (e.c.id % 5) * 160, yoyo: true, repeat: -1, ease: 'Sine.inOut' });
      this.tweens.add({ targets: zzz, y: zzz.y - 10, alpha: 0.3, duration: 1500, yoyo: true, repeat: -1 });
    }
    const npc = { e, img, rotulo, zzz, x: e.x, y: e.y };
    this.npcs.push(npc);
    this.obstaculo(e.x, e.y - 16, 22);
    this.interativos.push({ x: e.x, y: e.y, npc, rotulo: 'conversar', acao: () => this.conversar(npc) });
    this.pintarNpc(npc);
  }

  pintarNpc(npc) {
    const acesa = estado.descobertas.has(npc.e.c.id);
    npc.img.setTexture(acesa ? `ep-${npc.e.area.id}-${npc.e.c.variante}` : 'ep-apagada');
    npc.zzz.setVisible(!acesa);
    npc.rotulo.setText(acesa ? npc.e.c.nome.toUpperCase() : '? ? ?')
      .setColor(acesa ? '#141414' : '#f2efe7').setBackgroundColor(acesa ? '#f2efe7' : '#3a2880');
  }

  desenharFigura(a) {
    const g = this.figuras.get(a.id), cor = hex(a.cor), completa = areaCompleta(a);
    const acesa = (i) => i >= a.estrelas.length ? completa : estado.descobertas.has(a.estrelas[i].c.id);
    g.clear();
    for (const [i, j] of a.arestas) {
      const p = a.pontos[i], q = a.pontos[j];
      if (acesa(i) && acesa(j)) { g.lineStyle(5, cor, 1); g.lineBetween(p.x, p.y, q.x, q.y); }
      else { g.lineStyle(2.5, COR.papel, 0.3); this.tracejado(g, p.x, p.y, q.x, q.y, 5, 13); }
    }
    a.pontos.forEach((p, i) => {
      const ehCientista = i < a.estrelas.length;
      if (acesa(i)) {
        g.fillStyle(cor, 1).fillCircle(p.x, p.y, ehCientista ? 15 : 8);
        g.lineStyle(3, COR.papel, 1).strokeCircle(p.x, p.y, ehCientista ? 15 : 8);
      } else {
        g.fillStyle(COR.ceu2, 1).fillCircle(p.x, p.y, ehCientista ? 13 : 6);
        g.lineStyle(2.5, COR.papel, 0.5).strokeCircle(p.x, p.y, ehCientista ? 13 : 6);
      }
    });
  }

  criarJogadora() {
    const { m } = this;
    this.jog = this.physics.add.image(m.centro.x - 120, m.centro.y + 150, 'jogadora').setOrigin(0.5, 0.9).setScale(0.5);
    this.jog.body.setCircle(34, this.jog.width / 2 - 34, this.jog.height * 0.9 - 62);
    this.jog.setCollideWorldBounds(true);
    this.physics.add.collider(this.jog, this.obstaculos);
    this.cameras.main.startFollow(this.jog, false, 0.12, 0.12, 0, 30);

    this.rastro = this.add.particles(0, 0, 'p-papel-o', {
      follow: this.jog, followOffset: { x: 0, y: -6 }, lifespan: 520, frequency: 70, emitting: false,
      speed: { min: 8, max: 34 }, angle: { min: 60, max: 120 }, scale: { start: 0.55, end: 0 }, alpha: { start: 0.55, end: 0 },
    }).setDepth(-1);
  }

  /* ---------------------------------------------- entrada */
  aoTocar(p, sobre) {
    if (sobre.length || this.ui.aberto || this.ocupado) return;
    som.iniciar();
    const x = p.worldX, y = p.worldY;
    const npc = this.interativos.find((it) => Math.hypot(it.x - x, it.y - 40 - y) < 62);
    if (npc) {
      this.alvoNpc = npc;
      this.alvo = new Phaser.Math.Vector2(npc.x, npc.y + 46);
    } else {
      this.alvoNpc = null;
      this.alvo = new Phaser.Math.Vector2(x, y);
    }
  }

  /** Tecla E / toque no aviso. */
  interagir() {
    if (!this.perto || this.ui.aberto || this.ocupado) return;
    this.alvo = null; this.alvoNpc = null;
    this.jog.setVelocity(0);
    this.perto.acao();
  }

  /** Caminha sozinha até uma constelação (mapa) ou até uma estrela (álbum). */
  irPara(area, estrela = null) {
    const c = this.m.centro;
    if (estrela) {
      const it = this.interativos.find((i) => i.npc && i.npc.e === estrela);
      this.alvoNpc = it || null;
      this.alvo = new Phaser.Math.Vector2(estrela.x, estrela.y + 46);
    } else {
      const d = Math.hypot(area.centro.x - c.x, area.centro.y - c.y);
      this.alvoNpc = null;
      this.alvo = new Phaser.Math.Vector2(
        area.centro.x - ((area.centro.x - c.x) / d) * (R_TERRITORIO - 40),
        area.centro.y - ((area.centro.y - c.y) / d) * (R_TERRITORIO - 40));
    }
  }

  reposicionar() {
    this.jog.setPosition(this.m.centro.x - 120, this.m.centro.y + 150).setVelocity(0);
    this.alvo = null; this.alvoNpc = null;
    this.areaAtual = undefined;
  }

  /* ---------------------------------------------- conversas */
  async conversar(npc) {
    const { c, area } = npc.e;
    const acesa = estado.descobertas.has(c.id);
    const historia = [...paginar(c.biografia), ...(c.curiosidade ? ['Você sabia? ' + c.curiosidade] : [])];
    const paginas = acesa
      ? [`Que bom te ver de novo! Quer ouvir outra vez a história de ${c.nome}?`, ...historia]
      : [`Zzz… Oh, uma visita! Eu sou a estrela de ${c.nome}. Quando alguém conhece a história dela, eu brilho. Escuta só:`,
        ...historia, `Pronto! Agora você conhece ${primeiroNome(c.nome)}. Olha só como eu brilho!`];
    this.ocupado = true;
    await this.ui.dialogo({
      titulo: c.nome, sub: [area.constelacao, c.instituicao].filter(Boolean).join(' · '), cor: area.cor,
      retrato: { variante: c.variante, apagada: !acesa, foto: c.foto }, paginas,
    });
    this.ocupado = false;
    if (!acesa) this.acender(npc);
  }

  acender(npc) {
    const { c, area } = npc.e;
    estado.descobertas.add(c.id);
    estado.salvar();
    this.pintarNpc(npc);
    this.desenharFigura(area);
    som.tocar('acender');
    this.ui.atualizarHud(true);

    if (!REDUZIR) {
      this.tweens.add({ targets: npc.img, scaleX: 0.66, scaleY: 0.66, y: npc.y - 26, duration: 220, yoyo: true, ease: 'Quad.out' });
      this.cameras.main.zoomTo(this.zoomBase * DPR * 1.06, 160, 'Quad.easeOut', true, (cam, prog) => { if (prog === 1) cam.zoomTo(this.zoomBase * DPR, 320); });
      const n = leve() ? 10 : 26;
      for (const chave of ['p-' + area.id, 'p-papel-o']) {
        const ex = this.add.particles(npc.x, npc.y - 60, chave, {
          speed: { min: 140, max: 380 }, lifespan: 900, gravityY: 260, emitting: false,
          scale: { start: chave.endsWith('-o') ? 0.5 : 0.9, end: 0 }, rotate: { min: 0, max: 360 },
        }).setDepth(99998);
        ex.explode(n);
        this.time.delayedCall(1200, () => ex.destroy());
      }
    }

    const tudo = totalAcesas(this.m) === this.m.total;
    if (tudo) {
      this.desenharChao();
      this.time.delayedCall(900, () => this.ui.festaFinal());
    } else if (areaCompleta(area)) {
      this.desenharChao();
      this.time.delayedCall(900, () => this.ui.festaConstelacao(area));
    } else {
      this.ui.toast(`Estrela acesa! ${acesasNaArea(area)}/${area.estrelas.length} na ${area.constelacao}`);
    }
  }

  async falarComLume() {
    const { m, ui } = this;
    const base = { titulo: 'Lume', sub: 'Estrela-guia do observatório', cor: '#ffd23f', retrato: { lume: true } };
    this.ocupado = true;

    if (!estado.introVista) {
      await ui.dialogo({ ...base, paginas: [
        'Oi! Eu sou a Lume, a estrela-guia do Observatório Duília de Mello. Ele tem esse nome em homenagem à astrônoma brasileira conhecida como “Mulher das Estrelas”.',
        'Cada estrela deste céu guarda a história de uma cientista brasileira. Mas quase ninguém conhece essas histórias, e por isso as estrelas estão apagadas.',
        `Sua missão: visitar as ${m.areas.length} constelações, conversar com as ${m.total} estrelas e ouvir cada história. Quem é conhecida volta a brilhar!`,
        'Siga as trilhas e as setas coloridas na borda da tela. O telescópio aqui do centro mostra o mapa do céu. Boa exploração!',
      ] });
      estado.introVista = true;
      estado.salvar();
      this.ocupado = false;
      return;
    }

    const faltam = m.total - totalAcesas(m);
    const opcoes = ['Onde falta acender?', 'Desafio da Lume', 'Como eu jogo?'];
    if (!faltam) opcoes.push('Quero meu certificado');
    opcoes.push('Tchau!');
    const [resp] = await ui.dialogo({ ...base, paginas: [{
      texto: faltam ? `Você já acendeu ${m.total - faltam} de ${m.total} estrelas. Como posso ajudar?` : 'O céu inteiro está aceso graças a você! Como posso ajudar?',
      opcoes,
    }] });
    const escolha = opcoes[resp];

    if (escolha === 'Onde falta acender?') {
      const pendentes = m.areas.filter((a) => a.estrelas.length && !areaCompleta(a));
      await ui.dialogo({ ...base, paginas: [pendentes.length
        ? 'Ainda há estrelas apagadas aqui:\n' + pendentes.map((a) => `• ${a.constelacao}: ${a.estrelas.length - acesasNaArea(a)}`).join('\n') + '\nAbra o mapa no telescópio e toque na constelação para ir até lá.'
        : 'Não falta nenhuma! Todas as estrelas estão acesas.'] });
    } else if (escolha === 'Desafio da Lume') {
      await this.desafio(base);
    } else if (escolha === 'Como eu jogo?') {
      await ui.dialogo({ ...base, paginas: [
        'Ande com as setas ou WASD, ou toque no chão. Chegue perto de uma estrela e aperte E (ou toque nela) para conversar.',
        'M abre o mapa do céu e B abre o seu álbum de figurinhas. No menu você liga e desliga o som e começa um novo jogo.',
      ] });
    } else if (escolha === 'Quero meu certificado') {
      this.ocupado = false;
      ui.festaFinal();
      return;
    }
    this.ocupado = false;
  }

  /** Quiz gerado a partir do banco: de quem é a pista? */
  async desafio(base) {
    const { m, ui } = this;
    const sorteio = (lista) => lista.map((v) => [Math.random(), v]).sort((a, b) => a[0] - b[0]).map((p) => p[1]);
    const conhecidas = m.estrelas.filter((e) => estado.descobertas.has(e.c.id)).map((e) => e.c);
    const comPista = conhecidas.map((c) => {
      const pista = c.curiosidade || paginar(c.biografia, 160)[0];
      const partes = c.nome.toLowerCase().split(/\s+/).filter((p) => p.length >= 4);
      return partes.some((p) => pista.toLowerCase().includes(p)) ? null : { c, pista };
    }).filter(Boolean);

    if (comPista.length < 3 || m.estrelas.length < 3) {
      await ui.dialogo({ ...base, paginas: ['O desafio usa as histórias que você já ouviu. Acenda pelo menos 3 estrelas e volte aqui!'] });
      return;
    }
    const perguntas = sorteio(comPista).slice(0, 5);
    const paginas = [`Vou dar ${perguntas.length} pistas. Você me diz de qual cientista eu estou falando. Valendo!`];
    for (const { c, pista } of perguntas) {
      const outras = sorteio(m.estrelas.map((e) => e.c).filter((o) => o.id !== c.id)).slice(0, 2);
      const opcoes = sorteio([c, ...outras]);
      paginas.push({ texto: `De quem é esta pista?\n“${pista}”`, opcoes: opcoes.map((o) => o.nome), correta: opcoes.indexOf(c) });
    }
    const respostas = await ui.dialogo({ ...base, sub: 'Desafio da Lume', paginas });
    let acertos = 0;
    perguntas.forEach((_, i) => { if (respostas[i + 1] === paginas[i + 1].correta) acertos++; });
    if (acertos > estado.quizMelhor) { estado.quizMelhor = acertos; estado.salvar(); }
    await ui.dialogo({ ...base, paginas: [acertos === perguntas.length
      ? `Uau, ${acertos} de ${perguntas.length}! Você conhece mesmo essas cientistas.`
      : `Você acertou ${acertos} de ${perguntas.length}. Converse de novo com as estrelas para relembrar as histórias e tente outra vez!`] });
  }

  /* ---------------------------------------------- quadro a quadro */
  update(tempo, delta) {
    const { jog, ui } = this;
    const corpo = jog.body;
    const travado = ui.aberto || this.ocupado;
    const k = this.teclas;
    let ix = 0, iy = 0;

    if (!travado) {
      ix = (k.RIGHT.isDown || k.D.isDown ? 1 : 0) - (k.LEFT.isDown || k.A.isDown ? 1 : 0);
      iy = (k.DOWN.isDown || k.S.isDown ? 1 : 0) - (k.UP.isDown || k.W.isDown ? 1 : 0);
      if (ix || iy) { this.alvo = null; this.alvoNpc = null; ui.esconderDica(); }
      else if (this.alvo) {
        const dx = this.alvo.x - jog.x, dy = this.alvo.y - jog.y, d = Math.hypot(dx, dy);
        if (d < 12) { this.alvo = null; }
        else { const f = Math.min(1, d / 70 + 0.25); ix = (dx / d) * f; iy = (dy / d) * f; }
      }
    }
    const len = Math.hypot(ix, iy);
    if (len > 1) { ix /= len; iy /= len; }
    const s = 1 - Math.exp(-(delta / 1000) * (len ? 11 : 9));
    corpo.velocity.x += (ix * VEL - corpo.velocity.x) * s;
    corpo.velocity.y += (iy * VEL - corpo.velocity.y) * s;

    const v = corpo.velocity.length();
    if (Math.abs(corpo.velocity.x) > 20) jog.setFlipX(corpo.velocity.x < 0);
    jog.setAngle(REDUZIR ? 0 : Phaser.Math.Clamp(corpo.velocity.x / 30, -9, 9));
    jog.setDepth(jog.y);
    this.rastro.emitting = v > 90 && !leve();

    // destino por toque: desiste se ficar presa num obstáculo
    if (this.alvo && v < 24) { this.parado += delta; if (this.parado > 500) { this.alvo = null; } } else this.parado = 0;

    // quem está ao alcance?
    let perto = null, menor = RAIO_FALA;
    if (!travado) {
      for (const it of this.interativos) {
        const d = Math.hypot(jog.x - it.x, jog.y - 10 - it.y);
        if (d < menor) { menor = d; perto = it; }
      }
    }
    if (perto !== this.perto) {
      this.perto = perto;
      if (perto) som.tocar('perto');
    }
    if (perto) {
      const toque = this.sys.game.device.input.touch;
      this.aviso.setText((toque ? 'TOQUE: ' : 'E  ') + perto.rotulo.toUpperCase())
        .setPosition(perto.x, Math.min(perto.y - 112, jog.y - 100)).setVisible(true);
      if (this.alvoNpc === perto && !this.alvo) { this.alvoNpc = null; this.interagir(); }
      else if (this.alvoNpc === perto && menor < 70) { this.alvo = null; this.alvoNpc = null; this.interagir(); }
    } else {
      this.aviso.setVisible(false);
    }

    // em que lugar do céu estamos?
    const m = this.m;
    let area = null;
    if (Math.hypot(jog.x - m.centro.x, jog.y - m.centro.y) < R_HUB) area = 'hub';
    else area = m.areas.find((a) => Math.hypot(jog.x - a.centro.x, jog.y - a.centro.y) < R_TERRITORIO + 10) || null;
    if (area !== this.areaAtual) {
      const primeira = this.areaAtual === undefined;
      this.areaAtual = area;
      if (area === 'hub') { if (!primeira) ui.local('Observatório', 'Duília de Mello', '#ffd23f'); }
      else if (area) ui.local(area.constelacao, `${area.nome} · ${acesasNaArea(area)}/${area.estrelas.length} estrelas`, area.cor);
    }
  }
}

/* ============================================================== Bússola (setas na borda da tela) */
export class Bussola extends Phaser.Scene {
  constructor() { super('Bussola'); }
  create() {
    this.cameras.main.setOrigin(0, 0).setZoom(DPR);
    this.setas = new Map();
  }
  seta(chave, cor) {
    let s = this.setas.get(chave);
    if (!s) {
      s = this.add.triangle(0, 0, -9, -13, 15, 0, -9, 13, cor).setStrokeStyle(2.5, COR.papel, 1);
      this.setas.set(chave, s);
    }
    return s;
  }
  update(tempo) {
    const mundoCena = this.scene.get('Mundo');
    const { mundo, ctx } = this.registry.values;
    const cam = mundoCena.cameras?.main;
    if (!cam || !mundoCena.jog) return;
    const w = this.scale.width / DPR, h = this.scale.height / DPR, cx = w / 2, cy = h / 2, mg = 34;
    const v = cam.worldView, z = cam.zoom / DPR;
    const pulso = REDUZIR ? 1 : 0.75 + 0.25 * Math.sin(tempo / 260);
    const alvos = mundo.areas.filter((a) => a.estrelas.length).map((a) => ({ chave: a.id, cor: hex(a.cor), x: a.centro.x, y: a.centro.y, oculto: areaCompleta(a) }));
    alvos.push({ chave: 'hub', cor: COR.papel, x: mundo.centro.x, y: mundo.centro.y, oculto: false });
    for (const a of alvos) {
      const s = this.seta(a.chave, a.cor);
      const dentro = a.x > v.x && a.x < v.right && a.y > v.y && a.y < v.bottom;
      if (dentro || a.oculto || ctx.ui.aberto) { s.setVisible(false); continue; }
      const sx = (a.x - v.x) * z, sy = (a.y - v.y) * z, ang = Math.atan2(sy - cy, sx - cx);
      const t = Math.min((w / 2 - mg) / Math.abs(Math.cos(ang) || 1e-6), (h / 2 - mg) / Math.abs(Math.sin(ang) || 1e-6));
      s.setVisible(true).setPosition(cx + Math.cos(ang) * t, Math.max(78, cy + Math.sin(ang) * t)).setRotation(ang).setScale(pulso + 0.25);
    }
  }
}
