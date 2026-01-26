import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
    apiKey: "AIzaSyBL0j2ib-fECxSNml9BkDd85PWfaGNZo0c",
    authDomain: "sikerja-7b790.firebaseapp.com",
    projectId: "sikerja-7b790",
    storageBucket: "sikerja-7b790.firebasestorage.app",
    messagingSenderId: "721762289152",
    appId: "1:721762289152:web:4fc7418d9b9300a4717ae5",
    measurementId: "G-E3S9L0XNL1"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth };
