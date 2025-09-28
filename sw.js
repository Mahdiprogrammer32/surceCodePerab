const CACHE_NAME = 'kalahabama-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/sw.js',
    // '/index.php',
    '/home.php',
    '/login.php',
    '/dashboard.php',
    '/assets/css/style.css',
    '/assets/css/style_project.css',
    '/assets/css/checkout.css',
    '/assets/css/calculator.css',
    '/assets/js/calculator.js',
    '/fontA/css/all.min.css',
];

// نصب و کش منابع اولیه
self.addEventListener('install', event => {
    console.log('🔧 نصب سرویس‌ورکر...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(async cache => {
            for (const url of urlsToCache) {
                try {
                    const response = await fetch(url, { cache: 'no-cache' });
                    if (response.ok) {
                        await cache.put(url, response.clone());
                        console.log(`✅ کش شد: ${url}`);
                    } else {
                        console.warn(`❌ کش نشد (پاسخ نامعتبر): ${url}, status: ${response.status}`);
                    }
                } catch (err) {
                    console.warn(`⚠️ خطا در کش کردن ${url}:`, err);
                }
            }
        })
    );
});

// فعال‌سازی و حذف کش‌های قدیمی
self.addEventListener('activate', event => {
    console.log('✅ سرویس‌ورکر فعال شد!');
    event.waitUntil(
        caches.keys().then(names =>
            Promise.all(names.map(name => {
                if (name !== CACHE_NAME) {
                    console.log(`🗑️ حذف کش قدیمی: ${name}`);
                    return caches.delete(name);
                }
            }))
        ).then(() => {
            return self.clients.claim(); // فوراً کنترل کلاینت‌ها را بگیر
        })
    );
});

// مدیریت درخواست‌های شبکه
self.addEventListener('fetch', event => {
    if (event.request.url.startsWith(self.location.origin)) {
        event.respondWith(
            caches.match(event.request).then(response => {
                if (response) {
                    console.log(`📦 پاسخ از کش برای: ${event.request.url}`);
                    return response;
                }
                console.log(`🌐 درخواست از شبکه برای: ${event.request.url}`);
                return fetch(event.request).catch(err => {
                    console.warn(`📡 خطا در واکشی: ${event.request.url}`, err);
                });
            })
        );
    }
});

// آپدیت دستی کش با پیام
self.addEventListener('message', async event => {
    console.log('📩 پیام دریافت‌شده در سرویس‌ورکر:', event.data);
    if (event.data.action === 'update-data') {
        console.log('🔄 شروع آپدیت داده‌ها...');
        try {
            const cache = await caches.open(CACHE_NAME);
            console.log('📂 کش باز شد:', CACHE_NAME);
            const response = await fetch('/fetchAllData.php', { cache: 'no-cache' });
            console.log('🌐 پاسخ از fetchAllData.php:', response.status, response);
            if (response.ok) {
                await cache.put('/fetchAllData.php', response.clone());
                console.log('✅ داده‌ها با موفقیت آپدیت شدند.');
                event.source.postMessage({
                    status: 'success',
                    message: 'داده‌ها آپدیت شدند.'
                });
            } else {
                throw new Error(`پاسخ نامعتبر: ${response.status}`);
            }
        } catch (error) {
            console.error('❌ خطا در آپدیت داده‌ها:', error);
            event.source.postMessage({
                status: 'error',
                message: `آپدیت داده‌ها با خطا مواجه شد: ${error.message}`
            });
        }
    }
});