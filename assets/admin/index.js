// Add admin styles
import './admin.scss';
import './_itemDetails.js';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faEdit, faPalette, faSearch, faMountain, faBars, faTimes, faChair } from '@fortawesome/free-solid-svg-icons';

import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/collapse';

// Add jQuery
const $ = require('jquery');
global.$ = global.jQuery = $;

library.add(
    faEdit,
    faPalette,
    faSearch,
    faMountain,
    faBars,
    faTimes,
    faChair
);
dom.watch();

// Add bootstrap components
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/dropdown';



