import './bootstrap';
import Alpine from 'alpinejs';
import { initFlowbite } from 'flowbite';
import Swiper from 'swiper';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Alpine = Alpine;
Alpine.start();

const initSwipers = () => {
    document.querySelectorAll('[data-swiper="hero"]').forEach((element) => {
        if (element.dataset.swiperReady === 'true') {
            return;
        }

        const wrapper = element.closest('[data-swiper-wrapper]');

        new Swiper(element, {
            modules: [Autoplay, Navigation, Pagination],
            slidesPerView: 1,
            spaceBetween: 24,
            speed: 850,
            loop: true,
            autoplay: {
                delay: 3200,
                disableOnInteraction: false,
            },
            pagination: {
                el: wrapper?.querySelector('[data-swiper-pagination]') ?? null,
                clickable: true,
            },
            navigation: {
                nextEl: wrapper?.querySelector('[data-swiper-next]') ?? null,
                prevEl: wrapper?.querySelector('[data-swiper-prev]') ?? null,
            },
        });

        element.dataset.swiperReady = 'true';
    });
};

const bootUi = () => {
    initFlowbite();
    initSwipers();
};

let bootQueued = false;

const scheduleBootUi = () => {
    if (bootQueued) {
        return;
    }

    bootQueued = true;

    requestAnimationFrame(() => {
        bootQueued = false;
        bootUi();
    });
};

const observeUiChanges = () => {
    if (!document.body) {
        return;
    }

    const observer = new MutationObserver(() => {
        scheduleBootUi();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
};

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        () => {
            observeUiChanges();
            scheduleBootUi();
        },
        { once: true }
    );
} else {
    observeUiChanges();
    scheduleBootUi();
}

document.addEventListener('livewire:initialized', scheduleBootUi);
document.addEventListener('livewire:navigated', scheduleBootUi);
