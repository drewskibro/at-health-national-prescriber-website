/**
 * AT Health — Scroll Reveal System
 * Handles scroll-triggered animations: reveal, stagger, line-grow, count-up.
 */
(function () {
    'use strict';

    // ── Reveal elements on scroll ──
    var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('[data-reveal]').forEach(function (el) {
        revealObserver.observe(el);
    });

    // ── Stagger children — assign index CSS var ──
    document.querySelectorAll('[data-stagger]').forEach(function (container) {
        var children = container.querySelectorAll('[data-reveal]');
        children.forEach(function (child, i) {
            child.style.setProperty('--stagger-index', i);
        });
    });

    // ── Journey items ──
    var journeyObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                entry.target.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
                journeyObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('[data-journey-item]').forEach(function (item) {
        item.style.transform = 'translateY(40px)';
        journeyObserver.observe(item);
    });

    // ── Animated lines in section headers ──
    var lineObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var line = entry.target.querySelector('.animated-line');
                if (line && line.style.width === '0px') {
                    line.style.transition = 'width 0.8s ease-out 0.3s';
                    line.style.width = '100%';
                }
                lineObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.section-header').forEach(function (header) {
        lineObserver.observe(header);
    });

    // ── Count-up animation for numbers ──
    var countObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var el = entry.target;
                var target = parseInt(el.getAttribute('data-count'), 10);
                var suffix = el.getAttribute('data-suffix') || '';
                var duration = 1800;
                var start = performance.now();

                function easeOutQuart(t) {
                    return 1 - Math.pow(1 - t, 4);
                }

                function tick(now) {
                    var elapsed = now - start;
                    var progress = Math.min(elapsed / duration, 1);
                    var value = Math.floor(easeOutQuart(progress) * target);
                    el.textContent = value.toLocaleString() + suffix;
                    if (progress < 1) requestAnimationFrame(tick);
                }

                requestAnimationFrame(tick);
                countObserver.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(function (el) {
        countObserver.observe(el);
    });

    // ── FAQ Accordion ──
    window.toggleFaq = function (btn) {
        var item = btn.closest('div');
        var body = item.querySelector('.faq-body');
        var icon = item.querySelector('.faq-icon');
        var accordion = btn.closest('[id$="Accordion"], .ah-faq-accordion');
        var isOpen = body.style.maxHeight && body.style.maxHeight !== '0px';

        // Close all others in the same accordion
        if (accordion) {
            accordion.querySelectorAll('.faq-body').forEach(function (b) {
                b.style.maxHeight = '0px';
            });
            accordion.querySelectorAll('.faq-icon').forEach(function (i) {
                i.style.transform = 'rotate(0deg)';
            });
            accordion.querySelectorAll(':scope > div').forEach(function (d) {
                d.classList.remove('border-purple-300', 'shadow-md');
            });
        }

        // Toggle current
        if (!isOpen) {
            body.style.maxHeight = body.scrollHeight + 'px';
            if (icon) icon.style.transform = 'rotate(180deg)';
            item.classList.add('border-purple-300', 'shadow-md');
        }
    };
})();
