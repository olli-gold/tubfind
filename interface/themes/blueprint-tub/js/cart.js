var _CART_COOKIE = 'vufind_cart';
var _CART_COOKIE_DELIM = "\t";

$(document).ready(function() {
    $('.addToCartCheckbox').change(function(){
        var id = $(this).val();
        if ($(this).attr('checked')) {
            updateCartSummary(addItemToCartCookie(id));
        } else {
            updateCartSummary(removeItemFromCartCookie(id));
        }
    });
    
    var items = getItemsFromCartCookie();
    updateCheckboxStates(items);
    updateCartSummary(items);
});

function getItemsFromCartCookie() {
    var cookie = $.cookie(_CART_COOKIE);
    if (cookie) {
        var cart = cookie.split(_CART_COOKIE_DELIM);
        return cart ? cart : Array();
    } 
    return Array();
}

function addItemToCartCookie(item) {
    var items = getItemsFromCartCookie();
    items.push(item);
    items = uniqueValues(items);
    $.cookie(_CART_COOKIE, items.join(_CART_COOKIE_DELIM), { path: '/' });
    return items;
}

function removeItemFromCartCookie(item) {
    var items = getItemsFromCartCookie();
    var index = items.indexOf(item);
    if (index != -1) {
        items.splice(index, 1);
    }
    $.cookie(_CART_COOKIE, items.join(_CART_COOKIE_DELIM), { path: '/' });
    return items;
}

// we won't need these functions when the check states 
// and summary are updated in the smarty template
function updateCheckboxStates(items) {
    $('.addToCartCheckbox').each(function(){
        var id = $(this).attr('id').substr('checkbox_'.length);
        if (items.indexOf(id) != -1) {
            $('#checkbox_'+id).attr('checked', 'checked');
        }
    });
}

function updateCartSummary(items) {
    $('#cartSize').empty().append(items.length);
}