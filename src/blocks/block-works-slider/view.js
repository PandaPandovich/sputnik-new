import Swiper from 'swiper';
import { Grid, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/grid';
import 'swiper/css/navigation';
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.works__slider').forEach((el) => {
        const block = el.closest('.works');
        const prevEl = block ? block.querySelector('.works__nav-prev') : null;
        const nextEl = block ? block.querySelector('.works__nav-next') : null;
        new Swiper(el, {
            modules: [Grid, Navigation],
            slidesPerView: 1,
            spaceBetween: 20,
            navigation: prevEl && nextEl ? { prevEl, nextEl } : false,
            breakpoints: {
                768: {
                    slidesPerView: 3.2,
                    grid: {
                        rows: 2,
                        fill: 'row',
                    },
                }
            }
        });
    });
});
