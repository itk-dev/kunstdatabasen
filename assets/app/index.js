const $ = require('jquery');
global.$ = global.jQuery = $;

import './app.scss';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';
library.add(
    faArrowLeft,
    faArrowRight
);
dom.watch();

import 'bootstrap/js/dist/collapse';

require('select2');

$(function () {
    $(document).ready(function () {
        $('.tag-select-edit').select2({
            tags: true
        });

        $('.tag-select').select2();
    });
});
