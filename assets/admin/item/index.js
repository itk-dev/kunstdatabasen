// Add admin styles
import '../admin.scss';
import '../_itemDetails.js';
import '../../partials/imageCarousel';

// Add bootstrap components
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/dropdown';
import 'bootstrap/js/dist/carousel';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faEdit, faPalette, faSearch, faMountain, faBars, faPlusCircle, faChair, faTimes, faTimesCircle, faArrowLeft, faArrowRight, faExpand, faArrowAltCircleLeft, faArrowAltCircleRight } from '@fortawesome/free-solid-svg-icons';

import '../../partials/initSelect2';

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
    faArrowAltCircleRight
);
dom.watch();
