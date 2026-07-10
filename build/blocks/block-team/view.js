/******/ (() => { // webpackBootstrap
/*!***************************************!*\
  !*** ./src/blocks/block-team/view.js ***!
  \***************************************/
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('teamModal');
  if (!modal) return;
  const dataEl = document.getElementById('teamData');
  if (!dataEl) return;
  const team = JSON.parse(dataEl.textContent);
  let currentIndex = 0;
  const overlay = modal.querySelector('.team-modal__overlay');
  const closeBtn = document.getElementById('teamModalClose');
  const prevBtn = document.getElementById('teamModalPrev');
  const nextBtn = document.getElementById('teamModalNext');
  const cards = document.querySelectorAll('.team__item[data-member]');
  function toggle(id, show) {
    const el = document.getElementById(id);
    if (el) el.style.display = show ? '' : 'none';
  }
  function renderModal(index) {
    currentIndex = index;
    const m = team[index];
    document.getElementById('modalPhoto').src = m.photo || '';
    document.getElementById('modalPhoto').alt = m.name;
    document.getElementById('modalTag').textContent = m.tag;
    document.getElementById('modalName').textContent = m.name;
    document.getElementById('modalPosition').textContent = m.position;

    // Образование
    const eduText = document.getElementById('modalEduText');
    eduText.innerHTML = m.edu_text || '';
    toggle('modalEduSection', !!m.edu_text);

    // Тег
    toggle('modalTag', !!m.tag);

    // Навигация
    prevBtn.style.visibility = index > 0 ? 'visible' : 'hidden';
    nextBtn.style.visibility = index < team.length - 1 ? 'visible' : 'hidden';
  }
  function openModal(index) {
    renderModal(index);
    modal.classList.add('team-modal--active');
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    modal.classList.remove('team-modal--active');
    document.body.style.overflow = '';
  }
  cards.forEach(card => {
    card.addEventListener('click', () => {
      const index = parseInt(card.dataset.member, 10);
      openModal(index);
    });
  });
  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', closeModal);
  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) renderModal(currentIndex - 1);
  });
  nextBtn.addEventListener('click', () => {
    if (currentIndex < team.length - 1) renderModal(currentIndex + 1);
  });
  document.addEventListener('keydown', e => {
    if (!modal.classList.contains('team-modal--active')) return;
    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowLeft' && currentIndex > 0) renderModal(currentIndex - 1);
    if (e.key === 'ArrowRight' && currentIndex < team.length - 1) renderModal(currentIndex + 1);
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map