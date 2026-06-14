/**
 * Hero-слайдер на базе Swiper.
 */
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hero__slider').forEach((el) => {
        // Если в слайдере только один слайд — Swiper не нужен.
        if (!el.classList.contains('hero__slider--multi')) return;

        const wrap = el.closest('.hero__wrap') || el.parentElement;

        new Swiper(el, {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: el.querySelector('.hero__pagination'),
                clickable: true,
            },
            navigation: {
                prevEl: wrap.querySelector('.hero__nav--prev'),
                nextEl: wrap.querySelector('.hero__nav--next'),
            },
        });
    });
});
