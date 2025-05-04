// assets/js/validation.js
document.addEventListener('DOMContentLoaded', () => {
    // 1) Search (GET)
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
      searchForm.addEventListener('submit', e => {
        const q = document.getElementById('searchInput').value.trim();
        if (!q) {
          e.preventDefault();
          alert('Veuillez saisir un terme de recherche.');
        }
      });
    }
  
    // 2) Newsletter
    const nlForm = document.querySelector('.newsletter-form');
    if (nlForm) {
      nlForm.addEventListener('submit', e => {
        const email = nlForm.querySelector('input[name="newsletter_email"]').value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          e.preventDefault();
          alert('Veuillez saisir une adresse email valide pour la newsletter.');
        }
      });
    }
  
    // 3) Commentaires
    const cForm = document.querySelector('.comment-form');
    if (cForm) {
      cForm.addEventListener('submit', e => {
        const author  = cForm.querySelector('input[name="comment_author"]').value.trim();
        const content = cForm.querySelector('textarea[name="comment_content"]').value.trim();
        if (!author || !content) {
          e.preventDefault();
          alert('Votre nom et votre commentaire sont requis.');
        }
      });
    }
  
    // 4) Contact (contact.php)
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
      contactForm.addEventListener('submit', e => {
        const email = contactForm.querySelector('input[name="email"]').value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          e.preventDefault();
          alert('Veuillez saisir une adresse email valide.');
        }
      });
    }
  });
  