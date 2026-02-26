// assets/js/base.js

document.addEventListener('DOMContentLoaded', () => {
    // Thème (Mode Sombre)
    const themeToggle = document.getElementById('themeToggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    // Modal PDF & Lightbox
    const modal = document.createElement('div');
    modal.id = 'universal-modal';
    modal.className = 'universal-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div id="modal-body"></div>
        </div>
    `;
    document.body.appendChild(modal);

    const modalBody = modal.querySelector('#modal-body');
    const modalClose = modal.querySelector('.modal-close');

    modalClose.onclick = () => modal.style.display = 'none';
    window.onclick = (event) => { if (event.target == modal) modal.style.display = 'none'; };

    window.openPDF = (url) => {
        modalBody.innerHTML = `<iframe src="${url}" width="100%" height="600px" style="border:none;"></iframe>`;
        modal.style.display = 'block';
    };

    window.openLightbox = (url) => {
        modalBody.innerHTML = `<img src="${url}" style="width:100%; height:auto; border-radius:4px;">`;
        modal.style.display = 'block';
    };

    themeToggle?.addEventListener('click', () => {
        let theme = document.documentElement.getAttribute('data-theme');
        if (theme === 'dark') {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
    });

    // Menu Burger
    const burgerMenu = document.getElementById('burgerMenu');
    const navbarMenu = document.getElementById('navbarMenu');

    burgerMenu?.addEventListener('click', () => {
        navbarMenu.classList.toggle('active');
        burgerMenu.textContent = navbarMenu.classList.contains('active') ? '✕' : '☰';
    });

    // Toasts
    window.showToast = (message, type = 'info') => {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${message}</span>`;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    };

    // Spinners sur les boutons et Barre de progression d'upload
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            const progressContainer = document.getElementById('uploadProgressContainer');
            const progressBar = document.getElementById('uploadProgressBar');
            const progressText = document.getElementById('uploadProgressText');
            const hasFiles = this.querySelector('input[type="file"]')?.files.length > 0;

            if (btn && !btn.classList.contains('btn-no-spinner')) {
                const originalContent = btn.innerHTML;
                btn.classList.add('btn-loading');
                btn.innerHTML = `<span class="spinner"></span> ${btn.textContent}`;
            }

            // Si c'est un formulaire avec fichiers et qu'on a les éléments de progression
            if (hasFiles && progressContainer && progressBar && progressText) {
                e.preventDefault(); // On gère l'envoi en AJAX pour avoir la progression
                
                progressContainer.style.display = 'block';
                const formData = new FormData(this);
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressText.textContent = percent + '%';
                    }
                });

                xhr.onreadystatechange = () => {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Succès : on redirige ou on affiche un message
                            // Puisque c'est du PHP classique à la base, on check si le serveur a renvoyé une redirection
                            if (xhr.responseURL) {
                                window.location.href = xhr.responseURL;
                            } else {
                                // Fallback si pas de responseURL
                                location.reload();
                            }
                        } else {
                            // Erreur
                            window.showToast("Erreur lors de l'envoi du fichier", "error");
                            btn.classList.remove('btn-loading');
                            btn.innerHTML = btn.textContent;
                        }
                    }
                };

                xhr.open('POST', this.action || window.location.href, true);
                xhr.send(formData);
            }
        });
    });

    const items = document.querySelectorAll('.project-item');
    const obs   = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animationPlayState = 'running';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });
  
    items.forEach(item => {
      item.style.animationPlayState = 'paused';
      obs.observe(item);
    });

    const detail = document.querySelector('.project-detail');
    if (detail) {
      void detail.offsetWidth;
    }
});
  
  
