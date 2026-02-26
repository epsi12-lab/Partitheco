// assets/js/allProjects.js
document.addEventListener('DOMContentLoaded', () => {
  const container   = document.getElementById('projectsContainer');
  const searchInput = document.getElementById('searchInput');
  const momentFilter = document.getElementById('momentFilter');
  const tempsFilter  = document.getElementById('tempsFilter');
  const lang        = new URLSearchParams(location.search).get('lang') || 'fr';

  let offset = 0;
  const limit = 6;
  let loading = false;
  let allLoaded = false;
  let currentSearch = '';
  let currentMoment = '';
  let currentTemps = '';

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  // Observer pour l'Infinite Scroll
  const scrollObserver = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !loading && !allLoaded && currentSearch === '' && currentMoment === '' && currentTemps === '') {
      loadMore();
    }
  }, { threshold: 0.1 });

  function createProjectCard(p, idx) {
    const ext     = p.thumbnail ? p.thumbnail.split('.').pop().toLowerCase() : '';
    const fileUrl = `assets/img/${p.thumbnail}`;
    const detail  = `project.php?id=${p.id}&lang=${lang}`;

    const div = document.createElement('div');
    div.className = 'project-item';
    div.style.animationDelay = `${(idx % limit) * 100}ms`;
    div.style.animationPlayState = 'paused'; 

    let html = '';
    if (['jpg','jpeg','png','gif'].includes(ext)) {
      html += `<a href="javascript:void(0)" onclick="openLightbox('${fileUrl}')">
                 <img src="${fileUrl}" class="project-thumbnail">
               </a>`;
    } else if (ext === 'pdf') {
      html += `<a href="javascript:void(0)" onclick="openPDF('${fileUrl}')">
                 <img src="assets/static/file-pdf-solid.svg" class="project-thumbnail pdf-icon">
               </a>`;
    }
    html += `<h3><a href="${detail}">${p.title}</a></h3>`;
    html += `<p>${p.description.substring(0,100)}…</p>`;

    div.innerHTML = html;
    observer.observe(div);
    return div;
  }

  function render(projects, append = false) {
    if (!append) {
      container.innerHTML = '';
      // On remet le trigger d'infinite scroll si on reset
      setupScrollTrigger();
    }
    
    projects.forEach((p, idx) => {
      container.appendChild(createProjectCard(p, idx));
    });
  }

  function setupScrollTrigger() {
    let trigger = document.getElementById('infinite-scroll-trigger');
    if (!trigger) {
      trigger = document.createElement('div');
      trigger.id = 'infinite-scroll-trigger';
      trigger.style.height = '10px';
      trigger.style.width = '100%';
    }
    container.after(trigger);
    scrollObserver.observe(trigger);
  }

  async function loadMore() {
    if (loading || allLoaded) return;
    loading = true;

    try {
      const resp = await fetch(`/api/projects/getProjects.php?limit=${limit}&offset=${offset}&lang=${lang}`);
      const data = await resp.json();
      
      if (data.length < limit) {
        allLoaded = true;
      }

      render(data, true);
      offset += limit;
    } catch (err) {
      console.error('Erreur au chargement des publications :', err);
    } finally {
      loading = false;
    }
  }

  let timer;
  const performSearch = async () => {
    const q = searchInput.value.trim();
    const moment = momentFilter.value;
    const temps = tempsFilter.value;
    
    currentSearch = q;
    currentMoment = moment;
    currentTemps = temps;
    
    if (q === '' && moment === '' && temps === '') {
      offset = 0;
      allLoaded = false;
      render([]);
      loadMore();
    } else {
      allLoaded = true;
      try {
        let url = `/api/projects/search.php?lang=${lang}`;
        if (q) url += `&q=${encodeURIComponent(q)}`;
        if (moment) url += `&moment=${encodeURIComponent(moment)}`;
        if (temps) url += `&temps=${encodeURIComponent(temps)}`;
        
        const resp = await fetch(url);
        const data = await resp.json();
        render(data);
      } catch (err) {
        console.error('Erreur recherche :', err);
      }
    }
  };

  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(performSearch, 300);
  });

  momentFilter?.addEventListener('change', performSearch);
  tempsFilter?.addEventListener('change', performSearch);

  // Init
  render([]);
  loadMore();
});
