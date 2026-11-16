// Constelar — comportamentos do site (sem dependências).
(() => {
  'use strict';
  const d = document;
  d.documentElement.classList.add('js');
  const reduzido = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Reflexo do vidro acompanha o ponteiro.
  if (matchMedia('(hover: hover)').matches && !reduzido) {
    let alvo = null, quadro = 0, px = 0, py = 0;
    const aplicar = () => {
      quadro = 0;
      if (!alvo) return;
      const r = alvo.getBoundingClientRect();
      alvo.style.setProperty('--mx', ((px - r.left) / r.width) * 100 + '%');
      alvo.style.setProperty('--my', ((py - r.top) / r.height) * 100 + '%');
    };
    d.addEventListener('pointermove', (ev) => {
      alvo = ev.target instanceof Element ? ev.target.closest('.vidro, .botao') : null;
      px = ev.clientX; py = ev.clientY;
      if (alvo && !quadro) quadro = requestAnimationFrame(aplicar);
    }, { passive: true });
  }

  d.addEventListener('DOMContentLoaded', () => {
    // Entrada suave dos blocos ao rolar.
    const itens = d.querySelectorAll('.revelar');
    if ('IntersectionObserver' in window && !reduzido) {
      const obs = new IntersectionObserver((entradas) => {
        entradas.forEach((en, i) => {
          if (!en.isIntersecting) return;
          en.target.style.transitionDelay = Math.min(i * 70, 280) + 'ms';
          en.target.classList.add('visivel');
          obs.unobserve(en.target);
        });
      }, { rootMargin: '0px 0px -8% 0px' });
      itens.forEach((el) => obs.observe(el));
    } else {
      itens.forEach((el) => el.classList.add('visivel'));
    }

    // Contador de caracteres.
    d.querySelectorAll('textarea[data-contador]').forEach((ta) => {
      const max = ta.maxLength;
      const c = d.createElement('span');
      c.className = 'contador';
      ta.after(c);
      const atualizar = () => {
        c.textContent = ta.value.length + ' / ' + max;
        c.classList.toggle('contador--cheio', ta.value.length >= max);
      };
      ta.addEventListener('input', atualizar);
      atualizar();
    });
  });

  // Confirmação de ações destrutivas.
  d.addEventListener('click', (ev) => {
    const b = ev.target instanceof Element ? ev.target.closest('[data-confirmar]') : null;
    if (b && !confirm(b.dataset.confirmar)) ev.preventDefault();
  });

  // Menu móvel: fecha ao clicar fora ou com Esc.
  const fecharMenu = () => d.querySelectorAll('.menu[open]').forEach((m) => m.removeAttribute('open'));
  d.addEventListener('click', (ev) => { if (!(ev.target instanceof Element) || !ev.target.closest('.menu')) fecharMenu(); });
  d.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') fecharMenu(); });
})();
