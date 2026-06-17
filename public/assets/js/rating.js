// rating.js - Gestion des étoiles de notation
document.addEventListener('DOMContentLoaded', () => {
    const ratingSection = document.querySelector('.rating-section');
    if (!ratingSection) return;

    const projectId = ratingSection.dataset.projectId;
    const starBtns = ratingSection.querySelectorAll('.star-btn');
    const starsDisplay = ratingSection.querySelectorAll('.stars-display .star');
    const ratingInfo = ratingSection.querySelector('.rating-info');

    starBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const score = parseInt(btn.dataset.value);
            
            try {
                const response = await fetch('/api/rate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.getCsrfToken?.() || ''
                    },
                    body: JSON.stringify({ project_id: parseInt(projectId), score })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Mettre à jour les étoiles utilisateur
                    starBtns.forEach((s, i) => {
                        s.classList.toggle('active', i < score);
                    });
                    
                    // Mettre à jour l'affichage moyen
                    starsDisplay.forEach((s, i) => {
                        s.classList.toggle('filled', i < Math.round(data.average));
                    });
                    
                    // Mettre à jour le texte
                    ratingInfo.textContent = `${data.average}/5 (${data.count} avis)`;
                    
                    // Feedback visuel
                    ratingSection.style.borderColor = 'var(--color-lit-gold)';
                    setTimeout(() => {
                        ratingSection.style.borderColor = '';
                    }, 500);
                } else {
                    alert(data.error || 'Erreur lors de la notation');
                }
            } catch (err) {
                console.error('Erreur:', err);
                alert('Erreur de connexion');
            }
        });

        // Effet hover
        btn.addEventListener('mouseenter', () => {
            const val = parseInt(btn.dataset.value);
            starBtns.forEach((s, i) => {
                s.style.color = i < val ? 'var(--color-lit-gold)' : '';
            });
        });
    });

    // Reset hover
    const userRating = ratingSection.querySelector('.user-rating');
    if (userRating) {
        userRating.addEventListener('mouseleave', () => {
            starBtns.forEach(s => {
                s.style.color = '';
            });
        });
    }
});
