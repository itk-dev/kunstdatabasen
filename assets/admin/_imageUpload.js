let collectionHolder;
const addButton = $('<button type="button" class="btn btn-primary btn-sm add_link">Add image</button>');
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
});

function addForm (collectionHolder, newLinkLi) {
    const prototype = collectionHolder.data('prototype');
    const index = collectionHolder.data('index');
    let newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    collectionHolder.data('index', index + 1);
    const newFormLi = $('<li></li>').append(newForm);
    newLinkLi.before(newFormLi);
    addDeleteLink(newFormLi);
}

function addDeleteLink (tagFormLi) {
    const removeFormButton = $('<button type="button" class="btn btn-danger btn-sm">Remove image</button>');
    tagFormLi.append(removeFormButton);

    removeFormButton.on('click', function (e) {
        tagFormLi.remove();
    });
}
