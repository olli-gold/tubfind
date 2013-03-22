$(document).ready(function(){
    registerBulkActions();
});

function registerBulkActions() {
    $('form[name="bulkActionForm"] input[type="submit"]').unbind('click').click(function(){
        var ids = $.map($(this.form).find('input.checkbox:checked'), function(i) {
            return $(i).val();
        });
        var action = $(this).attr('name');
        var message = $(this).attr('title');
        switch (action) {
        case 'export':
            action = 'Export';
            break;
        case 'delete':
            action = 'Delete';
            break;
        case 'email':
            action = 'Email';
            break;
        }
        getLightbox('MyResearch', action, '', '', message, '', '', '', {ids:ids});
        return false;
    });

    // Support delete list button:
    $('.deleteList').unbind('click').click(function(){
        var id = $(this).attr('id').substr('deleteList'.length);
        var message = $(this).attr('title');
        var postParams = {listID: id, deleteList: 'deleteList'};
        getLightbox('MyResearch', 'Confirm', '', '', message, 'MyResearch', 'Favorites', '', postParams);
        return false;
    });
}
