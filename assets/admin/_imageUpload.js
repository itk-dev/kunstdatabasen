/* global FileReader */

let collectionHolder;
const addButton = $(
    '<button type="button" class="btn btn-success add_link px-5">Tilføj billede</button>'
);
const addNewLink = $('<li></li>').append(addButton);

jQuery(document).ready(function () {
    collectionHolder = $('ul.images');
    collectionHolder.find('li').each(function () {
        addDeleteLink($(this));
    });
    collectionHolder.append(addNewLink);
    collectionHolder.data('index', collectionHolder.find('input').length);

    addButton.on('click', function (e) {
        addForm(collectionHolder, addNewLink);
    });

    const inputs = $('.custom-file-input');
    inputs.on('change', (event) => {
        inputOnChange(event.target);
    });

    const images = $('.custom-file-label').closest('.vich-image').find('img');
    images.each((index, el) => {
        const element = $(el);
        const path = element.prop('src').split('/');

        if (path.length > 0) {
            const filename = path[path.length - 1];
            element
                .closest('li.media')
                .find('.custom-file-label')
                .html(filename);
        }
    });
});

function inputOnChange (target) {
    const element = $(target);
    const filename = element.val().replace('C:\\fakepath\\', '').trim();
    element.parent().find('.custom-file-label').html(filename);
}

function addForm (collectionHolder, newLinkLi) {
    const prototype = collectionHolder.data('prototype');
    const index = collectionHolder.data('index');
    let newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    collectionHolder.data('index', index + 1);
    const newFormLi = $('<li></li>').append(newForm);
    newLinkLi.before(newFormLi);

    const inputs = $(newFormLi).find('.custom-file-input');
    inputs.on('change', (event) => {
        inputOnChange(event.target);
        filePreview(event.target);
    });

    addDeleteLink(newFormLi);
}

function filePreview (input) {
    const target = $(input);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            target
                .closest('.vich-image')
                .find('.js-image-preview')
                .html(
                    '<img src="' +
                        e.target.result +
                        '" class="img-fluid" alt="preview image"/>'
                );
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addDeleteLink (tagFormLi) {
    movePrimaryImage(tagFormLi);

    const removeFormButton = $(
        '<button type="button" class="btn btn-danger btn-sm">Fjern billede</button>'
    );
    tagFormLi.find('.image-form-details').append(removeFormButton);

    removeFormButton.on('click', function (e) {
        tagFormLi.remove();
    });
}

function movePrimaryImage (newFormLi) {
    const formGroup = newFormLi
        .find('.js-image-primary-image')
        .closest('.form-group')
        .detach();
    formGroup.addClass('mt-2');
    newFormLi.find('.image-form-details').append(formGroup);
}
