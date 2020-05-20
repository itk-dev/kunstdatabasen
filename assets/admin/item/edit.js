import './item.scss';
import '../_imageUpload.js';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft } from '@fortawesome/free-solid-svg-icons';

library.add(
    faArrowLeft
);
dom.watch();

// Add jQuery
const $ = require('jquery');
global.$ = global.jQuery = $;

// Add select 2
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
