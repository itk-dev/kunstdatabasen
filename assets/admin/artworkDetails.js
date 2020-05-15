$(function () {
    $(document).ready(function () {
        const modalTitle = $('#artwork-details-modal-title');
        const modalBody = $('#artwork-details-modal-body');
        const modalEditLink = $('#artwork-details-modal-edit-link');
        const modal = $('#artwork-details-modal');

        $('.artwork-details-link').on('click', function (event) {
            event.preventDefault();

            const id = $(this).data('id');

            // Clear and show modal.
            modalTitle.html('');
            modalBody.html('');
            modalEditLink.prop('href', '#');
            modal.modal('show');

            $.ajax({
                url: '/admin/artwork/' + id + '/modal',
                type: 'GET',
                data: {
                    id: id
                },
                success: function (modalResponse) {
                    modalTitle.html(modalResponse.title);
                    modalBody.html(modalResponse.modalBody);
                    modalEditLink.prop('href', modalResponse.editLink);
                }
            });
        });
    });
});
