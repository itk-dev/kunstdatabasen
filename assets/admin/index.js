// Add admin styles
import "./admin.scss";
import "./item.scss";

// Add bootstrap components
import { Modal, Collapse, Dropdown } from "bootstrap";

import "./_itemDetails.js";
import "./_imageUpload.js";
import "./../partials/initSelect2";
import "./../partials/imageCarousel";

// Add jQuery
const $ = require("jquery");
global.$ = global.jQuery = $;

// Add font awesome icons
import { library, dom } from "@fortawesome/fontawesome-svg-core";
import {
    faArrowAltCircleLeft,
    faArrowAltCircleRight,
    faArrowLeft,
    faArrowRight,
    faBars,
    faChair,
    faEdit,
    faExpand,
    faFileExport,
    faMountain,
    faPalette,
    faPlusCircle,
    faSearch,
    faTimes,
    faTimesCircle,
} from "@fortawesome/free-solid-svg-icons";

library.add(
    faArrowAltCircleLeft,
    faArrowAltCircleRight,
    faArrowLeft,
    faArrowRight,
    faBars,
    faChair,
    faEdit,
    faExpand,
    faFileExport,
    faMountain,
    faPalette,
    faPlusCircle,
    faSearch,
    faTimes,
    faTimesCircle,
);
dom.watch();
