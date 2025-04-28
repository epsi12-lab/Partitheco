// assets/js/getProjects.js

document.addEventListener('DOMContentLoaded', () => {
    const container   = document.getElementById('projectsContainer');
    const loadBtn     = document.getElementById('loadMoreBtn');
    const searchInput = document.getElementById('searchInput');
    const lang        = new URLSearchParams(window.location.search).get('lang') || 'fr';
  
    let offset = container.children.length; 
    const pageSize = 5;
  
    function render(projects, clear = false) {
      if (clear) container.innerHTML = '';
      projects.forEach(p => {
        const ext    = p.thumbnail ? p.thumbnail.split('.').pop().toLowerCase() : '';
        const fileUrl= `assets/img/${p.thumbnail}`;
        const detail = `project.php?id=${p.id}&lang=${lang}`;
  
        const div = document.createElement('div');
        div.className = 'project-item';
  
        let html = '';
        if (['jpg','jpeg','png','gif'].includes(ext)) {
          html += `<a href="${detail}">
                     <img src="${fileUrl}" class="project-thumbnail">
                   </a>`;
        } else if (ext === 'pdf') {
          html += `<a href="${fileUrl}" target="_blank">
                     <img src="assets/img/pdf-icon.png" class="project-thumbnail pdf-icon">
                   </a>`;
        }
  
        html += `<h3><a href="${detail}">${p.title}</a></h3>`;
        html += `<p>${p.description.substring(0,100)}…</p>`;
  
        div.innerHTML = html;
        container.appendChild(div);
      });
    }
  
    loadBtn.addEventListener('click', async () => {
      const resp = await fetch(`api/projects/getProjects.php?limit=1000&offset=0&lang=${lang}`);
      const data = await resp.json();
      render(data, true);
      loadBtn.style.display = 'none';
    });
  
    let timer;
    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(async () => {
        const q = searchInput.value.trim();
        if (!q) {
          const resp = await fetch(`api/projects/getProjects.php?limit=${pageSize}&offset=0&lang=${lang}`);
          const data = await resp.json();
          render(data, true);
          loadBtn.style.display = 'block';
          offset = data.length;
        } else {
          const resp = await fetch(`api/projects/search.php?q=${encodeURIComponent(q)}&lang=${lang}`);
          const data = await resp.json();
          render(data, true);
          loadBtn.style.display = 'none';
        }
      }, 300);
    });
  });
  