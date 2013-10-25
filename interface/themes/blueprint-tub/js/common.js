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
    
    // attach mouseover event to grid view records
    $('.gridCellHover').mouseover(function() {
        $(this).addClass('gridMouseOver')
    });
    
    // attach mouseout event to grid view records
    $('.gridCellHover').mouseout(function() {
        $(this).removeClass('gridMouseOver')
    });  
    
    // assign click event to "viewCart" links
    $('a.viewCart').click(function() {
        var $dialog = getLightbox('Cart', 'Home', null, null, this.title, '', '', '', {viewCart:"1"});
        return false;
    });
    
    // check all
    $('.checkall').click(function () {
        $(this).parents('form:eq(0)').find(':checkbox').attr('checked', this.checked);
    });
    
    // Print
    var url = window.location.href;
    if(url.indexOf('?' + 'print' + '=') != -1  || url.indexOf('&' + 'print' + '=') != -1) {
        $("link[media='print']").attr("media", "all");
        window.print();
    }

    // Toggle side facets finc like -- taken from HeBIS
    
    // Set icons to facet
    $('.narrowList').each( function(e) {
        if ($(this).contents('dd').eq(0).hasClass('offscreen') == true){
            $(this).contents('dt').css('cursor','pointer');
            $(this).contents('dt').find('#fplus').removeClass('hidden');
            $(this).contents('dt').find('#fminus').addClass('hidden');
        } else {
            $(this).contents('dt').css('cursor','pointer');
            $(this).contents('dt').find('#fplus').addClass('hidden');
            $(this).contents('dt').find('#fminus').removeClass('hidden');
        }
    });

    // Removed collapsed class when facet elements are applied -- taken from HeBIS
    $('.collapsed > dd').each( function () { 
        if ($(this).contents('dl').hasClass('applied') == true) {
            $(this).parent().removeClass('collapsed');
        }
    });
    
    // Collapse side facets --taken from HeBIS
    $('.collapsed > dt').each( function () { toggleFacet(this); });
    
    // Toggle side facets informations
    $('.narrowList > dt').click( function () { toggleFacet(this); });

    // end facet functions taken from HeBIS

    // legt sich unten auf jede Seite, nicht benoetigt. HW
    //ContextHelp
    // contextHelp.init();
    // contextHelp.contextHelpSys.load();

    // finc specific added functions 

    // finc
    // add listpager result.tpl
    if ($('ul.listpager').length > 0) {
        $('ul.listpager').each(function( i, elem ) {
              listPager.init( $(elem) );
        });
    }

    // add tablepager
    if ($('table.extended').length > 0) {
        $('table.extended').each(function( i, elem ) {
              tablePager.init( $(elem) );
        });
    }

    $('.showAdditionalRow').each(function() {
       if ($(this).next('.additionalRow').length == 1){
           $(this).find('.more').append('<img src="' + path + '/images/plus.gif" alt="" />').css('cursor','pointer');
       }
    });

    // toggle extra information
    $('.more').toggle(
        function () {
           $(this).parents().next('.additionalRow').show(200);
           $(this).find('img').attr('src', path + '/images/minus.gif');
        },
        function () {
            $(this).parents().next('.additionalRow').hide(600);
            $(this).find('img').attr('src', path + '/images/plus.gif');
     });

     // toggle side facets
     // set icons to facet
/*
     $('.narrowList').each( function(e) {
        if ($(this).contents('dd').eq(0).hasClass('offscreen') == true){
            $(this).contents('dt').prepend('<img src="' + path + '/images/plus.gif" alt="" />').css('cursor','pointer');
        } else {
            $(this).contents('dt').prepend('<img src="' + path + '/images/minus.gif" alt="" />').css('cursor','pointer');
        }
      });
*/
    // toggle side facets informations
/*
    $('.narrowList > dt').click(
           function () {
           if ($(this).parents().contents('dd').eq(0).hasClass('offscreen') == true) {
               $(this).parents().contents('dd').removeClass('offscreen');
               $(this).find('img').attr('src', path + '/images/minus.gif');
           } else {
               var facet_length = ($(this).parents().contents('dd').length -1) < 5 ? $(this).parents().contents('dd').length -1 : 5;
               var facet = $(this).parents().contents('dd').eq(facet_length).attr('id').substring(4);
               lessFacets(facet);               
               $(this).parents().contents('dd').addClass('offscreen');
               $(this).find('img').attr('src', path + '/images/plus.gif');
          }
     });
*/
    // toggle top facet informations
    addMoreLessBox('.authorbox','.additional','.show-more-less','true');

    // toggle top facets informations
    $('.show-more-less').click( function () {
        toogleOffscreen($(this), '.additional', 'offscreen');
    });

    /* workaround to submitting values of autocomplete function if 
    the mouser arrow hovers over the proposal fields and the user
    press enter */
    /*
    $('.ui-autocomplete').bind('mouseover', function (e, index) {
        var input = $('input.autocomplete').val();
        $(window).keydown(function(event){
            switch (event.keyCode) {
                case 13: $('input.autocomplete').val(input);
                            break;
            }
        });
    });
    */
    
});

/* pager for multisets in results.tpl */
var listPager = {
    init: function (elem) {
        var iterator = 1;
        var factor = 5;
        var sum = elem.children('li').length;
        if (sum > factor) {    
            for(var i = factor; i < sum; i++) {
                elem.children('li').eq(i).addClass('offscreen');
            }
            listPager.getListEvent(elem, iterator, factor, sum);
        }
    },
    getNextListEntries: function (elem, iterator, factor, sum) {
        var stop = (sum > (factor*iterator)) ? (factor*iterator) : sum;
        elem.find('.pager').remove('li.pager');
        elem.find('.allResults').remove('li.allResults');
        for (var i = (factor*(iterator-1)); i < stop; i++) {
            elem.children('li').eq(i).removeClass('offscreen');
        }  
        if (sum > (factor*iterator)) {
            listPager.getListEvent(elem, iterator, factor, sum);
        } 
    },
    getAllListEntries: function (elem) {
        elem.find('.pager').remove('li.pager');
        elem.find('.allResults').remove('li.allResults');
        for (var i = (factor*(iterator-1)); i < sum; i++) {
            elem.children('li').eq(i).removeClass('offscreen');
        }
    },
    getListEvent: function (elem, iterator, factor, sum) {
        elem.append('<li class="pager"><img class="pageimage" src="../images/arrow-down.png" alt="mehr" /></li>');
        alert("Test");
        elem.append('<li class="allResults"><img class="pageimage" src="../images/arrow-all-down.png" alt="alle" /></li>');
        elem.find('.pager').click( function () {
            listPager.getNextListEntries(elem, iterator + 1, factor, sum);
        });
        elem.find('.allResults').click( function () {
            listPager.getAllListEntries(elem, iterator + 1, factor, sum);
        });
    }
}

/* pager for multisets in results.tpl */
/* list pager for multisets in result.tpl */
var listPager = {
    init: function (elem) {
        var iterator = 1;
        var factor = 5;
        var sum = elem.children('li').length;
        if (sum > factor) {
            for(var i = (factor); i < sum; i++) {
                elem.children('li').eq(i).addClass('offscreen');
            }
            listPager.showListEvents(elem);
            listPager.hideListMinimizeEvents(elem);
            listPager.setSumEntries(elem, sum);
            listPager.listenEvents(elem, iterator, factor, sum);
        } else {
            listPager.hidePager(elem);
        }
    },
    getNextListEntries: function (elem, iterator, factor, sum) {
        var stop = (sum > (factor*iterator)) ? (factor*iterator) : sum;
        for (var i = (factor*(iterator-1)); i < stop; i++) {
            elem.children('li').eq(i).removeClass('offscreen');
        }
        if (sum <= (factor*iterator)) {
            listPager.showListMinimizeEvents(elem);
            listPager.hideListEvents(elem);
        }
        listPager.listenEvents(elem, iterator, factor, sum);

    },
    getAllListEntries: function (elem, iterator, factor, sum) {
        listPager.showListMinimizeEvents(elem);
        listPager.hideListEvents(elem);
        for (var i = (factor*(iterator-1)); i < sum; i++) {
            elem.children('li').eq(i).removeClass('offscreen');
        }
        listPager.listenEvents(elem, iterator, factor, sum);
    },
    listenEvents: function (elem, iterator, factor, sum) {
        elem.next('ul').children('.next-parts').click( function () {
            listPager.getNextListEntries(elem, iterator + 1, factor, sum);
        });
        elem.next('ul').children('.all-parts').click( function () {
            listPager.getAllListEntries(elem, iterator + 1 , factor, sum);
        });
        elem.next('ul').children('.setback-parts').click( function () {
            iterator = 1;
            listPager.init(elem);
        });
        /*$('.next-parts').click( function () {
            listPager.getNextListEntries(elem, iterator + 1, factor, sum);
        });
        $('.all-parts').click( function () {
            listPager.getAllListEntries(elem, iterator + 1 , factor, sum);
        });
        $('.setback-parts').click( function () {
            listPager.init(elem);
        });*/
    },
    showListEvents: function () {
        $('ul .next-parts').show();
        $('ul .all-parts').show();
    },
    hideListEvents: function () {
        $('ul .next-parts').hide();
        $('ul .all-parts').hide();
    },
    showListMinimizeEvents: function () {
        $('ul .setback-parts').show();
    },
    hideListMinimizeEvents: function () {
        $('ul .setback-parts').hide();
    },
    setSumEntries: function (elem, sum){
        if ((elem).next('ul').find('li.hits').length == 0) {
            $.getJSON(path + '/AJAX/JSON?method=getTranslation', {"id": "", "str": "Showing"}, function(response) {
                 (elem).next('ul').children('li').eq(3).after('<li class="hits">'+ response.data.translation + ': ' + sum + '</li>');
                // (elem).next('ul').eq(3).after('<li class="hits">'+ response.data.translation + ': ' + sum + '</li>');
            });
        }
    },
    hidePager: function (elem) {
        elem.next('ul.pager').remove();
    }
}

/* pager for multisets in core.tpl */
var tablePager = {
    init: function (elem) {
        var iterator = 1;
        var factor = 5;
        var sum = elem.children('tbody').children('tr').length;
        if (sum > factor) {
            for(var i = (factor); i < sum; i++) {
                elem.children('tbody').children('tr').eq(i).addClass('offscreen');
            }
            tablePager.showListEvents(elem);
            tablePager.hideListMinimizeEvents(elem);
            tablePager.setSumEntries(sum);
            tablePager.listenEvents(elem, iterator, factor, sum);
        } else {
            tablePager.hidePager(elem);
        }
    },
    getNextListEntries: function (elem, iterator, factor, sum) {
        var stop = (sum > (factor*iterator)) ? (factor*iterator) : sum;
        for (var i = (factor*(iterator-1)); i < stop; i++) {
            elem.children('tbody').children('tr').eq(i).removeClass('offscreen');
        }
        if (sum <= (factor*iterator)) {
            tablePager.showListMinimizeEvents(elem);
            tablePager.hideListEvents(elem);
        }
        tablePager.listenEvents(elem, iterator, factor, sum);

    },
    getAllListEntries: function (elem, iterator, factor, sum) {
        tablePager.showListMinimizeEvents(elem);
        tablePager.hideListEvents(elem);
        for (var i = (factor*(iterator-1)); i < sum; i++) {
            elem.children('tbody').children('tr').eq(i).removeClass('offscreen');
        }
        tablePager.listenEvents(elem, iterator, factor, sum);
    },
    listenEvents: function (elem, iterator, factor, sum) {
        $('.next-parts').click( function () {
            tablePager.getNextListEntries(elem, iterator + 1, factor, sum);
        });
        $('.all-parts').click( function () {
            tablePager.getAllListEntries(elem, iterator +1 , factor, sum);
        });
        $('.setback-parts').click( function () {
            tablePager.init(elem);
        });
    },
    showListEvents: function () {
        $('ul .next-parts').show();
        $('ul .all-parts').show();
    },
    hideListEvents: function () {
        $('ul .next-parts').hide();
        $('ul .all-parts').hide();
    },
    showListMinimizeEvents: function () {
        $('ul .setback-parts').show();
    },
    hideListMinimizeEvents: function () {
        $('ul .setback-parts').hide();
    },
    setSumEntries: function (sum){
        if ($('ul.pager li.hits').length == 0) {
            $.getJSON(path + '/AJAX/JSON?method=getTranslation', {"id": "", "str": "Showing"}, function(response) {
                $('ul.pager li').eq(3).after('<li class="hits">'+ response.data.translation + ': ' + sum + '</li>');
            });
        }
    },
    hidePager: function () {
        $('ul.pager').remove();
    }
}

function addMoreLessBox(namespace, hascontent, wheretoadd, prependflag) {
    var flag = (prependflag == 'true') ? 'true' : 'false';
    if ($(namespace).find(hascontent).length > 0) {
        if (flag == 'true'){
            $(namespace).find(wheretoadd).prepend('<img src="' + path + '/images/plus.gif" alt="" />').css('cursor','pointer');
        } else {
            $(namespace).find(wheretoadd).append('<img src="' + path + '/images/plus.gif" alt="" />').css('cursor','pointer');
        }
    }
}

function toogleOffscreen(i, additionalrow, cssclass) {
    if ($(i).parents().find(additionalrow).hasClass(cssclass) == true) {
        $(i).parents().find(additionalrow).removeClass(cssclass);
        $(i).find('img').attr('src', path + '/images/minus.gif');
    } else {
        $(i).parents().find(additionalrow).addClass(cssclass);
        $(i).find('img').attr('src', path + '/images/plus.gif');
    }
    return;
}


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

function filterAll(element, formId) {
    //  Look for filters (specifically checkbox filters)
    if (formId == null) {
        formId = "searchForm";
    }
    $("#" + formId + " :input[type='checkbox'][name='filter[]']")
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

function htmlEncode(value){
    if (value) {
        return jQuery('<div />').text(value).html();
    } else {
        return '';
    }
}

// mostly lifted from http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F
function jqEscape(myid) {
    return String(myid).replace(/(:|\.)/g,'\\$1');
}

function printIDs(ids) {

    var url = '';
    if(ids.length == 0) {
        return false;
    }
    if(ids.length == 1) {
            url =  path + '/Record/' + encodeURIComponent(ids[0]) + '?type=ids&print=true';
    }
    else {
        $(ids).each(function() {
           url += encodeURIComponent(this) + '+'; 
        });
        url =  path + '/Search/Results?lookfor=' + url + '&type=ids&print=true';
    }
    window.open(url);
    return true;
}

var contextHelp = {
        
    init: function() {
        $('body').append('<table cellspacing="0" cellpadding="0" id="contextHelp"><tbody><tr class="top"><td class="left"></td><td class="center"><div class="arrow up"></div></td><td class="right"></td></tr><tr class="middle"><td></td><td class="body"><div id="closeContextHelp"></div><div id="contextHelpContent"></div></td><td></td></tr><tr class="bottom"><td class="left"></td><td class="center"><div class="arrow down"></div></td><td class="right"></td></tr></tbody></table>');
    },
    
    hover: function(listenTo, widthOffset, heightOffset, direction, align, msgText) {     
        $(listenTo).mouseenter(function() {
            contextHelp.contextHelpSys.setPosition(listenTo, widthOffset, heightOffset, direction, align, '', false);
            contextHelp.contextHelpSys.updateContents(msgText);
        });
        $(listenTo).mouseleave(function() {
            contextHelp.contextHelpSys.hideMessage();
        });
    }, 
    
    flash: function(id, widthOffset, heightOffset, direction, align, msgText, duration) {
        this.contextHelpSys.setPosition(id, widthOffset, heightOffset, direction, align);
        this.contextHelpSys.updateContents(msgText);
        setTimeout(this.contextHelpSys.hideMessage, duration);
    },
    
    contextHelpSys: {
        CHTable:"#contextHelp",
        CHContent:"#contextHelpContent",
        arrowUp:"#contextHelp .arrow.up",
        arrowDown:"#contextHelp .arrow.down",
        closeButton:"#closeContextHelp",
        showCloseButton: true,
        curElement:null,
        curOffsetX:0,
        curOffsetY:0,
        curDirection:"auto",
        curAlign:"auto",
        curMaxWidth:null,
        isUp:false,
        load:function(){
            $(contextHelp.contextHelpSys.closeButton).click(contextHelp.contextHelpSys.hideMessage);
            $(window).resize(contextHelp.contextHelpSys.position)},
        setPosition:function(element, offsetX, offsetY, direction, align, maxWidth, showCloseButton){
            if(element==null){element=document}
            if(offsetX==null){offsetX=0}
            if(offsetY==null){offsetY=0}
            if(direction==null){direction="auto"}
            if(align==null){align="auto"}
            if(showCloseButton==null){showCloseButton=true}
            contextHelp.contextHelpSys.curElement=$(element);
            contextHelp.contextHelpSys.curOffsetX=offsetX;
            contextHelp.contextHelpSys.curOffsetY=offsetY;
            contextHelp.contextHelpSys.curDirection=direction;
            contextHelp.contextHelpSys.curAlign=align;
            contextHelp.contextHelpSys.curMaxWidth=maxWidth;
            contextHelp.contextHelpSys.showCloseButton=showCloseButton;},
        position:function(){
            if(!contextHelp.contextHelpSys.isUp||!contextHelp.contextHelpSys.curElement.length){return}
            var offset=contextHelp.contextHelpSys.curElement.offset();
            var left=parseInt(offset.left)+parseInt(contextHelp.contextHelpSys.curOffsetX);
            var top=parseInt(offset.top)+parseInt(contextHelp.contextHelpSys.curOffsetY);
            var direction=contextHelp.contextHelpSys.curDirection;
            var align=contextHelp.contextHelpSys.curAlign;
            if(contextHelp.contextHelpSys.curMaxWidth){
                $(contextHelp.contextHelpSys.CHTable).css("width",contextHelp.contextHelpSys.curMaxWidth)}
            else{
                $(contextHelp.contextHelpSys.CHTable).css("width","auto")}
            if(direction=="auto"){
                if(parseInt(top)-parseInt($(contextHelp.contextHelpSys.CHTable).height()<$(document).scrollTop())){
                    direction="down"}
                else{direction="up"}
            }
            if(direction=="up"){
                top = parseInt(top) - parseInt($(contextHelp.contextHelpSys.CHTable).height());
                $(contextHelp.contextHelpSys.arrowUp).css("display","none");
                $(contextHelp.contextHelpSys.arrowDown).css("display","block")}
            else{
                if(direction=="down"){
                    top = parseInt(top) + parseInt(contextHelp.contextHelpSys.curElement.height());
                    $(contextHelp.contextHelpSys.arrowUp).css("display","block");
                    $(contextHelp.contextHelpSys.arrowDown).css("display","none")}
                }
            if(align=="auto"){
                if(left+parseInt($(contextHelp.contextHelpSys.CHTable).width()>$(document).width())){
                    align="left"}
                else{align="right"}
            }
            if(align=="right"){
                left-=24;
                $(contextHelp.contextHelpSys.arrowUp).css("background-position","0 0");
                $(contextHelp.contextHelpSys.arrowDown).css("background-position","0 -6px")
            }
            else{
                if(align=="left"){
                    left-=parseInt($(contextHelp.contextHelpSys.CHTable).width());
                    left+=24;
                    $(contextHelp.contextHelpSys.arrowUp).css("background-position","100% 0");
                    $(contextHelp.contextHelpSys.arrowDown).css("background-position","100% -6px")}
            }
            if(contextHelp.contextHelpSys.showCloseButton) {
                $(contextHelp.contextHelpSys.closeButton).show();
            } else {
                $(contextHelp.contextHelpSys.closeButton).hide();
            }
            $(contextHelp.contextHelpSys.CHTable).css("left",left + "px");
            $(contextHelp.contextHelpSys.CHTable).css("top",top + "px");},
            
        updateContents:function(msg){
            contextHelp.contextHelpSys.isUp=true;
            $(contextHelp.contextHelpSys.CHContent).empty();
            $(contextHelp.contextHelpSys.CHContent).append(msg);
            contextHelp.contextHelpSys.position();
            $(contextHelp.contextHelpSys.CHTable).hide();
            $(contextHelp.contextHelpSys.CHTable).fadeIn()
            },
        hideMessage:function(){
            if(contextHelp.contextHelpSys.isUp){
                $(contextHelp.contextHelpSys.CHTable).fadeOut();
                contextHelp.contextHelpSys.isUp=false}
        }
    }
}

// Toggle facet --HeBIS
function toggleFacet(elemId) {
    if ($(elemId).parents().contents('dd').eq(0).hasClass('offscreen') == true) {
        $(elemId).parents().contents('dd').removeClass('offscreen');
        $(elemId).css('cursor','pointer');
        $(elemId).find('#fplus').addClass('hidden');
        $(elemId).find('#fminus').removeClass('hidden');
        $(elemId).parents().contents('dd').eq(0).slideDown(400);
    } else {
        $(elemId).css('cursor','pointer');
        $(elemId).find('#fplus').removeClass('hidden');
        $(elemId).find('#fminus').addClass('hidden');
        $(elemId).parents().contents('dd').eq(0).slideUp(200, function () { $(this).parents().contents('dd').addClass('offscreen'); });             
        var facet_length = ($(elemId).parents().contents('dd').length -1) < 5 ? $(elemId).parents().contents('dd').length -1 : 5;
        var facet = $(elemId).parents().contents('dd').eq(facet_length).attr('id').substring(4);
        lessFacets(facet);               
   }
}

function moreFacets(name) {
    $("#more"+name).hide();
    $("#narrowGroupHidden_"+name).removeClass("offscreen").slideUp(0).slideDown('fast');
}

function lessFacets(name) {
    $("#more"+name).show();
    $("#narrowGroupHidden_"+name).slideUp('fast', function () { $("#narrowGroupHidden_"+name).addClass("offscreen"); });    
}

