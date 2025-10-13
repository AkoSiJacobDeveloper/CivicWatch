import '../css/app.css';
import './bootstrap';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import 'flowbite';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// import PrimeVue from 'primevue/config'
// import MultiSelect from 'primevue/multiselect'
// import 'primeicons/primeicons.css'
// import '@primevue/themes/aura/theme.css'
// import Aura from '@primevue/themes/aura'

import { faFacebook, faTwitter, faTiktok, faInstagram } from '@fortawesome/free-brands-svg-icons';
import { faMoon, faBookOpenReader, faFlag, faSun, faLocation, faPhone, faEnvelope, faChevronRight, faPaperPlane, faInfoCircle, faUserShield, faArrowCircleLeft, faCircleExclamation, faLock, faCheck, faCopy, faChartSimple, faFile, faHourglass, faRightFromBracket, faSquareCaretLeft, faSquareCaretRight, faEllipsisVertical, faChevronCircleLeft, faChevronCircleRight, faCompass, faBullseye, faEye, faRoad, faLightbulb, faTrash, faDroplet, faChair, faTree, faDog, faPersonDigging, faVolumeHigh, faThumbTack, faCamera, faLocationDot, faGear, faComment, faChevronUp, faHome, faExclamationTriangle, faSearch, faQuestionCircle, faShieldAlt, faFileContract, faDatabase, faCookieBite } from '@fortawesome/free-solid-svg-icons';

library.add(
    faMoon, faBookOpenReader, faFlag, faSun, faFacebook, faTwitter, faTiktok, faInstagram,
    faLocation, faPhone, faEnvelope, faChevronRight, faPaperPlane, faInfoCircle, faUserShield,
    faArrowCircleLeft, faCircleExclamation, faLock, faCheck, faCopy, faChartSimple, faFile,
    faHourglass, faRightFromBracket, faSquareCaretLeft, faSquareCaretRight, faEllipsisVertical,
    faChevronCircleLeft, faChevronCircleRight, faCompass, faBullseye, faEye, faRoad, faLightbulb,
    faTrash, faDroplet, faChair, faTree, faDog, faPersonDigging, faVolumeHigh, faThumbTack, faCamera,
    faLocationDot, faGear, faComment, faChevronUp, faHome, faExclamationTriangle, faSearch,
    faQuestionCircle, faShieldAlt, faFileContract, faDatabase, faCookieBite
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
        .use(plugin)
        .use(ZiggyVue)
        .use(Toast)
        .use(VueSweetalert2)
        // .use(PrimeVue, { theme: { preset: Aura } }) // ✅ Only this one
        // .component('MultiSelect', MultiSelect)
        .component('font-awesome-icon', FontAwesomeIcon)
        .mount(el);
    },
    progress: {
        color: '#2C2C2C',
    },
});
