/**
 * Слайдер отзывов на базе Swiper.
 */
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('DOMContentLoaded', () => {
    const reviewsSlider = document.querySelector('.reviews__slider');
    if (!reviewsSlider) return;

    new Swiper(reviewsSlider, {
        modules: [Navigation, Pagination],
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
        pagination: {
            el: '.reviews__pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.reviews__next',
            prevEl: '.reviews__prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 4,
            },
        },
    });
});
