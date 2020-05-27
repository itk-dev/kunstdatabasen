// Add jQuery
const $ = require('jquery');
global.$ = global.jQuery = $;

import './item.scss';
import '../_imageUpload.js';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft } from '@fortawesome/free-solid-svg-icons';

library.add(
    faArrowLeft
);
dom.watch();

import '../../partials/initSelect2';
