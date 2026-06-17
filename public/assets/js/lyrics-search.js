// assets/js/lyrics-search.js
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('lyricsSearchInput');
  const button = document.getElementById('lyricsSearchBtn');
  const results = document.getElementById('lyricsSearchResults');
  const lang = new URLSearchParams(location.search).get('lang') || 'fr';

  if (!input || !button || !results) {
    return;
  }

  let timer;

  async function runSearch() {
    const query = input.value.trim();
    if (query.length < 3) {
      results.innerHTML = '<p class="lyrics-search-hint">Saisissez au moins 3 caractères.</p>';
      return;
    }

    try {
      const resp = await fetch(`/api/search-lyrics.php?q=${encodeURIComponent(query)}`);
      const data = await resp.json();

      if (!data.results || data.results.length === 0) {
        results.innerHTML = '<p class="lyrics-search-hint">Aucun résultat trouvé dans les paroles.</p>';
        return;
      }

      results.innerHTML = data.results.map(r => `
        <a class="lyrics-search-result" href="project.php?id=${r.id}&lang=${lang}">
          <strong>${escapeHtml(r.title)}</strong>
          ${r.author ? `<span class="lyrics-search-author"> — ${escapeHtml(r.author)}</span>` : ''}
          <p class="lyrics-search-excerpt">${r.excerpt}</p>
        </a>
      `).join('');
    } catch (err) {
      console.error('Erreur recherche par paroles :', err);
      results.innerHTML = '<p class="lyrics-search-hint">Erreur lors de la recherche.</p>';
    }
  }

  button.addEventListener('click', runSearch);
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(runSearch, 400);
  });
});
