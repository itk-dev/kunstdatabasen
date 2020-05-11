import './app.scss';

// Need jQuery? Install it with "yarn add jquery", then add the line `import $ from 'jquery';` to this file.

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';

library.add(
    faArrowLeft,
    faArrowRight
);
dom.watch();

import 'bootstrap/js/dist/collapse';
