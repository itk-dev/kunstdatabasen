// Add bootstrap components
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/dropdown';

// Add admin styles
import '../admin.scss';
import '../../partials/imageCarousel';
import '../_itemDetails.js';
import '../../partials/initSelect2';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import {
    faEdit,
    faPalette,
    faSearch,
    faMountain,
    faBars,
    faPlusCircle,
    faChair,
    faTimes,
    faTimesCircle,
    faArrowLeft,
    faArrowRight,
    faExpand,
    faArrowAltCircleLeft,
    faArrowAltCircleRight,
    faFileExport
} from '@fortawesome/free-solid-svg-icons';

library.add(
    faEdit,
    faPalette,
    faSearch,
    faMountain,
    faBars,
    faPlusCircle,
    faChair,
    faTimes,
    faTimesCircle,
    faArrowLeft,
    faArrowRight,
    faExpand,
    faArrowAltCircleLeft,
    faArrowAltCircleRight,
    faFileExport
);
dom.watch();
