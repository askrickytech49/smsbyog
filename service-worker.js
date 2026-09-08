self.addEventListener('install', event => {
  event.waitUntil(
    caches.open('otpbuy-cache-v1').then(cache => {
      return cache.addAll([
        '/',
        '/index.html',
        '/styles.css',
        '/scripts.js',
        '/images/icons-192.png',
        '/images/icons-512.png'
      ]);
    })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});
