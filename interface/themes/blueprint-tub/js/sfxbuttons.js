$(document).ready(function() {
    checkFulltextButtons();
});

function checkFulltextButtons() {
    var id = $.map($('.recordId'), function(i) {
        return $(i).attr('id').substr('record'.length);
    });
    var currentId;
    for (var ids in id) {
        currentId = id[ids];
        checkImage(currentId);
    }
}

function checkImage(currentId) {
        var ouimage = document.getElementById("openurlimage"+currentId);
        if (ouimage) {
            ouimage.onload = function(){
                if (ouimage.complete){
                    var height = ouimage.height;
                    var width = ouimage.width;
                    if (width <= 1 && height <= 1) {
                        $('#sfxmenu'+currentId).removeClass('hidden');
                    }
                }
            }
        }
        else {
            $('#sfxmenu'+currentId).removeClass('hidden');
        }
}