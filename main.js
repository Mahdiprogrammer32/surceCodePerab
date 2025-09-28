if ('Notification' in window && 'serviceWorker' in navigator) {
    // درخواست مجوز برای نمایش نوتیفیکیشن‌ها
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            console.log('کاربر مجوز دریافت نوتیفیکیشن را داد.');
        } else {
            console.log('کاربر مجوز دریافت نوتیفیکیشن را رد کرد.');
        }
    });

    // ثبت سرویس ورکر
    navigator.serviceWorker.register('/sw.js').then(registration => {
        console.log('Service Worker ثبت شد:', registration);
    }).catch(error => {
        console.error('خطا در ثبت سرویس ورکر:', error);
    });
}


if ('serviceWorker' in navigator && 'SyncManager' in window) {
    navigator.serviceWorker.ready.then((registration) => {
      document.getElementById('sync-button').addEventListener('click', () => {
        registration.sync
          .register('sync-data')
          .then(() => {
            console.log('Sync Event با موفقیت ثبت شد.');
          })
          .catch((error) => {
            console.log('ثبت Sync Event با خطا مواجه شد:', error);
          });
      });
    });
  } else {
    console.log('Background Sync API پشتیبانی نمی‌شود.');
  }