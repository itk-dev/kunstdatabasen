const $ = require('jquery');
global.$ = global.jQuery = $;

import './admin.scss';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faEdit, faPalette, faSearch, faMountain } from '@fortawesome/free-solid-svg-icons'

library.add(
  faEdit,
  faPalette,
  faSearch,
  faMountain
);
dom.watch();

require('select2');

$(function () {
    $(document).ready(function () {
        $('.tag-select-edit').select2({
            tags: true
        });

        $('.tag-select').select2();
    });
});
