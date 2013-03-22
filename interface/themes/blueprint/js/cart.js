var _CART_COOKIE = 'vufind_cart';
var _CART_COOKIE_DELIM = "\t";

$(document).ready(function() {

    var cartRecordId = $('#cartId').val();
    $('#cartItems').hide();
    $('#viewCart, #updateCart, #updateCartBottom').removeClass('offscreen');

    // Record
    $('#recordCart').removeClass('offscreen').click(function() {
        if(cartRecordId != undefined) {
            if ($(this).hasClass('bookbagAdd')) {
                updateCartSummary(addItemToCartCookie(cartRecordId));
                $(this).html(vufindString.removeBookBag).removeClass('bookbagAdd').addClass('bookbagDelete');
            } else {
                updateCartSummary(removeItemFromCartCookie(cartRecordId));
                $(this).html(vufindString.addBookBag).removeClass('bookbagDelete').addClass('bookbagAdd');
            }
        }
        return false;
    });
    redrawCartStatus()
    var $form = $('form[name="bulkActionForm"]');
    registerUpdateCart($form);

});

function registerUpdateCart($form) {
    if($form) {
        $("#updateCart, #updateCartBottom").unbind('click').click(function(){
            var elId = this.id;
            var selected = $("input[name='ids[]']:checked", $form);
            var cleanSelected = [];
            $(selected).each(function(i) {
                var item = this.value.replace('[^a-z0-9]','');
                cleanSelected[i] = item;
            });

            if (cleanSelected.length > 0) {
                var inCart = 0;
                var msg = "";
                var orig = getItemsFromCartCookie();
                $(cleanSelected).each(function(i) {
                    for (i in orig) {
                        if (this == orig[i]) {
                            inCart++;
                        }
                    }
                    addItemToCartCookie(this);
                });
                var updated = getItemsFromCartCookie();
                var added = updated.length - orig.length;
                msg += added + " " + vufindString.itemsAddBag + "<br />";
                if (inCart > 0 && orig.length > 0) {
                    msg += inCart + " " + vufindString.itemsInBag + "<br />";
                }
                if (updated.length >= vufindString.bookbagMax) {
                  msg += vufindString.bookbagFull + "<br />";
                }
                cartHelp(msg, elId);
            } else {
                cartHelp(vufindString.bulk_noitems_advice, elId);
            }
            redrawCartStatus();
            return false;
        });
    }
}

function cartHelp(msg, elId) {
    contextHelp.flash('#' + elId, '10', '1', 'down', 'right', msg, 5000);
}

function redrawCartStatus() {
    var items = getItemsFromCartCookie();
    var checkBoxItems = [];
    $(items).each(function(i, value) {
        checkBoxItems[i] = value.replace('[^a-z0-9]','');
    })
    removeCartCheckbox();
    updateRecordState(items);
    updateCartSummary(items);
}

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
    if(items.length < vufindString.bookbagMax) {
      items.push(item);
    }
    items = uniqueValues(items);
    $.cookie(_CART_COOKIE, items.join(_CART_COOKIE_DELIM), { path: '/' });
    return items;
}

function removeItemFromCartCookie(item) {
    var items = getItemsFromCartCookie();
    var index = $.inArray(item, items);
    if (index != -1) {
        items.splice(index, 1);
    }
    $.cookie(_CART_COOKIE, items.join(_CART_COOKIE_DELIM), { path: '/' });
    return items;
}

function updateRecordState(items) {
    var cartRecordId = $('#cartId').val();
    if (cartRecordId != undefined) {
        var index = $.inArray(cartRecordId, items);
        if(index != -1) {
            $('#recordCart').html(vufindString.removeBookBag).removeClass('cartAdd').addClass('cartRemove');
        } else {
            $('#recordCart').html(vufindString.addBookBag).removeClass('cartRemove').addClass('cartAdd');
        }
    }
}

function updateCartSummary(items) {
    $('#cartSize').empty().append(items.length);
    var cartStatus = (items.length >= vufindString.bookbagMax) ? " (" + vufindString.bookbagStatusFull + ")" : "&nbsp;";
    $('#cartStatus').html(cartStatus);
}

function removeRecordState() {
    $('#recordCart').html(vufindString.addBookBag).removeClass('cartRemove').addClass('cartAdd');
    $('#cartSize').empty().append("0");
}

function removeCartCheckbox() {
 $('.checkbox_ui, .selectAllCheckboxes').each(function(){
     $(this).attr('checked', false);
 });
}