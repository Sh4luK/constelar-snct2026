document.addEventListener('click', (ev) => {
  const b = ev.target.closest('[data-confirmar]');
  if (b && !confirm(b.dataset.confirmar)) ev.preventDefault();
});
