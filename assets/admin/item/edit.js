// Add jQuery
import "./item.scss";
import "../_imageUpload.js";

// Add font awesome icons
import { library, dom } from "@fortawesome/fontawesome-svg-core";
import { faArrowLeft } from "@fortawesome/free-solid-svg-icons";

import "../../partials/initSelect2";

const $ = require("jquery");
global.$ = global.jQuery = $;

library.add(faArrowLeft);
dom.watch();
