import Swiper from 'swiper';

const servicesSlider = document.querySelector('.services__items');

if (servicesSlider) {
    new Swiper(servicesSlider, {
        speed: 400,
        slidesPerView: 'auto',
        spaceBetween: 8,
    });
}