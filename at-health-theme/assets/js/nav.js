/**
 * AT Health — Navigation JS
 * Handles mobile menu toggle, submenu accordions, and keyboard accessibility.
 */
(function () {
    'use strict';

    const menuToggle = document.getElementById('menuToggle');
    const menuClose = document.getElementById('menuClose');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

    function openMenu() {
        if (!mobileMenu || !mobileMenuOverlay) return;
        mobileMenu.classList.add('open');
        mobileMenu.classList.remove('translate-x-full');
        mobileMenuOverlay.classList.add('open');
        mobileMenuOverlay.classList.remove('opacity-0', 'pointer-events-none');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        if (!mobileMenu || !mobileMenuOverlay) return;
        mobileMenu.classList.remove('open');
        mobileMenu.classList.add('translate-x-full');
        mobileMenuOverlay.classList.remove('open');
        mobileMenuOverlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }

    if (menuToggle) menuToggle.addEventListener('click', openMenu);
    if (menuClose) menuClose.addEventListener('click', closeMenu);
    if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMenu);

    // Close on link click
    if (mobileMenu) {
        mobileMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });
    }

    // Escape key closes menu
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });

    // Mobile submenu accordion
    document.querySelectorAll('[data-mobile-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-mobile-toggle');
            var submenu = document.getElementById(targetId);
            var icon = btn.querySelector('.mobile-toggle-icon');

            if (!submenu) return;

            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        });
    });
})();
