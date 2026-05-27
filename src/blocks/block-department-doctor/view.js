import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

const doctorSlider = document.querySelector('.dept-doctor__cards');
if ( doctorSlider ) {
    new Swiper( doctorSlider, {
        modules: [ Navigation ],
        slidesPerView: 1.3,
        spaceBetween: 24,
        navigation: {
            prevEl: '.dept-doctor__nav-prev',
            nextEl: '.dept-doctor__nav-next',
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
        },
    });
}

// Модальное окно врача
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deptDoctorModal');
    if (!modal) return;

    const dataEl = document.getElementById('deptDoctorData');
    if (!dataEl) return;

    const doctors = JSON.parse(dataEl.textContent);
    let currentIndex = 0;

    const overlay = modal.querySelector('.team-modal__overlay');
    const closeBtn = document.getElementById('deptDoctorModalClose');
    const prevBtn = document.getElementById('deptDoctorModalPrev');
    const nextBtn = document.getElementById('deptDoctorModalNext');
    const cards = document.querySelectorAll('.dept-doctor__card[data-doctor]');

    function toggle(id, show) {
        const el = document.getElementById(id);
        if (el) el.style.display = show ? '' : 'none';
    }

    function renderModal(index) {
        currentIndex = index;
        const m = doctors[index];

        document.getElementById('deptModalPhoto').src = m.photo || '';
        document.getElementById('deptModalPhoto').alt = m.name;
        document.getElementById('deptModalTag').textContent = m.tag;
        document.getElementById('deptModalName').textContent = m.name;
        document.getElementById('deptModalPosition').textContent = m.position;
        document.getElementById('deptModalExperience').textContent = m.experience;
        document.getElementById('deptModalEducation').textContent = m.education;
        document.getElementById('deptModalBranch').textContent = m.branch;

        // Скрываем stats если все пустые
        const statsEl = modal.querySelector('.team-modal__stats');
        if (statsEl) {
            statsEl.style.display = (m.experience || m.education || m.branch) ? '' : 'none';
        }

        // Образование
        const eduText = document.getElementById('deptModalEduText');
        eduText.innerHTML = m.edu_text || '';
        toggle('deptModalEduSection', !!m.edu_text);

        // Опыт работы
        const workEl = document.getElementById('deptModalWork');
        const workItems = m.work || [];
        toggle('deptModalWorkSection', workItems.length > 0);
        workEl.innerHTML = workItems.map(item =>
            `<div class="team-modal__list-item">
                <span class="team-modal__list-period">${item.period || ''}</span>
                <span class="team-modal__list-text">${item.text || ''}</span>
            </div>`
        ).join('');

        // Повышение квалификации
        const coursesEl = document.getElementById('deptModalCourses');
        const courseItems = m.courses || [];
        toggle('deptModalCoursesSection', courseItems.length > 0);
        coursesEl.innerHTML = courseItems.map(item =>
            `<div class="team-modal__list-item">
                <span class="team-modal__list-date">${item.date || ''}</span>
                <span class="team-modal__list-text">${item.text || ''}</span>
            </div>`
        ).join('');

        // Заметка
        const noteText = document.getElementById('deptModalNoteText');
        noteText.textContent = m.note || '';
        toggle('deptModalNote', !!m.note);

        // Тег
        toggle('deptModalTag', !!m.tag);

        // Навигация
        prevBtn.style.visibility = index > 0 ? 'visible' : 'hidden';
        nextBtn.style.visibility = index < doctors.length - 1 ? 'visible' : 'hidden';
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
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            const index = parseInt(card.dataset.doctor, 10);
            openModal(index);
        });
    });

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    prevBtn.addEventListener('click', () => {
        if (currentIndex > 0) renderModal(currentIndex - 1);
    });

    nextBtn.addEventListener('click', () => {
        if (currentIndex < doctors.length - 1) renderModal(currentIndex + 1);
    });

    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('team-modal--active')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft' && currentIndex > 0) renderModal(currentIndex - 1);
        if (e.key === 'ArrowRight' && currentIndex < doctors.length - 1) renderModal(currentIndex + 1);
    });
});
