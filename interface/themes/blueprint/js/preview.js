$(document).ready(function() {
    getBookPreviews();
});

function getBookPreviews() {
    var skeys = '';
    $('.previewBibkeys').each(function(){
        skeys += $(this).attr('class');
    });
    skeys = skeys.replace(/previewBibkeys/g, '').replace(/^\s+|\s+$/g, '');
    var bibkeys = skeys.split(/\s+/);
    
    // fetch Google preview if enabled
    if ($('.previewGBS').length > 0) {
        var script = 'https://encrypted.google.com/books?jscmd=viewapi&bibkeys=' 
            + bibkeys.join(',') + '&callback=processGBSBookInfo';
        $.getScript(script);
    }
    
    // fetch OpenLibrary preview if enabled
    if ($('.previewOL').length > 0) {
        var script = 'http://openlibrary.org/api/books?bibkeys=' 
            + bibkeys.join(',') + '&callback=processOLBookInfo';
        $.getScript(script);
    }
    
    // fetch HathiTrust preview if enabled
    if ($('.previewHT').length > 0) {
        getHTPreviews(skeys);
    }
}

function getHTPreviews(skeys) {
    skeys = skeys.replace(/(ISBN|LCCN|OCLC)/gi, '$1:').toLowerCase();
    var bibkeys = skeys.split(/\s+/);
    // fetch 20 books at time if there are more than 20 
    // since hathitrust only allows 20 at a time
    // as per http://vufind.org/jira/browse/VUFIND-317
    var batch = [];
    for(i = 0; i < bibkeys.length; i++) {
        batch.push(bibkeys[i]);
        if ((i > 0 && i % 20 == 0) || i == bibkeys.length-1) {
            var script = 'http://catalog.hathitrust.org/api/volumes/brief/json/' 
                + batch.join('|') + '&callback=processHTBookInfo';
            $.getScript(script);
            batch = [];
        }
    }
}

function processGBSBookInfo(booksInfo) {
    processBookInfo(booksInfo, 'previewGBS');
}

function processOLBookInfo(booksInfo) {
    processBookInfo(booksInfo, 'previewOL');
}

function processHTBookInfo(booksInfo) {
    for (b in booksInfo) {
        var bibkey = b.replace(/:/, '').toUpperCase();
        var $link = $('.previewHT.' + bibkey);
        var items = booksInfo[b].items;
        for (var i = 0; i < items.length; i++) {
            if (items[i].rightsCode == "pd" || items[i].rightsCode == "world") {
                $link.attr('href', items[i].itemURL).show();
            }
        }
    }
}

function processBookInfo(booksInfo, previewClass) {
    for (bibkey in booksInfo) {
        var bookInfo = booksInfo[bibkey];
        if (bookInfo) {
            if (bookInfo.preview == "full" || bookInfo.preview == "partial") {
                $link = $('.' + previewClass + '.' + bibkey);
                $link.attr('href', bookInfo.preview_url).show();
            }
        }
    }
}

