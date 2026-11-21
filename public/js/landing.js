/**
 * SCOMAR — Scomberomorus Marine
 * Official Landing Page Interactive Scripts
 * Lightweight, accessible, no external dependencies.
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. Navbar Scroll Transition
    const navbar = document.querySelector('.landing-nav');
    function updateNavbar() {
        if (!navbar) return;
        if (window.scrollY > 40) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
    window.addEventListener('scroll', updateNavbar, { passive: true });
    updateNavbar();

    // 2. Mobile Drawer Navigation Toggle
    const mobileToggle = document.querySelector('.mobile-toggle');
    const mobileDrawer = document.querySelector('.mobile-drawer');

    if (mobileToggle && mobileDrawer) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = mobileDrawer.classList.toggle('open');
            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close drawer on clicking link
        mobileDrawer.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileDrawer.classList.remove('open');
                mobileToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Close on clicking outside
        document.addEventListener('click', function (e) {
            if (!mobileToggle.contains(e.target) && !mobileDrawer.contains(e.target)) {
                mobileDrawer.classList.remove('open');
                mobileToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 3. Smooth Scrolling with Offset for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#') return;
            const targetElem = document.querySelector(targetId);
            if (targetElem) {
                e.preventDefault();
                const navHeight = (navbar && navbar.offsetHeight) ? navbar.offsetHeight : 70;
                const elementPosition = targetElem.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - navHeight;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // 4. Scroll Reveal via IntersectionObserver (Progressive Enhancement)
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealElements = document.querySelectorAll('.reveal-on-scroll');

    if (!prefersReducedMotion && 'IntersectionObserver' in window && revealElements.length > 0) {
        document.body.classList.add('reveal-init');
        const observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -20px 0px'
        });

        revealElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    // 5. Deep link & URL query parameter instant scroll support
    const urlParams = new URLSearchParams(window.location.search);
    const scrollParam = urlParams.get('scroll');
    if (scrollParam) {
        const y = parseInt(scrollParam, 10);
        window.scrollTo({ top: y, behavior: 'instant' });
        revealElements.forEach(function (el) { el.classList.add('is-visible'); });
        updateNavbar();
    } else if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            const navH = (navbar && navbar.offsetHeight) ? navbar.offsetHeight : 70;
            const topPos = target.getBoundingClientRect().top + window.pageYOffset - navH;
            window.scrollTo({ top: topPos, behavior: 'instant' });
            revealElements.forEach(function (el) { el.classList.add('is-visible'); });
            updateNavbar();
        }
    }
});