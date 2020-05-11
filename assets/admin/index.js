import './admin.scss';

// Need jQuery? Install it with "yarn add jquery", then add the line `import $ from 'jquery';` to this file.

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faEdit, faPalette, faSearch, faMountain } from '@fortawesome/free-solid-svg-icons'

library.add(
  faEdit,
  faPalette,
  faSearch,
  faMountain
)
dom.watch()

const $ = require('jquery');
global.$ = global.jQuery = $;
