var http = null;
if (window.XMLHttpRequest) {
    http = new XMLHttpRequest();
} else if (window.ActiveXObject) {
    http = new ActiveXObject("Microsoft.XMLHTTP");
}

if (http != null) {
    var lookForString = gup( 'Record' );
    http.open( "GET" , "/AJAX/MoreLikeThis?id=" + lookForString , true );

    http.onreadystatechange = recommend;
    http.send( null );
}

function recommend() {
    if ( http.readyState == 4 ) {
        var htmlResponse = http.responseText;
        document.getElementById( "similarrecs" ).innerHTML = htmlResponse;
    }
}

function gup( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = name+"/([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else {
    var returnwert;
    if (results[1].indexOf('/') != -1) {
      returnwert = results[1].slice(0, results[1].indexOf('/'));
    }
    else if (results[1].indexOf('?') != -1) {
      returnwert = results[1].slice(0, results[1].indexOf('?'));
    }
    else {
      returnwert = results[1];
    }
    return returnwert;
  }
}