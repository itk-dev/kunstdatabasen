import './app.scss';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft, faArrowRight, faExpand, faArrowAltCircleLeft, faArrowAltCircleRight, faCircle, faTimes } from '@fortawesome/free-solid-svg-icons';

import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/carousel';
import 'bootstrap/js/dist/modal';

import './../partials/initSelect2';
import './../partials/imageCarousel';

const $ = require('jquery');
global.$ = global.jQuery = $;

library.add(
    faArrowLeft,
    faArrowRight,
    faExpand,
    faArrowAltCircleLeft,
    faArrowAltCircleRight,
    faCircle,
    faTimes
);
dom.watch();
