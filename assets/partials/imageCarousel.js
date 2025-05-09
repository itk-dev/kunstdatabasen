import "bootstrap/js/dist/carousel";

import { Modal } from "bootstrap";

$(function () {
    function register() {
        $("#carouselImageIndicators").on("slide.bs.carousel", (event) => {
            const next = $(event.relatedTarget);
            const to = next.index();
            $("#carouselImageActiveIndex").text(to + 1);
        });

        $(".js-image-full").on("click", function () {
            $("#carouselImageFull").attr("src", $(this).data("src"));
            new Modal("#carouselImageModal").show();
        });
    }

    $(document).ready(register);

    window.addEventListener(
        "registerImageCarousel",
        () => {
            register();
        },
        false,
    );
});
