// Constelar — som sintetizado (Web Audio): nenhum arquivo de áudio.
import { estado } from './nucleo.js';

const NOTAS = { C4: 261.63, D4: 293.66, E4: 329.63, G4: 392, A4: 440, C5: 523.25, D5: 587.33, E5: 659.25, G5: 783.99, A5: 880, C6: 1046.5, E6: 1318.5 };

export const som = {
  ctx: null, mestre: null, musica: null, timer: null,

  /** Chamado no primeiro gesto do jogador (exigência dos navegadores). */
  iniciar() {
    if (this.ctx) { this.ctx.resume?.(); return; }
    const AC = window.AudioContext || window.webkitAudioContext;
    if (!AC) return;
    this.ctx = new AC();
    this.mestre = this.ctx.createGain();
    this.mestre.gain.value = 0.9;
    this.mestre.connect(this.ctx.destination);
    this.musica = this.ctx.createGain();
    this.musica.gain.value = 0;
    const eco = this.ctx.createDelay(1);
    eco.delayTime.value = 0.46;
    const volta = this.ctx.createGain();
    volta.gain.value = 0.38;
    this.musica.connect(this.mestre);
    this.musica.connect(eco); eco.connect(volta); volta.connect(eco); volta.connect(this.mestre);
    this.ajustarMusica();
    document.addEventListener('visibilitychange', () => (document.hidden ? this.ctx.suspend() : this.ctx.resume()));
  },

  nota(freq, { tipo = 'triangle', ini = 0, dur = 0.3, vol = 0.12, destino = null, ataque = 0.008 } = {}) {
    if (!this.ctx) return;
    const t = this.ctx.currentTime + ini;
    const o = this.ctx.createOscillator(), g = this.ctx.createGain();
    o.type = tipo; o.frequency.value = freq;
    g.gain.setValueAtTime(0.0001, t);
    g.gain.exponentialRampToValueAtTime(vol, t + ataque);
    g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
    o.connect(g); g.connect(destino || this.mestre);
    o.start(t); o.stop(t + dur + 0.05);
  },

  tocar(nome) {
    if (!this.ctx || !estado.som) return;
    const n = NOTAS;
    switch (nome) {
      case 'letra': this.nota(480 + Math.random() * 240, { dur: 0.045, vol: 0.035 }); break;
      case 'abrir': this.nota(n.E5, { dur: 0.09, vol: 0.07 }); this.nota(n.A5, { ini: 0.06, dur: 0.12, vol: 0.07 }); break;
      case 'fechar': this.nota(n.A5, { dur: 0.08, vol: 0.06 }); this.nota(n.E5, { ini: 0.06, dur: 0.12, vol: 0.06 }); break;
      case 'perto': this.nota(n.G5, { tipo: 'sine', dur: 0.1, vol: 0.05 }); break;
      case 'acender': [n.C5, n.E5, n.G5, n.C6, n.E6].forEach((f, i) => { this.nota(f, { ini: i * 0.085, dur: 0.5, vol: 0.13 }); this.nota(f * 2, { tipo: 'sine', ini: i * 0.085, dur: 0.3, vol: 0.04 }); }); break;
      case 'completa': [n.C5, n.E5, n.G5, n.C6, n.G5, n.C6, n.E6].forEach((f, i) => this.nota(f, { ini: i * 0.11, dur: 0.6, vol: 0.13 }));
        [n.C5, n.E5, n.G5, n.C6].forEach((f) => this.nota(f, { tipo: 'sine', ini: 0.85, dur: 1.6, vol: 0.09 })); break;
      case 'certo': this.nota(n.E5, { dur: 0.12 }); this.nota(n.A5, { ini: 0.1, dur: 0.25 }); break;
      case 'errado': this.nota(196, { tipo: 'sawtooth', dur: 0.16, vol: 0.05 }); this.nota(155, { tipo: 'sawtooth', ini: 0.12, dur: 0.24, vol: 0.05 }); break;
    }
  },

  /** Trilha ambiente generativa: notas longas de uma escala pentatônica. */
  ajustarMusica() {
    if (!this.ctx) return;
    const ligada = estado.som && estado.musica;
    this.musica.gain.setTargetAtTime(ligada ? 1 : 0, this.ctx.currentTime, 0.4);
    clearInterval(this.timer);
    if (!ligada) return;
    const escala = [NOTAS.C4, NOTAS.D4, NOTAS.E4, NOTAS.G4, NOTAS.A4, NOTAS.C5, NOTAS.D5, NOTAS.E5];
    const tocar = () => {
      if (document.hidden) return;
      const f = escala[Math.floor(Math.random() * escala.length)];
      this.nota(f / 2, { tipo: 'sine', dur: 4.2, vol: 0.035, destino: this.musica, ataque: 1.2 });
      if (Math.random() < 0.6) this.nota(f, { tipo: 'triangle', ini: 0.4, dur: 2.6, vol: 0.018, destino: this.musica, ataque: 0.6 });
    };
    tocar();
    this.timer = setInterval(tocar, 2600);
  },
};
