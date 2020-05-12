import './app.scss';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';

import 'bootstrap/js/dist/collapse';

const $ = require('jquery');
global.$ = global.jQuery = $;
library.add(
    faArrowLeft,
    faArrowRight
);
dom.watch();

require('select2');
require('select2/dist/js/i18n/da');

$(function () {
    $(document).ready(function () {
        $('.tag-select-edit').select2({
            tags: true,
            allowClear: true,
            placeholder: 'Skriv',
            language: 'da_DK'
        });

        $('.tag-select').select2({
            language: 'da_DK'
        });
    });
});
