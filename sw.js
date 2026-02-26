// Service Worker - Partithéco PWA (Mode hors-ligne amélioré)
const CACHE_NAME = 'partitheco-v2';
const MEDIA_CACHE_NAME = 'partitheco-media-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
  '/',
  '/index.php',
  '/publications.php',
  '/calendrier.php',
  '/livrets.php',
  '/offline.html',
  '/assets/css/base.css',
  '/assets/css/navbar.css',
  '/assets/css/buttons.css',
  '/assets/css/liturgique.css',
  '/assets/css/homepage.css',
  '/assets/css/publications.css',
  '/assets/css/responsive.css',
  '/assets/css/audio-player.css',
  '/assets/css/footer.css',
  '/assets/css/forms.css',
  '/assets/js/base.js',
  '/assets/js/dynamic-features.js',
  '/assets/js/audio-player.js',
  '/assets/static/file-pdf-solid.svg',
  '/assets/static/partitheco.gif',
  '/manifest.json'
];

// Extensions de fichiers média à mettre en cache
const MEDIA_EXTENSIONS = ['.pdf', '.mp3', '.wav', '.ogg', '.mp4', '.webm'];

// Installation - mise en cache des ressources statiques
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Cache statique ouvert');
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activation - nettoyage des anciens caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== MEDIA_CACHE_NAME) {
            console.log('Suppression ancien cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Vérifier si l'URL est un fichier média
function isMediaFile(url) {
  return MEDIA_EXTENSIONS.some(ext => url.toLowerCase().includes(ext));
}

// Vérifier si l'URL est dans assets/img (partitions)
function isPartitionFile(url) {
  return url.includes('/assets/img/') && isMediaFile(url);
}

// Fetch - stratégie adaptée selon le type de ressource
self.addEventListener('fetch', (event) => {
  // Ignorer les requêtes non-GET
  if (event.request.method !== 'GET') return;
  
  // Ignorer les requêtes API (toujours réseau)
  if (event.request.url.includes('/api/')) return;

  const url = event.request.url;

  // Stratégie pour les fichiers média (PDF, audio, vidéo)
  if (isPartitionFile(url)) {
    event.respondWith(
      caches.open(MEDIA_CACHE_NAME).then((cache) => {
        return cache.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            // Retourner le cache et mettre à jour en arrière-plan
            fetch(event.request).then((networkResponse) => {
              if (networkResponse.status === 200) {
                cache.put(event.request, networkResponse.clone());
              }
            }).catch(() => {});
            return cachedResponse;
          }
          
          // Pas en cache, récupérer du réseau
          return fetch(event.request).then((networkResponse) => {
            if (networkResponse.status === 200) {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          }).catch(() => {
            // Fichier non disponible hors-ligne
            return new Response('Fichier non disponible hors-ligne', {
              status: 503,
              statusText: 'Service Unavailable'
            });
          });
        });
      })
    );
    return;
  }

  // Stratégie Network First pour les autres ressources
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Cloner la réponse pour la mettre en cache
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Fallback vers le cache
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // Page hors-ligne pour les navigations
          if (event.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
        });
      })
  );
});

// Message handler pour le cache manuel des partitions
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'CACHE_PARTITION') {
    const url = event.data.url;
    caches.open(MEDIA_CACHE_NAME).then((cache) => {
      fetch(url).then((response) => {
        if (response.status === 200) {
          cache.put(url, response);
          // Notifier le client
          event.source.postMessage({
            type: 'PARTITION_CACHED',
            url: url,
            success: true
          });
        }
      }).catch((error) => {
        event.source.postMessage({
          type: 'PARTITION_CACHED',
          url: url,
          success: false,
          error: error.message
        });
      });
    });
  }
  
  if (event.data && event.data.type === 'REMOVE_CACHED_PARTITION') {
    const url = event.data.url;
    caches.open(MEDIA_CACHE_NAME).then((cache) => {
      cache.delete(url).then((success) => {
        event.source.postMessage({
          type: 'PARTITION_REMOVED',
          url: url,
          success: success
        });
      });
    });
  }
  
  if (event.data && event.data.type === 'GET_CACHED_PARTITIONS') {
    caches.open(MEDIA_CACHE_NAME).then((cache) => {
      cache.keys().then((requests) => {
        const urls = requests.map(req => req.url);
        event.source.postMessage({
          type: 'CACHED_PARTITIONS_LIST',
          urls: urls
        });
      });
    });
  }
  
  if (event.data && event.data.type === 'CLEAR_MEDIA_CACHE') {
    caches.delete(MEDIA_CACHE_NAME).then(() => {
      event.source.postMessage({
        type: 'MEDIA_CACHE_CLEARED',
        success: true
      });
    });
  }
});

// Sync en arrière-plan
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-favorites') {
    console.log('Synchronisation des favoris...');
  }
});

// Notifications push
self.addEventListener('push', (event) => {
  if (event.data) {
    const data = event.data.json();
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/assets/img/icon-192.png',
      badge: '/assets/img/icon-192.png'
    });
  }
});
