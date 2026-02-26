// offline-cache.js - Gestion du cache hors-ligne des partitions

class OfflineCacheManager {
  constructor() {
    this.cachedUrls = new Set();
    this.init();
  }

  init() {
    if (!('serviceWorker' in navigator)) {
      console.warn('Service Worker non supporté');
      return;
    }

    // Écouter les messages du SW
    navigator.serviceWorker.addEventListener('message', (event) => {
      this.handleSWMessage(event.data);
    });

    // Charger la liste des partitions en cache
    this.getCachedPartitions();
  }

  handleSWMessage(data) {
    switch (data.type) {
      case 'PARTITION_CACHED':
        if (data.success) {
          this.cachedUrls.add(data.url);
          this.updateUI(data.url, true);
          window.showToast?.('Partition sauvegardée hors-ligne', 'success');
        } else {
          window.showToast?.('Erreur de sauvegarde', 'error');
        }
        break;

      case 'PARTITION_REMOVED':
        if (data.success) {
          this.cachedUrls.delete(data.url);
          this.updateUI(data.url, false);
          window.showToast?.('Partition retirée du cache', 'info');
        }
        break;

      case 'CACHED_PARTITIONS_LIST':
        this.cachedUrls = new Set(data.urls);
        this.updateAllUI();
        break;

      case 'MEDIA_CACHE_CLEARED':
        this.cachedUrls.clear();
        this.updateAllUI();
        window.showToast?.('Cache média vidé', 'info');
        break;
    }
  }

  async getCachedPartitions() {
    const sw = await navigator.serviceWorker.ready;
    sw.active.postMessage({ type: 'GET_CACHED_PARTITIONS' });
  }

  async cachePartition(url) {
    const sw = await navigator.serviceWorker.ready;
    sw.active.postMessage({ type: 'CACHE_PARTITION', url: url });
  }

  async removeFromCache(url) {
    const sw = await navigator.serviceWorker.ready;
    sw.active.postMessage({ type: 'REMOVE_CACHED_PARTITION', url: url });
  }

  async clearMediaCache() {
    const sw = await navigator.serviceWorker.ready;
    sw.active.postMessage({ type: 'CLEAR_MEDIA_CACHE' });
  }

  isCached(url) {
    return this.cachedUrls.has(url);
  }

  updateUI(url, isCached) {
    const buttons = document.querySelectorAll(`[data-cache-url="${url}"]`);
    buttons.forEach(btn => {
      btn.classList.toggle('cached', isCached);
      btn.title = isCached ? 'Disponible hors-ligne' : 'Sauvegarder hors-ligne';
      btn.innerHTML = isCached ? '📥 Hors-ligne ✓' : '📥 Sauvegarder';
    });
  }

  updateAllUI() {
    document.querySelectorAll('[data-cache-url]').forEach(btn => {
      const url = btn.dataset.cacheUrl;
      const isCached = this.isCached(url);
      btn.classList.toggle('cached', isCached);
      btn.title = isCached ? 'Disponible hors-ligne' : 'Sauvegarder hors-ligne';
      btn.innerHTML = isCached ? '📥 Hors-ligne ✓' : '📥 Sauvegarder';
    });
  }

  // Créer un bouton de cache pour une partition
  createCacheButton(url) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-cache-offline';
    btn.dataset.cacheUrl = url;
    btn.title = this.isCached(url) ? 'Disponible hors-ligne' : 'Sauvegarder hors-ligne';
    btn.innerHTML = this.isCached(url) ? '📥 Hors-ligne ✓' : '📥 Sauvegarder';
    
    if (this.isCached(url)) {
      btn.classList.add('cached');
    }

    btn.addEventListener('click', () => {
      if (this.isCached(url)) {
        this.removeFromCache(url);
      } else {
        this.cachePartition(url);
      }
    });

    return btn;
  }
}

// Instance globale
window.offlineCache = new OfflineCacheManager();

// Auto-ajouter les boutons de cache aux partitions
document.addEventListener('DOMContentLoaded', () => {
  // Ajouter boutons aux liens de téléchargement PDF/audio
  setTimeout(() => {
    document.querySelectorAll('a[href*="/assets/img/"][download]').forEach(link => {
      const url = new URL(link.href, window.location.origin).href;
      if (!link.parentElement.querySelector('[data-cache-url]')) {
        const btn = window.offlineCache.createCacheButton(url);
        link.parentElement.appendChild(btn);
      }
    });

    // Ajouter aux lecteurs audio
    document.querySelectorAll('.audio-player-enhanced audio source').forEach(source => {
      const url = new URL(source.src, window.location.origin).href;
      const player = source.closest('.audio-player-enhanced');
      if (player && !player.querySelector('[data-cache-url]')) {
        const btn = window.offlineCache.createCacheButton(url);
        btn.style.marginTop = '0.5rem';
        player.appendChild(btn);
      }
    });
  }, 500);
});
