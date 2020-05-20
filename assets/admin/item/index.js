// Add admin styles
import '../admin.scss';
import '../_itemDetails.js';

// Add font awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faEdit, faPalette, faSearch, faMountain, faBars, faPlusCircle, faChair } from '@fortawesome/free-solid-svg-icons';

library.add(
    faEdit,
    faPalette,
    faSearch,
    faMountain,
    faBars,
    faPlusCircle,
    faChair
);
dom.watch();

// Add bootstrap components
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/dropdown';
