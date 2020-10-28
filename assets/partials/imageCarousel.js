import 'bootstrap/js/dist/carousel';

$(function () {
    function register () {
        $('#carouselImageIndicators').on('slide.bs.carousel', event => {
            const next = $(event.relatedTarget);
            const to = next.index();
            $('#carouselImageActiveIndex').text(to + 1);
        });

        $('.js-image-full').on('click', function () {
            $('#carouselImageFull').attr('src', $(this).data('src'));
            $('#carouselImageModal').modal();
        });
    }

    $(document).ready(register);

    window.addEventListener('registerImageCarousel', () => {
        register();
    }, false);
});
