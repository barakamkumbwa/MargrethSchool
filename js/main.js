/* =========================================
   MARGRETH PRE & PRIMARY SCHOOL
   Main JavaScript File
   ========================================= */

document.addEventListener("DOMContentLoaded", function() {
    
    // Hapa utaweka JavaScript nyingine baadaye
    // (Kama FAQ Accordion, Form Validation, n.k.)
    
    console.log("Margreth School Website Loaded Successfully!");

});
    /* --- 2. ACCORDION FUNCTIONALITY (Card Slide Animation) --- */
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        
        header.addEventListener('click', () => {
            
            // Kama kitu hiki kimefunguliwa (active)
            if (item.classList.contains('active')) {
                
                // 1. Ongeza class ya 'closing' kwa card nzima
                item.classList.add('closing');
                item.classList.remove('active');
                
                // 2. Baada ya animation (0.5s), ondoa 'closing'
                setTimeout(() => {
                    item.classList.remove('closing');
                    
                    // 3. Fungua card ya kwanza kwa animation ya 'opening'
                    const firstItem = accordionItems[0];
                    firstItem.classList.add('opening');
                    
                    setTimeout(() => {
                        firstItem.classList.remove('opening');
                        firstItem.classList.add('active');
                    }, 100);
                    
                }, 500);
                
            } else {
                
                // 1. Kama kuna card nyingine imefunguliwa, ifunge kwa animation
                accordionItems.forEach(otherItem => {
                    if (otherItem.classList.contains('active')) {
                        otherItem.classList.add('closing');
                        otherItem.classList.remove('active');
                        
                        setTimeout(() => {
                            otherItem.classList.remove('closing');
                        }, 500);
                    }
                });
                
                // 2. Fungua card uliyobonyeza kwa animation ya 'opening' baada ya muda
                setTimeout(() => {
                    item.classList.add('opening');
                    
                    setTimeout(() => {
                        item.classList.remove('opening');
                        item.classList.add('active');
                    }, 100);
                    
                }, 500);
            }
        });
    });



        /* --- 3. ABOUT SECTION ANIMATION (Card by Card) --- */
    const statCards = document.querySelectorAll('.stat-card');
    const aboutImages = document.querySelectorAll('.about-image');

    // Weka kila card ikiwa na animation delay tofauti
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `all 0.6s ease ${index * 0.15}s`;
    });

    // Weka kila picha ikiwa na animation delay tofauti
    aboutImages.forEach((img, index) => {
        img.style.opacity = '0';
        img.style.transform = 'translateY(30px)';
        img.style.transition = `all 0.6s ease ${index * 0.2 + 0.3}s`;
    });

    // Tumia IntersectionObserver ili kuanza animation pale section inapofika kwenye screen
    const aboutSection = document.querySelector('.about-new-section');
    
    if (aboutSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Cards zinaingia
                    statCards.forEach(card => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    });
                    
                    // Picha zinaingia
                    aboutImages.forEach(img => {
                        img.style.opacity = '1';
                        img.style.transform = 'translateY(0)';
                    });
                    
                    // Acha kuangalia baada ya kuanza
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.2 // Inaanza pale 20% ya section inaonekana
        });

        observer.observe(aboutSection);
    }



        /* --- 4. ACTIVE NAVIGATION LINK ON SCROLL --- */
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('nav ul li a');

    window.addEventListener('scroll', () => {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 150;
            const sectionHeight = section.clientHeight;
            
            if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });


    /* --- 5. GALLERY FILTER --- */
const filterBtns = document.querySelectorAll('.filter-btn');
const galleryItems = document.querySelectorAll('.gallery-item');

if (filterBtns.length > 0 && galleryItems.length > 0) {
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Ondoa 'active' kwenye button zote
            filterBtns.forEach(b => b.classList.remove('active'));
            // Weka 'active' kwenye button iliyobonyezwa
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');

            galleryItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}




    /* --- 6. GALLERY SLIDESHOW (Auto-Rotate) --- */
    const slides = document.querySelectorAll('.gallery-slides .slide');
    
    if (slides.length > 0) {
        let currentSlide = 0;
        const slideInterval = 5000; // Sekunde 5 kwa kila picha

        function nextSlide() {
            // Ondoa 'active' kwenye slide ya sasa
            slides[currentSlide].classList.remove('active');
            
            // Nenda kwenye slide inayofuata
            currentSlide = (currentSlide + 1) % slides.length;
            
            // Weka 'active' kwenye slide mpya
            slides[currentSlide].classList.add('active');
        }

        // Anza auto-rotate
        setInterval(nextSlide, slideInterval);
    }
/* --- GALLERY IMAGE POPUP --- */
const galleryZoomLinks = document.querySelectorAll('.gallery-zoom');

if (galleryZoomLinks.length > 0) {
    const imagePopup = document.createElement('div');
    imagePopup.className = 'gallery-image-popup';
    imagePopup.setAttribute('role', 'dialog');
    imagePopup.setAttribute('aria-modal', 'true');
    imagePopup.setAttribute('aria-label', 'Image preview');
    imagePopup.innerHTML = '<button class="gallery-popup-close" type="button" aria-label="Close image preview">&times;</button><img src="" alt="">';
    document.body.appendChild(imagePopup);

    const popupImage = imagePopup.querySelector('img');
    const closePopup = () => {
        imagePopup.classList.remove('is-open');
        popupImage.src = '';
    };

    galleryZoomLinks.forEach(link => {
        link.addEventListener('click', event => {
            event.preventDefault();
            popupImage.src = link.href;
            popupImage.alt = link.closest('.gallery-item')?.querySelector('img')?.alt || 'Gallery image';
            imagePopup.classList.add('is-open');
        });
    });

    imagePopup.querySelector('.gallery-popup-close').addEventListener('click', closePopup);
    imagePopup.addEventListener('click', event => {
        if (event.target === imagePopup) closePopup();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closePopup();
    });
}

/* --- AUTOMATIC NEWS SLIDER --- */
const newsSlider = document.querySelector('.news-slider');

if (newsSlider) {
    const newsSlides = Array.from(newsSlider.querySelectorAll('.news-slide'));
    const dotsContainer = newsSlider.querySelector('.news-slider-dots');
    const previousButton = newsSlider.querySelector('.news-slider-prev');
    const nextButton = newsSlider.querySelector('.news-slider-next');
    let activeNewsIndex = 0;
    let newsTimer;
    let leaveTimer;

    const showNews = index => {
        const nextNewsIndex = (index + newsSlides.length) % newsSlides.length;
        const currentSlide = newsSlides[activeNewsIndex];
        const nextSlide = newsSlides[nextNewsIndex];

        window.clearTimeout(leaveTimer);
        newsSlides.forEach(slide => slide.classList.remove('is-leaving'));

        if (nextNewsIndex !== activeNewsIndex) {
            currentSlide.classList.remove('is-active');
            currentSlide.classList.add('is-leaving');
            leaveTimer = window.setTimeout(() => currentSlide.classList.remove('is-leaving'), 700);
            nextSlide.classList.add('is-active');
        }

        activeNewsIndex = nextNewsIndex;
        newsSlides.forEach((slide, slideIndex) => {
            slide.setAttribute('aria-hidden', String(slideIndex !== activeNewsIndex));
            dotsContainer.children[slideIndex].classList.toggle('is-active', slideIndex === activeNewsIndex);
            dotsContainer.children[slideIndex].setAttribute('aria-current', slideIndex === activeNewsIndex ? 'true' : 'false');
        });
    };

    newsSlides.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'news-slider-dot';
        dot.setAttribute('aria-label', `Show news ${index + 1}`);
        dot.addEventListener('click', () => {
            showNews(index);
            restartNewsTimer();
        });
        dotsContainer.appendChild(dot);
    });

    const restartNewsTimer = () => {
        window.clearInterval(newsTimer);
        newsTimer = window.setInterval(() => showNews(activeNewsIndex + 1), 5000);
    };

    previousButton.addEventListener('click', () => {
        showNews(activeNewsIndex - 1);
        restartNewsTimer();
    });
    nextButton.addEventListener('click', () => {
        showNews(activeNewsIndex + 1);
        restartNewsTimer();
    });
    showNews(0);
    restartNewsTimer();
}

/* --- ADMISSIONS REQUIREMENTS AND FEES REVEAL --- */
const admissionInfoCards = document.querySelectorAll('.requirements-box, .fees-box');

if (admissionInfoCards.length > 0) {
    if ('IntersectionObserver' in window) {
        document.documentElement.classList.add('admissions-reveal-ready');
        const admissionCardObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.18 });

        admissionInfoCards.forEach(card => admissionCardObserver.observe(card));
    } else {
        admissionInfoCards.forEach(card => card.classList.add('is-visible'));
    }
}

/* --- SCHOOL-THEMED INTERNAL PAGE LOADER --- */
const schoolPageLoader = document.createElement('div');
schoolPageLoader.className = 'site-page-loader';
schoolPageLoader.setAttribute('aria-hidden', 'true');
schoolPageLoader.innerHTML = '<span class="site-loader-spinner" role="status" aria-label="Loading"></span>';
document.body.appendChild(schoolPageLoader);

const initialLoaderStartedAt = performance.now();
schoolPageLoader.classList.add('is-active');
schoolPageLoader.setAttribute('aria-hidden', 'false');

const hideInitialLoader = () => {
    const minimumVisibleTime = 650;
    const remainingTime = Math.max(0, minimumVisibleTime - (performance.now() - initialLoaderStartedAt));
    window.setTimeout(() => {
        schoolPageLoader.classList.remove('is-active');
        schoolPageLoader.setAttribute('aria-hidden', 'true');
    }, remainingTime);
};

if (document.readyState === 'complete') {
    hideInitialLoader();
} else {
    window.addEventListener('load', hideInitialLoader, { once: true });
}

document.addEventListener('click', event => {
    const link = event.target.closest('a');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if ((link.target && link.target !== '_self') || link.hasAttribute('download')) return;

    const destination = new URL(link.href, window.location.href);
    if (destination.origin !== window.location.origin || destination.pathname === window.location.pathname) return;

    event.preventDefault();
    schoolPageLoader.classList.add('is-active');
    schoolPageLoader.setAttribute('aria-hidden', 'false');
    window.setTimeout(() => window.location.assign(destination.href), 800);
});

/* --- BACK TO TOP BUTTON --- */
const backToTopButton = document.createElement('button');
backToTopButton.className = 'back-to-top';
backToTopButton.type = 'button';
backToTopButton.setAttribute('aria-label', 'Back to top');
backToTopButton.setAttribute('aria-hidden', 'true');
backToTopButton.disabled = true;
backToTopButton.innerHTML = '<i class="fas fa-arrow-up" aria-hidden="true"></i>';
document.body.appendChild(backToTopButton);

const updateBackToTopVisibility = () => {
    const isVisible = window.scrollY > 320;
    backToTopButton.classList.toggle('is-visible', isVisible);
    backToTopButton.setAttribute('aria-hidden', String(!isVisible));
    backToTopButton.disabled = !isVisible;
};

window.addEventListener('scroll', updateBackToTopVisibility, { passive: true });
updateBackToTopVisibility();

backToTopButton.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
    });
});

/* --- CONTACT FORM DELIVERY FEEDBACK --- */
const contactFormStatus = document.querySelector('#contact-form-status');
if (contactFormStatus) {
    const formStatus = new URLSearchParams(window.location.search).get('form');
    const statusMessages = {
        sent: ['Message sent successfully. Thank you for contacting us.', 'success'],
        invalid: ['Please check your details and try sending the form again.', 'error'],
        error: ['Your message could not be sent right now. Please try again later or contact us by phone.', 'error']
    };

    if (statusMessages[formStatus]) {
        const [message, type] = statusMessages[formStatus];
        contactFormStatus.textContent = message;
        contactFormStatus.classList.add(`form-status--${type}`);
        contactFormStatus.hidden = false;
    }
}
