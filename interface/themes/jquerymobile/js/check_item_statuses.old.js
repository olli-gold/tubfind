$('.results-page').live('pageshow', function() {
    checkItemStatuses();
});

function checkItemStatuses() {
    var id = $.map($('.recordId'), function(i) {
        return $(i).attr('id').substr('record'.length);
    });
    if (id.length) {
        $(".ajax_availability").show();
        $.ajax({
            dataType: 'json',
            url: path + '/AJAX/JSON?method=getItemStatuses',
            data: {id:id},
            success: function(response) {
                if (response.status == 'OK') {
                    $.each(response.data, function(i, result) {
                        var safeId = jqEscape(result.id);
                        if (typeof(result.missing_data) != 'undefined'
                            && result.missing_data
                        ) {
                            // No data is available -- hide the entire status area:
                            $('#callnumAndLocation' + safeId).hide();
                            $('.status' + safeId).empty();
                        } else if (result.locationList) {
                            // Not supported in this theme:
                            $('#callnumAndLocation' + safeId).hide();
                            $('.status' + safeId).empty();
                        } else {
                            // Default case -- load call number and location into appropriate containers:
                            $('.callnumber' + safeId).empty().append(result.callnumber);
                            $('.location' + safeId).empty().append(result.reserve == 'true' ? result.reserve_message : result.location);
                            $('.status' + safeId).empty().append(result.availability_message);
                        }
                    });
                } else {
                    // display the error message on each of the ajax status place holder
                    $(".ajax_availability").empty().append(response.data);
                }
                $(".ajax_availability").removeClass('ajax_availability');
            }
        });
    }
}
