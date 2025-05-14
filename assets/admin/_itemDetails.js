/* global Event */

$(function () {
    $(document).ready(function () {
        const modalTitle = $("#item-details-modal-title");
        const modalBody = $("#item-details-modal-body");
        const modalEditLink = $("#item-details-modal-edit-link");
        const modal = $("#item-details-modal");

        $(".item-details-link").on("click", function (event) {
            event.preventDefault();

            const id = $(this).data("id");

            // Clear and show modal.
            modalTitle.html("");
            modalBody.html("");
            modalEditLink.prop("href", "#");
            modal.modal("show");

            $.ajax({
                url: "/admin/item/" + id + "/modal",
                type: "GET",
                data: {
                    id,
                },
                success: function (modalResponse) {
                    modalTitle.html(modalResponse.title);
                    modalBody.html(modalResponse.modalBody);
                    modalEditLink.prop("href", modalResponse.editLink);

                    const event = new Event("registerImageCarousel");
                    window.dispatchEvent(event);
                },
            });
        });
    });
});
