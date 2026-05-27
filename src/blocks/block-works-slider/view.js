import Swiper from 'swiper';
import { Grid } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/grid';
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.works__slider').forEach((el) => {
        new Swiper(el, {
            modules: [Grid],
            slidesPerView: 1,
            spaceBetween: 20,
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
