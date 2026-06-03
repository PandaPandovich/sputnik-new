import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

const servicesSlider = document.querySelector('.services__items');

if (servicesSlider) {
    new Swiper(servicesSlider, {
        modules: [Navigation],
        speed: 400,
        slidesPerView: 'auto',
        spaceBetween: 8,
        navigation: {
            prevEl: '.services__nav-prev',
            nextEl: '.services__nav-next',
        },
    });
}