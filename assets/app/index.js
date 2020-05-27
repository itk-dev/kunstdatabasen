import './app.scss';

const $ = require('jquery');
global.$ = global.jQuery = $;

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';

import 'bootstrap/js/dist/collapse';

library.add(
    faArrowLeft,
    faArrowRight
);
dom.watch();

import './../partials/initSelect2';
