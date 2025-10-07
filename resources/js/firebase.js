import { initializeApp } from "firebase/app";
import { getFirestore } from "firebase/firestore";

const firebaseConfig = {
    apiKey: "AIzaSyCKDXpyPu-f-Ty9Da4DV-xpeNKuyUTDRZU",
    authDomain: "civicwatch-7ca5e.firebaseapp.com",
    projectId: "civicwatch-7ca5e",
    storageBucket: "civicwatch-7ca5e.firebasestorage.app",
    messagingSenderId: "476743252876",
    appId: "1:476743252876:web:7661ef44e6a403d25374a9"
};

const app = initializeApp(firebaseConfig);

const db = getFirestore(app);

export { db };