import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY || "AIzaSyAGGD1dva4EAfrKJtHyZRu7uD0arBQb9Rs",
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || "marketplace-54184.firebaseapp.com",
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || "marketplace-54184",
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || "marketplace-54184.firebasestorage.app",
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || "1039800099806",
    appId: import.meta.env.VITE_FIREBASE_APP_ID || "1:1039800099806:web:8823dccbd2157464c9b34f",
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || "G-1ZK0LRY506"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth };
