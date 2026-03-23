/**
 * Dynamic Features - Partitheco
 * Fonctionnalités dynamiques pour améliorer l'UX
 */

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initLazyLoading();
    initSmoothScroll();
    initCounterAnimations();
    initSearchAutocomplete();
    initFilterPersistence();
    initPullToRefresh();
    initSwipeNavigation();
});

/**
 * Animations au scroll (Intersection Observer)
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll(
        '.moment-card, .calendar-card, .temps-card, .stat-item, .project-item, .chant-jour-card, .publications-grid > *'
    );

    if (!animatedElements.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = `${index * 50}ms`;
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animatedElements.forEach(el => {
        el.classList.add('animate-ready');
        observer.observe(el);
    });

    // Ajouter les styles d'animation
    if (!document.getElementById('scroll-animation-styles')) {
        const style = document.createElement('style');
        style.id = 'scroll-animation-styles';
        style.textContent = `
            .animate-ready {
                opacity: 0;
                transform: translateY(30px);
            }
            .animate-in {
                animation: slideInUp 0.6s ease forwards;
            }
            @keyframes slideInUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Lazy loading des images
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if (!images.length) return;

    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    }, {
        rootMargin: '100px'
    });

    images.forEach(img => imageObserver.observe(img));
}

/**
 * Smooth scroll pour les ancres
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Animation des compteurs de statistiques
 */
function initCounterAnimations() {
    const counters = document.querySelectorAll('.stat-number, [data-counter]');
    
    if (!counters.length) return;

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));
}

function animateCounter(element) {
    const target = parseInt(element.textContent) || parseInt(element.dataset.counter) || 0;
    const duration = 1500;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

/**
 * Autocomplétion de recherche en temps réel
 */
function initSearchAutocomplete() {
    const searchInputs = document.querySelectorAll('#searchInput, .hero-search input[name="q"]');
    
    searchInputs.forEach(input => {
        if (!input) return;

        let debounceTimer;
        const resultsContainer = createAutocompleteContainer(input);

        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetchSearchSuggestions(query, resultsContainer, input);
            }, 300);
        });

        input.addEventListener('blur', () => {
            setTimeout(() => {
                resultsContainer.style.display = 'none';
            }, 200);
        });

        input.addEventListener('focus', () => {
            if (resultsContainer.children.length > 0) {
                resultsContainer.style.display = 'block';
            }
        });
    });
}

function createAutocompleteContainer(input) {
    let container = input.parentElement.querySelector('.autocomplete-results');
    if (!container) {
        container = document.createElement('div');
        container.className = 'autocomplete-results';
        container.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg, #fff);
            border: 1px solid var(--border-color, #ddd);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        `;
        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(container);
    }
    return container;
}

async function fetchSearchSuggestions(query, container, input) {
    try {
        const response = await fetch(`api/projects/search.php?q=${encodeURIComponent(query)}&limit=5`);
        const payload = await response.json();
        const results = Array.isArray(payload) ? payload : (payload.data || []);

        if (results.length > 0) {
            container.innerHTML = results.map(item => `
                <a href="project.php?id=${item.id}" class="autocomplete-item" style="
                    display: block;
                    padding: 10px 15px;
                    text-decoration: none;
                    color: var(--text-color, #333);
                    border-bottom: 1px solid var(--border-color, #eee);
                    transition: background 0.2s;
                ">
                    <strong>${highlightMatch(item.title, query)}</strong>
                    ${item.author ? `<br><small style="color: #666;">${item.author}</small>` : ''}
                </a>
            `).join('');
            container.style.display = 'block';

            // Hover effect
            container.querySelectorAll('.autocomplete-item').forEach(item => {
                item.addEventListener('mouseenter', () => {
                    item.style.background = 'var(--color-lit-gold, #f5f5f5)';
                });
                item.addEventListener('mouseleave', () => {
                    item.style.background = 'transparent';
                });
            });
        } else {
            container.innerHTML = '<div style="padding: 10px 15px; color: #666;">Aucun résultat</div>';
            container.style.display = 'block';
        }
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

function highlightMatch(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark style="background: var(--color-lit-gold, #ffeb3b); padding: 0 2px;">$1</mark>');
}

/**
 * Persistance des filtres dans l'URL et localStorage
 */
function initFilterPersistence() {
    const filterSelects = document.querySelectorAll('#momentFilter, #tempsFilter, #voixFilter');
    
    filterSelects.forEach(select => {
        // Restaurer depuis localStorage si pas dans l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const paramName = select.name;
        
        if (!urlParams.has(paramName)) {
            const saved = localStorage.getItem(`filter_${paramName}`);
            if (saved) {
                select.value = saved;
            }
        }

        // Sauvegarder les changements
        select.addEventListener('change', () => {
            localStorage.setItem(`filter_${select.name}`, select.value);
        });
    });
}

/**
 * Pull to refresh (mobile)
 */
function initPullToRefresh() {
    if (!('ontouchstart' in window)) return;

    let startY = 0;
    let pulling = false;
    const threshold = 100;

    document.addEventListener('touchstart', (e) => {
        if (window.scrollY === 0) {
            startY = e.touches[0].pageY;
            pulling = true;
        }
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (!pulling) return;
        
        const currentY = e.touches[0].pageY;
        const diff = currentY - startY;

        if (diff > 50 && diff < threshold) {
            showPullIndicator(diff / threshold);
        }
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        if (!pulling) return;
        
        const endY = e.changedTouches[0].pageY;
        const diff = endY - startY;

        if (diff >= threshold) {
            location.reload();
        }
        
        hidePullIndicator();
        pulling = false;
    });
}

function showPullIndicator(progress) {
    let indicator = document.getElementById('pull-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'pull-indicator';
        indicator.style.cssText = `
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: var(--color-lit-night, #1a2a44);
            color: white;
            padding: 10px 20px;
            border-radius: 0 0 10px 10px;
            z-index: 9999;
            transition: opacity 0.3s;
        `;
        indicator.textContent = '↓ Tirer pour actualiser';
        document.body.appendChild(indicator);
    }
    indicator.style.opacity = progress;
}

function hidePullIndicator() {
    const indicator = document.getElementById('pull-indicator');
    if (indicator) {
        indicator.style.opacity = '0';
        setTimeout(() => indicator.remove(), 300);
    }
}

/**
 * Navigation par swipe (mobile)
 */
function initSwipeNavigation() {
    if (!('ontouchstart' in window)) return;

    const pills = document.querySelector('.quick-nav-pills');
    if (!pills) return;

    let isScrolling = false;
    let startX = 0;

    pills.addEventListener('touchstart', (e) => {
        startX = e.touches[0].pageX;
        isScrolling = true;
    }, { passive: true });

    pills.addEventListener('touchmove', (e) => {
        if (!isScrolling) return;
        
        const currentX = e.touches[0].pageX;
        const diff = startX - currentX;
        
        pills.scrollLeft += diff * 0.5;
        startX = currentX;
    }, { passive: true });

    pills.addEventListener('touchend', () => {
        isScrolling = false;
    });
}

/**
 * Notification toast
 */
window.showToast = function(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        transition: transform 0.3s ease;
        max-width: 90vw;
        text-align: center;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    setTimeout(() => {
        toast.style.transform = 'translateX(-50%) translateY(100px)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
};

/**
 * Détection de connexion hors-ligne
 */
window.addEventListener('online', () => {
    showToast('Connexion rétablie', 'success');
});

window.addEventListener('offline', () => {
    showToast('Vous êtes hors-ligne', 'error', 5000);
});

/**
 * Préchargement des pages au survol
 */
document.querySelectorAll('a[href^="project.php"], a[href^="publications.php"]').forEach(link => {
    link.addEventListener('mouseenter', () => {
        const prefetch = document.createElement('link');
        prefetch.rel = 'prefetch';
        prefetch.href = link.href;
        document.head.appendChild(prefetch);
    }, { once: true });
});
