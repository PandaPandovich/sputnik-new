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
        document.getElementById('modalExperience').textContent = m.experience;
        document.getElementById('modalEducation').textContent = m.education;
        document.getElementById('modalBranch').textContent = m.branch;

        // Скрываем stats если все пустые
        const statsEl = modal.querySelector('.team-modal__stats');
        if (statsEl) {
            statsEl.style.display = (m.experience || m.education || m.branch) ? '' : 'none';
        }

        // Образование
        const eduText = document.getElementById('modalEduText');
        eduText.innerHTML = m.edu_text || '';
        toggle('modalEduSection', !!m.edu_text);

        // Опыт работы
        const workEl = document.getElementById('modalWork');
        const workItems = m.work || [];
        toggle('modalWorkSection', workItems.length > 0);
        workEl.innerHTML = workItems.map(item =>
            `<div class="team-modal__list-item">
                <span class="team-modal__list-period">${item.period || ''}</span>
                <span class="team-modal__list-text">${item.text || ''}</span>
            </div>`
        ).join('');

        // Повышение квалификации
        const coursesEl = document.getElementById('modalCourses');
        const courseItems = m.courses || [];
        toggle('modalCoursesSection', courseItems.length > 0);
        coursesEl.innerHTML = courseItems.map(item =>
            `<div class="team-modal__list-item">
                <span class="team-modal__list-date">${item.date || ''}</span>
                <span class="team-modal__list-text">${item.text || ''}</span>
            </div>`
        ).join('');

        // Заметка
        const noteText = document.getElementById('modalNoteText');
        noteText.textContent = m.note || '';
        toggle('modalNote', !!m.note);

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

    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('team-modal--active')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft' && currentIndex > 0) renderModal(currentIndex - 1);
        if (e.key === 'ArrowRight' && currentIndex < team.length - 1) renderModal(currentIndex + 1);
    });
});
