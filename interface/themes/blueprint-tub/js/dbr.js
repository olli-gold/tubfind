var http = null;
if (window.XMLHttpRequest) {
    http = new XMLHttpRequest();
} else if (window.ActiveXObject) {
    http = new ActiveXObject("Microsoft.XMLHTTP");
}

if (http != null) {
    var lookForString = gup( 'lookfor' );
    http.open( "GET" , "/AJAX/DBRecommendations?lookfor=" + lookForString , true );

    http.onreadystatechange = recommend;
    http.send( null );
}

function recommend() {
    if ( http.readyState == 4 ) {
        var htmlResponse = http.responseText;
        document.getElementById( "dbRecommender" ).innerHTML = htmlResponse;
    }
}

function gup( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}