// Reflexo especular do vidro segue o ponteiro (só em dispositivos com hover).
(() => {
  if (!window.matchMedia('(hover: hover)').matches || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  let alvo = null, quadro = 0, px = 0, py = 0;
  const aplicar = () => {
    quadro = 0;
    if (!alvo) return;
    const r = alvo.getBoundingClientRect();
    alvo.style.setProperty('--mx', ((px - r.left) / r.width) * 100 + '%');
    alvo.style.setProperty('--my', ((py - r.top) / r.height) * 100 + '%');
  };
  document.addEventListener('pointermove', (ev) => {
    const g = ev.target instanceof Element ? ev.target.closest('.glass') : null;
    if (alvo && alvo !== g) { alvo.style.removeProperty('--mx'); alvo.style.removeProperty('--my'); }
    alvo = g;
    px = ev.clientX; py = ev.clientY;
    if (g && !quadro) quadro = requestAnimationFrame(aplicar);
  }, { passive: true });
})();
