import './app.scss';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';

import 'bootstrap/js/dist/collapse';

import './../partials/initSelect2';

const $ = require('jquery');
global.$ = global.jQuery = $;

library.add(
    faArrowLeft,
    faArrowRight
);
dom.watch();
