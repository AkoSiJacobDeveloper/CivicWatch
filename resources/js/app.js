import '../css/app.css';
import './bootstrap';

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import 'flowbite';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css'


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
import { faFacebook, faTwitter, faTiktok, faInstagram } from '@fortawesome/free-brands-svg-icons';
import { faMoon, faBookOpenReader, faFlag, faSun, faLocation, faPhone, faEnvelope, faChevronRight, faPaperPlane, faInfoCircle, faUserShield, faArrowCircleLeft, faCircleExclamation, faLock, faCheck, faCopy, faChartSimple, faFile, faHourglass, faRightFromBracket, faSquareCaretLeft, faSquareCaretRight, faEllipsisVertical, faChevronCircleLeft, faChevronCircleRight, faCompass, faBullseye, faEye, faRoad, faLightbulb, faTrash, faDroplet, faChair, faTree, faDog, faPersonDigging, faVolumeHigh, faThumbTack, faCamera, faLocationDot, faGear, faComment, faChevronUp, faHome, faExclamationTriangle, faSearch, faQuestionCircle, faShieldAlt, faFileContract, faDatabase, faCookieBite } from '@fortawesome/free-solid-svg-icons';

library.add(faMoon, faBookOpenReader, faFlag, faSun, faFacebook, faTwitter, faTiktok, faInstagram, faLocation, faPhone, faEnvelope, faChevronRight, faPaperPlane, faInfoCircle, faUserShield, faArrowCircleLeft, faCircleExclamation, faLock, faCheck, faCopy, faChartSimple, faFile, faHourglass, faRightFromBracket, faSquareCaretLeft, faSquareCaretRight, faEllipsisVertical, faChevronCircleLeft, faChevronCircleRight, faCompass, faBullseye, faEye, faRoad, faLightbulb, faTrash, faDroplet, faChair, faTree, faDog, faPersonDigging, faVolumeHigh, faThumbTack, faCamera, faLocationDot, faGear, faComment, faChevronUp, faHome, faExclamationTriangle, faSearch, faQuestionCircle, faShieldAlt, faFileContract, faDatabase, faCookieBite );

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,  
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast)
            .use(VueSweetalert2)
            .component('font-awesome-icon', FontAwesomeIcon)
            .mount(el);
            
    },
    progress: {
        color: '#2C2C2C',
    },
});
