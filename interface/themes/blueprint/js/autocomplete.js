$(document).ready(function(){
    // initialize autocomplete
    initAutocomplete(); 
});

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