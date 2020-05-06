let collectionHolder;
let addButton = $('<button type="button" class="add_link">Add image</button>');
let addNewLink = $('<li></li>').append(addButton);

jQuery(document).ready(function () {
    collectionHolder = $('ul.images');
    collectionHolder.find('li').each(function() {
        addDeleteLink($(this));
    });
    collectionHolder.append(addNewLink);
    collectionHolder.data('index', collectionHolder.find('input').length);

    addButton.on('click', function (e) {
        addForm(collectionHolder, addNewLink);
    });
});

function addForm(collectionHolder, newLinkLi) {
    let prototype = collectionHolder.data('prototype');
    let index = collectionHolder.data('index');
    let newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    collectionHolder.data('index', index + 1);
    let newFormLi = $('<li></li>').append(newForm);
    newLinkLi.before(newFormLi);
    addDeleteLink(newFormLi);
}

function addDeleteLink(tagFormLi) {
    let removeFormButton = $('<button type="button">Remove image</button>');
    tagFormLi.append(removeFormButton);

    removeFormButton.on('click', function(e) {
        tagFormLi.remove();
    });
}
