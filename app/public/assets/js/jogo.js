/* Constelar — observatório virtual (Phaser 3). Dados vêm de /api/cientistas.php. */
(() => {
  'use strict';

  const MUNDO = { w: 3600, h: 2800 };
  const CENTRO = { x: MUNDO.w / 2, y: MUNDO.h / 2 };
  const RAIO_INTERACAO = 110;
  const VELOCIDADE = 280;
  const CHAVE_STORAGE = 'constelar.descobertas.v1';
  const DPR = Math.min(window.devicePixelRatio || 1, 2);
  const FONTE = 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';

  const corNum = (hex) => parseInt(String(hex).replace('#', ''), 16) || 0xffffff;

  function lerDescobertas() {
    try {
      const v = JSON.parse(localStorage.getItem(CHAVE_STORAGE) || '[]');
      return new Set(Array.isArray(v) ? v.filter(Number.isInteger) : []);
    } catch {
      return new Set();
    }
  }

  function salvarDescobertas(set) {
    try { localStorage.setItem(CHAVE_STORAGE, JSON.stringify([...set])); } catch { /* sem storage: progresso só nesta visita */ }
  }

  // PRNG determinístico: a posição de cada estrela não muda entre visitas.
  function mulberry32(seed) {
    return () => {
      seed = (seed + 0x6d2b79f5) | 0;
      let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  function texto(cena, x, y, conteudo, estilo) {
    return cena.add.text(x, y, conteudo, { fontFamily: FONTE, color: '#e8ecff', ...estilo }).setResolution(DPR);
  }

  function paginar(str, max = 230) {
    const frases = String(str).match(/[^.!?]+[.!?]+["”»)]?\s*|[^.!?]+$/g) || [str];
    const paginas = [];
    let atual = '';
    for (const f of frases) {
      if (atual && (atual + f).length > max) { paginas.push(atual.trim()); atual = ''; }
      atual += f;
    }
    if (atual.trim()) paginas.push(atual.trim());
    return paginas;
  }

  /* ---------------------------------------------------------------- Boot */
  class Boot extends Phaser.Scene {
    constructor() { super('Boot'); }

    create() {
      const { width, height } = this.scale;
      const msg = texto(this, width / 2, height / 2, 'Abrindo a cúpula do observatório…', { fontSize: '18px' }).setOrigin(0.5);
      this.gerarTexturas();

      fetch('/api/cientistas.php', { headers: { Accept: 'application/json' } })
        .then((r) => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then((dados) => {
          if (!Array.isArray(dados.areas) || !Array.isArray(dados.cientistas)) throw new Error('formato inválido');
          this.scene.start('Mundo', dados);
        })
        .catch(() => msg.setText('Não foi possível carregar o céu.\nRecarregue a página.').setAlign('center'));
    }

    gerarTexturas() {
      const g = this.make.graphics({ x: 0, y: 0 }, false);

      // Estrela: brilho radial branco (tingido pela cor da área).
      for (let r = 32; r > 0; r -= 2) {
        g.fillStyle(0xffffff, Math.pow(1 - r / 32, 2.2));
        g.fillCircle(32, 32, r);
      }
      g.fillStyle(0xffffff, 1).fillCircle(32, 32, 4);
      g.generateTexture('estrela', 64, 64).clear();

      // Personagem: silhueta simples (tingida pela cor da área).
      g.fillStyle(0xffffff, 1);
      g.fillCircle(14, 9, 8);
      g.fillRoundedRect(4, 19, 20, 22, 8);
      g.generateTexture('personagem', 28, 42).clear();

      // Jogadora: astronauta.
      g.fillStyle(0xf1f5f9, 1).fillRoundedRect(6, 20, 24, 22, 7);
      g.fillStyle(0xcbd5e1, 1).fillRect(10, 38, 6, 8).fillRect(20, 38, 6, 8);
      g.fillStyle(0xf8fafc, 1).fillCircle(18, 14, 12);
      g.fillStyle(0x1e3a8a, 1).fillRoundedRect(10, 9, 16, 10, 5);
      g.fillStyle(0x93c5fd, 0.8).fillRect(12, 11, 5, 3);
      g.generateTexture('jogadora', 36, 48).clear();

      // Telescópio do observatório.
      g.fillStyle(0x94a3b8, 1).fillTriangle(30, 70, 20, 96, 40, 96);
      g.fillStyle(0xe2e8f0, 1);
      g.save();
      g.translateCanvas(30, 66).rotateCanvas(-0.7);
      g.fillRoundedRect(-8, -44, 16, 56, 5);
      g.fillStyle(0xfcd34d, 1).fillRect(-9, -46, 18, 6);
      g.restore();
      g.generateTexture('telescopio', 64, 100).clear();

      // Fundo estrelado repetível.
      const rnd = mulberry32(42);
      for (let i = 0; i < 220; i++) {
        g.fillStyle(0xffffff, 0.2 + rnd() * 0.6);
        g.fillCircle(rnd() * 512, rnd() * 512, rnd() < 0.92 ? 0.8 : 1.6);
      }
      g.generateTexture('ceu', 512, 512).destroy();
    }
  }

  /* --------------------------------------------------------------- Mundo */
  class Mundo extends Phaser.Scene {
    constructor() { super('Mundo'); }

    init(dados) {
      this.areas = dados.areas;
      this.cientistas = dados.cientistas;
      this.descobertas = lerDescobertas();
      this.interativos = [];
      this.alvo = null;
      this.proximo = null;
      this.fechouEm = 0;
      this.uiPronto = false;
    }

    create() {
      this.physics.world.setBounds(0, 0, MUNDO.w, MUNDO.h);
      this.cameras.main.setBounds(0, 0, MUNDO.w, MUNDO.h).setBackgroundColor('#050816');

      this.ceu = this.add.tileSprite(0, 0, this.scale.width, this.scale.height, 'ceu').setOrigin(0).setScrollFactor(0);
      this.ceu2 = this.add.tileSprite(0, 0, this.scale.width, this.scale.height, 'ceu').setOrigin(0).setScrollFactor(0).setAlpha(0.5).setTileScale(0.6);

      this.criarObservatorio();
      this.criarConstelacoes();

      this.jogadora = this.physics.add.image(CENTRO.x, CENTRO.y + 170, 'jogadora').setCollideWorldBounds(true).setDepth(10);
      this.jogadora.body.setCircle(16, 2, 14);

      const cam = this.cameras.main;
      cam.startFollow(this.jogadora, true, 0.1, 0.1);
      this.ajustarZoom();

      this.dica = texto(this, 0, 0, '', {
        fontSize: '14px', backgroundColor: '#0b1026dd', padding: { x: 10, y: 6 }, color: '#fcd34d',
      }).setOrigin(0.5, 1).setDepth(20).setVisible(false).setInteractive({ useHandCursor: true });
      this.dica.on('pointerdown', (p, x, y, ev) => { ev.stopPropagation(); this.interagir(); });

      this.teclas = this.input.keyboard.addKeys('W,A,S,D,UP,DOWN,LEFT,RIGHT');
      // Evento (não JustDown): um toque rápido de tecla não é perdido entre frames.
      const acao = (ev) => {
        if (ev.repeat || !this.uiPronto) return;
        if (this.ui.dialogoAberto) this.ui.avancar(); else this.interagir();
      };
      ['keydown-E', 'keydown-SPACE', 'keydown-ENTER'].forEach((n) => this.input.keyboard.on(n, acao));
      this.input.keyboard.addCapture('W,A,S,D,UP,DOWN,LEFT,RIGHT,SPACE');
      this.input.on('pointerdown', (p, sobre) => {
        if (sobre.length || this.ui.dialogoAberto || this.time.now - this.fechouEm < 250) return;
        this.alvo = new Phaser.Math.Vector2(p.worldX, p.worldY);
      });

      this.scale.on('resize', this.aoRedimensionar, this);
      this.events.once('shutdown', () => this.scale.off('resize', this.aoRedimensionar, this));

      this.scene.launch('UI');
      this.ui = this.scene.get('UI');
      this.ui.events.once('create', () => { this.uiPronto = true; this.atualizarHud(); });
    }

    ajustarZoom() {
      const menor = Math.min(this.scale.width, this.scale.height);
      this.cameras.main.setZoom(menor < 560 ? 0.75 : 1);
    }

    aoRedimensionar(tamanho) {
      this.ceu.setSize(tamanho.width, tamanho.height);
      this.ceu2.setSize(tamanho.width, tamanho.height);
      this.ajustarZoom();
    }

    criarObservatorio() {
      const g = this.add.graphics();
      g.fillStyle(0x1b2559, 0.35).fillCircle(CENTRO.x, CENTRO.y, 260);
      g.lineStyle(2, 0x2a3466, 1).strokeCircle(CENTRO.x, CENTRO.y, 260);
      g.lineStyle(1, 0x2a3466, 0.6).strokeCircle(CENTRO.x, CENTRO.y, 180);
      g.fillStyle(0x1e293b, 1).fillEllipse(CENTRO.x, CENTRO.y + 30, 150, 46);

      const tel = this.add.image(CENTRO.x, CENTRO.y - 20, 'telescopio').setDepth(5);
      texto(this, CENTRO.x, CENTRO.y + 70, 'Observatório Duília de Mello', { fontSize: '16px', color: '#fcd34d', fontStyle: 'bold' }).setOrigin(0.5);

      this.interativos.push({
        x: tel.x, y: tel.y + 30, rotulo: '[E] olhar no telescópio',
        dialogo: () => ({
          titulo: 'Observatório Duília de Mello',
          subtitulo: 'Guia do Constelar',
          cor: '#fcd34d',
          paginas: [
            'Bem-vinda e bem-vindo ao Constelar! Aqui, cada cientista é uma estrela e cada área do conhecimento é uma constelação.',
            'Este observatório homenageia Duília de Mello, a "Mulher das Estrelas": astrônoma brasileira que descobriu a supernova SN 1997D e colabora com a NASA.',
            'Explore o céu com as setas, WASD ou tocando na tela. Aproxime-se de uma cientista e converse com ela para acender a sua estrela.',
            'Acenda todas as estrelas de uma área para completar a constelação. Boa exploração!',
          ],
        }),
      });
    }

    criarConstelacoes() {
      const n = this.areas.length;
      this.estrelasPorArea = new Map();
      this.linhas = new Map();

      this.areas.forEach((area, i) => {
        const ang = -Math.PI / 2 + (i * 2 * Math.PI) / Math.max(n, 1);
        const cx = CENTRO.x + Math.cos(ang) * 950;
        const cy = CENTRO.y + Math.sin(ang) * 720;
        const cor = corNum(area.cor);
        area.centro = { x: cx, y: cy };

        this.add.image(cx, cy, 'estrela').setTint(cor).setAlpha(0.08).setScale(14);
        area.rotulo = texto(this, cx, cy - 330, area.constelacao, { fontSize: '22px', color: area.cor, fontStyle: 'bold' }).setOrigin(0.5);
        texto(this, cx, cy - 302, area.nome, { fontSize: '14px', color: '#a9b2d9' }).setOrigin(0.5);

        this.linhas.set(area.id, this.add.graphics().setDepth(1));

        const rnd = mulberry32(area.id * 9973 + 7);
        const posicoes = [];
        const lista = this.cientistas.filter((c) => c.area_id === area.id);
        if (!lista.length) {
          texto(this, cx, cy, 'Aguardando novas estrelas…', { fontSize: '14px', color: '#a9b2d9' }).setOrigin(0.5);
        }

        const estrelas = lista.map((c) => {
          let x = cx, y = cy;
          for (let t = 0; t < 40; t++) {
            const r = 40 + rnd() * 220, a = rnd() * Math.PI * 2;
            x = cx + Math.cos(a) * r * 1.3;
            y = cy + Math.sin(a) * r;
            if (posicoes.every((p) => Phaser.Math.Distance.Between(p.x, p.y, x, y) > 120)) break;
          }
          posicoes.push({ x, y });
          return this.criarEstrela(c, area, x, y);
        });
        this.estrelasPorArea.set(area.id, estrelas);
        this.desenharLinhas(area);
      });
    }

    criarEstrela(c, area, x, y) {
      const cor = corNum(area.cor);
      const brilho = this.add.image(x, y, 'estrela').setTint(cor).setDepth(2);
      const figura = this.add.image(x, y + 42, 'personagem').setTint(cor).setDepth(3);
      const nome = texto(this, x, y + 70, '', { fontSize: '13px', align: 'center' }).setOrigin(0.5, 0).setDepth(3);
      this.tweens.add({ targets: brilho, scale: { from: 0.95, to: 1.15 }, duration: 1400 + (c.id % 7) * 180, yoyo: true, repeat: -1, ease: 'Sine.inOut' });

      const estrela = { c, area, x, y, brilho, figura, nome };
      this.aplicarEstado(estrela);

      this.interativos.push({
        x, y: y + 30, rotulo: '[E] conversar', estrela,
        dialogo: () => this.dialogoCientista(estrela),
      });
      return estrela;
    }

    aplicarEstado(e) {
      const acesa = this.descobertas.has(e.c.id);
      e.brilho.setAlpha(acesa ? 1 : 0.35);
      e.figura.setAlpha(acesa ? 1 : 0.55);
      e.nome.setText(acesa ? e.c.nome : '???').setColor(acesa ? '#e8ecff' : '#a9b2d9');
    }

    desenharLinhas(area) {
      const g = this.linhas.get(area.id);
      const estrelas = (this.estrelasPorArea.get(area.id) || []).filter((e) => this.descobertas.has(e.c.id));
      g.clear();
      if (estrelas.length < 2) return;
      const total = this.estrelasPorArea.get(area.id).length;
      const completa = estrelas.length === total;
      g.lineStyle(completa ? 3 : 2, corNum(area.cor), completa ? 0.9 : 0.5);
      g.beginPath().moveTo(estrelas[0].x, estrelas[0].y);
      estrelas.slice(1).forEach((e) => g.lineTo(e.x, e.y));
      if (completa && estrelas.length > 2) g.closePath();
      g.strokePath();
    }

    dialogoCientista(e) {
      const { c, area } = e;
      const paginas = [`Olá! Eu sou ${c.nome}.` + (c.instituicao ? `\nInstituição: ${c.instituicao}.` : ''), ...paginar(c.biografia)];
      if (c.curiosidade) paginas.push('Curiosidade: ' + c.curiosidade);
      return { titulo: c.nome, subtitulo: `${area.constelacao} · ${area.nome}`, cor: area.cor, paginas, aoFim: () => this.descobrir(e) };
    }

    descobrir(e) {
      if (this.descobertas.has(e.c.id)) return;
      this.descobertas.add(e.c.id);
      salvarDescobertas(this.descobertas);
      this.aplicarEstado(e);
      this.desenharLinhas(e.area);
      this.tweens.add({ targets: e.brilho, scale: 2.2, duration: 260, yoyo: true, ease: 'Quad.out' });
      this.cameras.main.flash(180, 255, 255, 255, false);

      const estrelas = this.estrelasPorArea.get(e.area.id);
      const acesas = estrelas.filter((x) => this.descobertas.has(x.c.id)).length;
      if (acesas === estrelas.length) {
        this.ui.aviso(`✦ ${e.area.constelacao} completa!`, e.area.cor);
        this.tweens.add({ targets: e.area.rotulo, scale: 1.3, duration: 400, yoyo: true, repeat: 2 });
      } else {
        this.ui.aviso(`Estrela acesa: ${acesas}/${estrelas.length} na ${e.area.constelacao}`, e.area.cor);
      }
      this.atualizarHud();
    }

    atualizarHud() {
      const linhas = this.areas.map((a) => {
        const est = this.estrelasPorArea.get(a.id) || [];
        return { nome: a.constelacao, cor: a.cor, acesas: est.filter((e) => this.descobertas.has(e.c.id)).length, total: est.length };
      });
      this.ui.atualizarHud(this.cientistas.filter((c) => this.descobertas.has(c.id)).length, this.cientistas.length, linhas);
    }

    // Setas na borda da tela apontando para constelações fora de vista.
    atualizarBussola() {
      const cam = this.cameras.main, v = cam.worldView, z = cam.zoom;
      const alvos = this.areas.map((a) => {
        const dentro = a.centro.x > v.x && a.centro.x < v.right && a.centro.y > v.y && a.centro.y < v.bottom;
        return { cor: a.cor, visivel: !dentro, sx: (a.centro.x - v.x) * z, sy: (a.centro.y - v.y) * z };
      });
      this.ui.atualizarBussola(alvos);
    }

    interagir() {
      if (!this.proximo || this.ui.dialogoAberto) return;
      this.alvo = null;
      this.jogadora.setVelocity(0);
      const d = this.proximo.dialogo();
      this.ui.abrirDialogo(d, () => {
        this.fechouEm = this.time.now;
        if (d.aoFim) d.aoFim();
      });
    }

    update() {
      if (!this.uiPronto) return;
      const k = this.teclas;

      this.ceu.setTilePosition(this.cameras.main.scrollX * 0.15, this.cameras.main.scrollY * 0.15);
      this.ceu2.setTilePosition(this.cameras.main.scrollX * 0.35, this.cameras.main.scrollY * 0.35);

      if (this.ui.dialogoAberto) {
        this.jogadora.setVelocity(0);
        return;
      }

      let vx = (k.RIGHT.isDown || k.D.isDown ? 1 : 0) - (k.LEFT.isDown || k.A.isDown ? 1 : 0);
      let vy = (k.DOWN.isDown || k.S.isDown ? 1 : 0) - (k.UP.isDown || k.W.isDown ? 1 : 0);

      if (vx || vy) {
        this.alvo = null;
      } else if (this.alvo) {
        const dx = this.alvo.x - this.jogadora.x, dy = this.alvo.y - this.jogadora.y;
        if (Math.hypot(dx, dy) < 8) this.alvo = null; else { vx = dx; vy = dy; }
      }

      const v = new Phaser.Math.Vector2(vx, vy);
      if (v.lengthSq() > 0) {
        v.normalize().scale(VELOCIDADE);
        this.jogadora.setFlipX(v.x < 0);
        this.ui.esconderAjuda();
      }
      this.jogadora.setVelocity(v.x, v.y);

      let melhor = null, dist = RAIO_INTERACAO;
      for (const it of this.interativos) {
        const d = Phaser.Math.Distance.Between(this.jogadora.x, this.jogadora.y, it.x, it.y);
        if (d < dist) { dist = d; melhor = it; }
      }
      this.proximo = melhor;
      this.atualizarBussola();
      if (melhor) {
        const rot = melhor.estrela && !this.descobertas.has(melhor.estrela.c.id) ? '[E] conversar ✦' : melhor.rotulo;
        this.dica.setText(this.sys.game.device.input.touch ? rot.replace('[E] ', 'Toque: ') : rot)
          .setPosition(melhor.x, melhor.y - 80).setVisible(true);
      } else {
        this.dica.setVisible(false);
      }
    }
  }

  /* ------------------------------------------------------------------ UI */
  class UI extends Phaser.Scene {
    constructor() { super('UI'); }

    create() {
      this.dialogoAberto = false;

      this.hudTotal = texto(this, 0, 12, '', { fontSize: '16px', fontStyle: 'bold', color: '#fcd34d', backgroundColor: '#0b1026cc', padding: { x: 10, y: 6 } }).setOrigin(1, 0);
      this.hudAreas = this.add.container(0, 50);

      const toque = this.sys.game.device.input.touch;
      this.ajuda = texto(this, 0, 0, toque ? 'Toque na tela para mover · toque no aviso para conversar' : 'Setas / WASD ou clique para mover · E para conversar', {
        fontSize: '14px', backgroundColor: '#0b1026dd', padding: { x: 12, y: 8 }, align: 'center',
      }).setOrigin(0.5, 1);
      this.time.delayedCall(12000, () => this.esconderAjuda());

      this.caixa = this.add.container(0, 0).setVisible(false);
      this.fundoCaixa = this.add.graphics();
      this.zonaCaixa = this.add.zone(0, 0, 10, 10).setOrigin(0).setInteractive({ useHandCursor: true });
      this.zonaCaixa.on('pointerdown', (p, x, y, ev) => { ev.stopPropagation(); this.avancar(); });
      this.tTitulo = texto(this, 20, 16, '', { fontSize: '20px', fontStyle: 'bold' });
      this.tSub = texto(this, 20, 44, '', { fontSize: '13px', color: '#a9b2d9' });
      this.tCorpo = texto(this, 20, 74, '', { fontSize: '17px', lineSpacing: 6 });
      this.tRodape = texto(this, 0, 0, '', { fontSize: '12px', color: '#a9b2d9' }).setOrigin(1, 1);
      this.caixa.add([this.fundoCaixa, this.zonaCaixa, this.tTitulo, this.tSub, this.tCorpo, this.tRodape]);

      this.aviso_ = texto(this, 0, 0, '', { fontSize: '18px', fontStyle: 'bold', backgroundColor: '#0b1026ee', padding: { x: 14, y: 10 } })
        .setOrigin(0.5, 0).setAlpha(0);

      this.scale.on('resize', this.posicionar, this);
      this.events.once('shutdown', () => this.scale.off('resize', this.posicionar, this));
      this.posicionar();
    }

    posicionar() {
      const { width: w, height: h } = this.scale;
      this.hudTotal.setPosition(w - 12, 12);
      this.hudAreas.setPosition(w - 12, 52);
      this.hudAreas.setVisible(w >= 640);
      this.ajuda.setPosition(w / 2, h - 16).setWordWrapWidth(w - 32);
      this.aviso_.setPosition(w / 2, 56);

      const cw = Math.min(760, w - 24);
      const ch = Math.min(230, Math.max(190, h * 0.32));
      this.caixa.setPosition((w - cw) / 2, h - ch - 12);
      this.fundoCaixa.clear()
        .fillStyle(0x0b1026, 0.95).fillRoundedRect(0, 0, cw, ch, 14)
        .lineStyle(2, corNum(this.corAtual || '#2a3466'), 1).strokeRoundedRect(0, 0, cw, ch, 14);
      this.zonaCaixa.setSize(cw, ch);
      this.zonaCaixa.input.hitArea.setSize(cw, ch);
      this.tCorpo.setWordWrapWidth(cw - 40, true);
      this.tRodape.setPosition(cw - 16, ch - 12);
      this.caixaW = cw;
    }

    atualizarBussola(alvos) {
      const { width: w, height: h } = this.scale;
      const cx = w / 2, cy = h / 2, m = 28;
      if (!this.setas) this.setas = [];
      alvos.forEach((a, i) => {
        let s = this.setas[i];
        if (!s) {
          s = this.setas[i] = this.add.triangle(0, 0, 0, -11, 18, 0, 0, 11, corNum(a.cor)).setAlpha(0.85);
          this.tweens.add({ targets: s, alpha: 0.4, duration: 900, yoyo: true, repeat: -1 });
        }
        s.setVisible(a.visivel && !this.dialogoAberto);
        if (!a.visivel) return;
        const ang = Math.atan2(a.sy - cy, a.sx - cx);
        const t = Math.min((w / 2 - m) / Math.abs(Math.cos(ang) || 1e-6), (h / 2 - m) / Math.abs(Math.sin(ang) || 1e-6));
        s.setPosition(cx + Math.cos(ang) * t, cy + Math.sin(ang) * t).setRotation(ang);
      });
    }

    atualizarHud(acesas, total, areas) {
      this.hudTotal.setText(`✦ ${acesas}/${total} estrelas`);
      this.hudAreas.removeAll(true);
      areas.forEach((a, i) => {
        const feita = a.total > 0 && a.acesas === a.total;
        this.hudAreas.add(texto(this, 0, i * 20, `${feita ? '✦ ' : ''}${a.nome} ${a.acesas}/${a.total}`, {
          fontSize: '13px', color: feita ? a.cor : '#a9b2d9', backgroundColor: '#0b1026aa', padding: { x: 6, y: 2 },
        }).setOrigin(1, 0));
      });
    }

    esconderAjuda() {
      if (this.ajuda.visible && !this.ajudaSumindo) {
        this.ajudaSumindo = true;
        this.tweens.add({ targets: this.ajuda, alpha: 0, duration: 600, onComplete: () => this.ajuda.setVisible(false) });
      }
    }

    aviso(msg, cor) {
      this.tweens.killTweensOf(this.aviso_);
      this.aviso_.setText(msg).setColor(cor || '#fcd34d').setAlpha(1);
      this.tweens.add({ targets: this.aviso_, alpha: 0, delay: 2600, duration: 700 });
    }

    abrirDialogo(d, aoFechar) {
      this.dialogo = d;
      this.aoFechar = aoFechar;
      this.pagina = 0;
      this.corAtual = d.cor;
      this.posicionar();
      this.tTitulo.setText(d.titulo).setColor(d.cor || '#e8ecff');
      this.tSub.setText(d.subtitulo || '');
      this.caixa.setVisible(true);
      this.dialogoAberto = true;
      (this.setas || []).forEach((x) => x.setVisible(false));
      this.mostrarPagina();
    }

    mostrarPagina() {
      const txt = this.dialogo.paginas[this.pagina];
      const ultima = this.pagina === this.dialogo.paginas.length - 1;
      this.tRodape.setText(`${this.pagina + 1}/${this.dialogo.paginas.length}  ${ultima ? '■ fechar' : '▶ continuar'} (E / toque)`);
      this.tCorpo.setText('');
      this.digitando?.remove();
      let i = 0;
      this.textoCheio = txt;
      this.digitando = this.time.addEvent({
        delay: 18, repeat: txt.length - 1,
        callback: () => { i++; this.tCorpo.setText(txt.slice(0, i)); },
      });
    }

    avancar() {
      if (!this.dialogoAberto) return;
      if (this.digitando && this.digitando.getOverallProgress() < 1) {
        this.digitando.remove();
        this.digitando = null;
        this.tCorpo.setText(this.textoCheio);
        return;
      }
      if (++this.pagina < this.dialogo.paginas.length) {
        this.mostrarPagina();
        return;
      }
      this.caixa.setVisible(false);
      this.dialogoAberto = false;
      const cb = this.aoFechar;
      this.aoFechar = null;
      if (cb) cb();
    }
  }

  new Phaser.Game({
    type: Phaser.AUTO,
    parent: 'jogo',
    backgroundColor: '#050816',
    scale: { mode: Phaser.Scale.RESIZE, width: window.innerWidth, height: window.innerHeight },
    physics: { default: 'arcade', arcade: { debug: false } },
    scene: [Boot, Mundo, UI],
  });
})();
