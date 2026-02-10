import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY || "AIzaSyBL0j2ib-fECxSNml9BkDd85PWfaGNZo0c",
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || "sikerja-7b790.firebaseapp.com",
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || "sikerja-7b790",
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || "sikerja-7b790.firebasestorage.app",
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || "721762289152",
    appId: import.meta.env.VITE_FIREBASE_APP_ID || "1:721762289152:web:4fc7418d9b9300a4717ae5",
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || "G-E3S9L0XNL1"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth };
