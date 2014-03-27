$(document).ready(function() {
    checkPrintStatuses();
});

function checkPrintStatuses() {
    var id = $.map($('.recordId'), function(i) {
        return $(i).attr('id').substr('record'.length);
    });
    var currentId;
    for (var ids in id) {
        currentId = id[ids];
        $.ajax({
            dataType: 'json',
            url: path + '/AJAX/JSON/?method=getPrintedStatus',
            data: {"id":currentId},
            success: function(resp) {
                if(resp.status == 'OK') {
                    $.each(resp.data, function(i, res) {
                        if (res.id) {
                            if (res.status == "2") {
                                $('#printed' + res.originalId + "-availability").empty().append(vufindString.alsoPrinted);
                            }
                            if (res.status == "3") {
                                $('#printed' + res.originalId + "-availability").empty().append(vufindString.maybeAlsoPrinted);
                            }
                            if (res.status == "5") {
                                $('#printed' + res.originalId + "-availability").empty().append(vufindString.ebookAlsoPrinted);
                            }
                            $('#printed' + res.originalId + "-availability").show();
                            if (res.edition) {
                                $('#printed' + res.originalId + "-volume").empty().append('<a href="'+url+'/Record/'+res.id+'">'+res.edition+'</a>');
                            }
                            else if (res.gbvtitle) {
                                $('#printed' + res.originalId + "-volume").empty().append('<a href="'+url+'/Record/'+res.id+'">'+res.gbvtitle+' '+res.gbvdate+'</a>');
                            }
                            else {
                                $('#printed' + res.originalId + "-volume").empty().append('<a href="'+url+'/Record/'+res.id+'">'+res.title+' '+res.volume+'.'+res.date+'</a>');
                            }
                            $('#printed' + res.originalId + "-volume").show();
                        }
                        // if the ID is null, set availability information from EZB, if possible
                        else {
                            if (res.signature) {
                                $('#printed' + res.originalId + "-availability").empty().append(vufindString.alsoPrinted);
                                $('#printed' + res.originalId + "-availability").show();
                                if (res.edition) {
                                    $('#printed' + res.originalId + "-volume").empty().append(res.edition);
                                    $('#printed' + res.originalId + "-volume").show();
                                }
                                $('#callnumber' + res.originalId).empty().append(res.signature);
                                $('#callnumber' + res.originalId).show();
                                $('#callnumber' + res.originalId + 'label').show();
                            }
                            /*
                            $('#location' + res.originalId).empty().append(res.location);
                            $('#location' + res.originalId).show();
                            $('#location' + res.originalId + 'label').show();
                            */
                        }
                        // show the whole box
                        $('#printed' + res.originalId).show();
                    });
                }
            }
        });
    }
}
