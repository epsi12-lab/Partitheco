// assets/js/allProjects.js
document.addEventListener('DOMContentLoaded', () => {
  const container   = document.getElementById('projectsContainer');
  const searchInput = document.getElementById('searchInput');
  const lang        = new URLSearchParams(location.search).get('lang') || 'fr';

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  function render(projects) {
    container.innerHTML = '';
    projects.forEach((p, idx) => {
      const ext     = p.thumbnail ? p.thumbnail.split('.').pop().toLowerCase() : '';
      const fileUrl = `assets/img/${p.thumbnail}`;
      const detail  = `project.php?id=${p.id}&lang=${lang}`;

      const div = document.createElement('div');
      div.className = 'project-item';
      div.style.animationDelay = `${idx * 100}ms`;
      div.style.animationPlayState = 'paused'; 

      let html = '';
      if (['jpg','jpeg','png','gif'].includes(ext)) {
        html += `<a href="${detail}">
                   <img src="${fileUrl}" class="project-thumbnail">
                 </a>`;
      } else if (ext === 'pdf') {
        html += `<a href="${fileUrl}" target="_blank">
                   <img src="assets/static/file-pdf-solid.svg" class="project-thumbnail pdf-icon">
                 </a>`;
      }
      html += `<h3><a href="${detail}">${p.title}</a></h3>`;
      html += `<p>${p.description.substring(0,100)}…</p>`;

      div.innerHTML = html;
      container.appendChild(div);

      observer.observe(div);
    });
  }

  async function loadAll() {
    try {
      const resp = await fetch(`/api/projects/getProjects.php?limit=1000&offset=0&lang=${lang}`);
      const data = await resp.json();
      render(data);
    } catch (err) {
      console.error('Erreur au chargement des publications :', err);
      container.innerHTML = '<p>Impossible de charger les publications.</p>';
    }
  }

  let timer;
  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
      const q = searchInput.value.trim();
      if (q === '') {
        loadAll();
      } else {
        try {
          const resp = await fetch(`/api/projects/search.php?q=${encodeURIComponent(q)}&lang=${lang}`);
          const data = await resp.json();
          render(data);
        } catch (err) {
          console.error('Erreur recherche :', err);
        }
      }
    }, 300);
  });

  loadAll();
});
