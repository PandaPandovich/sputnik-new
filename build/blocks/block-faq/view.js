/******/ (() => { // webpackBootstrap
/*!**************************************!*\
  !*** ./src/blocks/block-faq/view.js ***!
  \**************************************/
/**
 * Аккордеон FAQ — раскрытие/скрытие ответов.
 */
document.addEventListener('DOMContentLoaded', () => {
  const items = document.querySelectorAll('.faq__item');
  items.forEach(item => {
    const question = item.querySelector('.faq__question');
    question.addEventListener('click', () => {
      const isActive = item.classList.contains('is-active');

      // Закрыть все
      items.forEach(i => i.classList.remove('is-active'));

      // Открыть текущий если был закрыт
      if (!isActive) {
        item.classList.add('is-active');
      }
    });
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map