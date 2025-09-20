// firebase-messaging-sw.js
importScripts("https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging.js");

const firebaseConfig = {
  apiKey: "AIzaSyAHChNxtWWB4v28t8UQglk3OxCl13LBr-E",
  authDomain: "deliveryapp-8b3ec.firebaseapp.com",
  projectId: "deliveryapp-8b3ec",
  storageBucket: "deliveryapp-8b3ec.firebasestorage.app",
  messagingSenderId: "401322501798",
  appId: "1:401322501798:web:443c04a655b131b0417667",
  measurementId: "G-HJ91GC32GE"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
  self.registration.showNotification(payload.notification.title, {
    body: payload.notification.body,
    icon: "/order-icon.png"
  });
});
