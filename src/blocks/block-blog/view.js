import Swiper from 'swiper';
import 'swiper/css';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.blog__slider').forEach((el) => {
        new Swiper(el, {
            slidesPerView: 1.2,
            spaceBetween: 20,
            loop: true,
            breakpoints: {
                768: {
                    slidesPerView: 3,
                }
            }
        });
    });
});
