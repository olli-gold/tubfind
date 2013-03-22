$(document).ready(function() {
    checkItemStatuses();
});

function checkItemStatuses() {
    var id = $.map($('.recordId'), function(i) {
        return $(i).attr('id').substr('record'.length);
    });
    var currentId;
    for (var ids in id) {
        currentId = id[ids];
        $(".ajax_availability").show();
        $.ajax({
            dataType: 'json',
            url: path + '/AJAX/JSON?method=getItemStatuses',
            data: {"id[]":currentId},
            success: function(response) {
                if(response.status == 'OK') {
                    $.each(response.data, function(i, result) {
                        if (result.callnumber == 'Unknown' || result.callnumber == '') {
                            $('#callnumber' + result.id).hide();
                            $('#callnumber' + result.id + 'label').hide();
                        }
                        else {
                            $('#callnumber' + result.id).empty().append(result.callnumber);
                        }
                        if (result.location == 'Unknown' || result.location == '' || result.location == 'Unbekannt') {
                            $('#location' + result.id).hide();
                            $('#location' + result.id + 'label').hide();
                        }
                        else {
                            $('#location' + result.id).empty().append(result.reserve == 'true' ? result.reserve_message : result.location);
                        }
                        if ((result.callnumber == 'Unknown' && result.location == 'Unknown') || result.callnumber == 'Einzelsign.') {
                            $('#status' + result.id).hide();
                        }
                        else {
                            $('#status' + result.id).empty().append(result.availability_message);
                            if (result.duedate && result.availability == 'false') {
                                $('#status' + result.id).append(' until '+result.duedate);
                            }
                            //if (result.reservationUrl && result.location == 'Magazin') {
                            if (result.reservationUrl) {
                                $('#status' + result.id).append(' '+result.reservationUrl);
                            }
                        }
                        if (result.presenceOnly == '1') {
                            $('#status' + result.id).append(' Nur Präsenznutzung');
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
