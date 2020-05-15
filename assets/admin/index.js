import './admin.scss';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faEdit, faPalette, faSearch, faMountain } from '@fortawesome/free-solid-svg-icons';

const $ = require('jquery');
global.$ = global.jQuery = $;

library.add(
    faEdit,
    faPalette,
    faSearch,
    faMountain
);
dom.watch();

require('select2');
require('select2/dist/js/i18n/da');

import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/collapse';

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
