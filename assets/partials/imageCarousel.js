$(function () {
    $(document).ready(function () {
        $('#carouselImageIndicators').on('slide.bs.carousel', function (e) {
            $('#carouselImageActiveIndex').text(e.to + 1);
        });

        $('.js-image-full').on('click', function () {
            $('#carouselImageFull').attr('src', $(this).data('src'));
            $('#carouselImageModal').modal();
        });
    });
});
