$(document).ready(function(){
    registerBulkActions();
});

function registerBulkActions() {
    $('form[name="bulkActionForm"] input[type="submit"]').unbind('click').click(function(){
        var ids = $.map($(this.form).find('input.checkbox_ui:checked'), function(i) {
            return $(i).val();
        });
        var action = $(this).attr('name');
        var message = $(this).attr('title');
        var id = '';
        var module = "Cart";
        switch (action) {
        case 'export':
            var postParams = {origin: 'Favorites', ids:ids, 'export':'1'};
            action = "Home";
            break;
        case 'delete':
            module = "MyResearch";
            action = "Delete";
            var postParams = {origin: 'Favorites', ids:ids, 'delete':'1'};
            id = $(this).attr('id');
            id = (id.indexOf('bottom_delete_list_items_') != -1) 
                ? id.replace('bottom_delete_list_items_', '')
                : id.replace('delete_list_items_', '');
            break;
        case 'email':
            action = "Home";
            var postParams = {origin: 'Favorites', ids:ids, email:'1'};
            break;
        case 'print': 
            var printing = printIDs(ids);
            if(printing) {
                return false;
            } else {
                action = "Home";
                var postParams = {origin: 'Favorites', error:'1'};
            }
            break;
        }
        getLightbox(module, action, id, '', message, '', '', '', postParams);
        return false;
    });

    // Support delete list button:
    $('.deleteList').unbind('click').click(function(){
        var id = $(this).attr('id').substr('deleteList'.length);
        var message = $(this).attr('title');
        var postParams = {origin: 'Favorites', listID: id, deleteList: 'deleteList'};
        getLightbox('Cart', 'Home', '', '', message, 'MyResearch', 'Favorites', '', postParams);
        return false;
    });
}
