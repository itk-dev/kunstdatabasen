import './initSelect2.scss';

require('select2');
require('select2/dist/js/i18n/da');

$(function () {
    $(document).ready(function () {
        $('.tag-select-edit').select2({
            tags: true,
            allowClear: true,
            placeholder: 'Skriv',
            language: 'da_DK'
        });

        $(document).ready(function () {
            $('.tag-select').select2({
                language: 'da_DK',
                allowClear: true,
                placeholder: ''
            });
        });
    });
});
