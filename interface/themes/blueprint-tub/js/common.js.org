/**
 * Initialize common functions and event handlers.
 */
// disable caching for all AJAX requests
$.ajaxSetup({cache: false});

// set global options for the jQuery validation plugin
$.validator.setDefaults({
    errorClass: 'invalid'
});
    
// add a modified version of the original phoneUS rule 
// to accept only 10-digit phone numbers
$.validator.addMethod("phoneUS", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, ""); 
    return this.optional(element) || phone_number.length > 9 &&
        phone_number.match(/^(\([2-9]\d{2}\)|[2-9]\d{2})[2-9]\d{2}\d{4}$/);
}, 'Please specify a valid phone number');

$(document).ready(function(){
    // initialize autocomplete
    initAutocomplete();    

    // put focus on the "mainFocus" element
    $('.mainFocus').each(function(){ $(this).focus(); } );

    // support "jump menu" dropdown boxes
    $('select.jumpMenu').change(function(){ $(this).parent('form').submit(); });

    // attach click event to the "keep filters" checkbox
    $('#searchFormKeepFilters').change(function() { filterAll(this); });

    // attach click event to the search help links
    $('a.searchHelp').click(function(){
        window.open(path + '/Help/Home?topic=search', 'Help', 'width=625, height=510');
        return false;
    });

    // attach click event to the advanced search help links
    $('a.advsearchHelp').click(function(){
        window.open(path + '/Help/Home?topic=advsearch', 'Help', 'width=625, height=510');
        return false;
    });

    // assign click event to "email search" links
    $('a.mailSearch').click(function() {
        var id = this.id.substr('mailSearch'.length);
        var $dialog = getLightbox('Search', 'Email', id, null, this.title);
        return false;
    });

    // assign action to the "select all checkboxes" class
    $('input[type="checkbox"].selectAllCheckboxes').change(function(){
        $(this.form).find('input[type="checkbox"]').attr('checked', $(this).attr('checked'));
    });
});

function toggleMenu(elemId) {
    var elem = $("#"+elemId);
    if (elem.hasClass("offscreen")) {
        elem.removeClass("offscreen");
    } else {
        elem.addClass("offscreen");
    }
}

function moreFacets(name) {
    $("#more"+name).hide();
    $("#narrowGroupHidden_"+name).removeClass("offscreen");
}

function lessFacets(name) {
    $("#more"+name).show();
    $("#narrowGroupHidden_"+name).addClass("offscreen");
}

function filterAll(element) {
    //  Look for filters (specifically checkbox filters)
    $("#searchForm :input[type='checkbox'][name='filter[]']")
        .attr('checked', element.checked);
}

function extractParams(str) {
    var params = {};
    var classes = str.split(/\s+/);
    for(i = 0; i < classes.length; i++) {
        if (classes[i].indexOf(':') > 0) {
            var pair = classes[i].split(':');
            params[pair[0]] = pair[1];
        }
    }
    return params;
}

// return unique values from the given array
function uniqueValues(array) {
    var o = {}, i, l = array.length, r = [];
    for(i=0; i<l;i++) {
        o[array[i]] = array[i];
    }
    for(i in o) {
        r.push(o[i]);
    }
    return r;
}

function initAutocomplete() {
    $('input.autocomplete').each(function() {
        var params = extractParams($(this).attr('class'));
        var maxItems = params.maxItems > 0 ? params.maxItems : 10;
        var $autocomplete = $(this).autocomplete({
            source: function(request, response) {
                var type = params.type;
                if (!type && params.typeSelector) {
                    type = $('#' + params.typeSelector).val();
                } 
                $.ajax({
                    url: path + '/AJAX/JSON_Autocomplete',
                    data: {method:'getSuggestions',type:type,q:request.term},
                    dataType:'json',
                    success: function(json) {
                        if (json.status == 'OK' && json.data.length > 0) {
                            response(json.data.slice(0, maxItems));
                        } else {
                            $autocomplete.autocomplete('close');
                        }
                    }
                });
            }
        });
    });
}

