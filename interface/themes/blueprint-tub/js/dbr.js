var http = null;
if (window.XMLHttpRequest) {
    http = new XMLHttpRequest();
} else if (window.ActiveXObject) {
    http = new ActiveXObject("Microsoft.XMLHTTP");
}

if (http != null) {
    var lookForString = encodeURI( 'html' );
    http.open( "GET" , "/Search/DBRecommendations?lookfor=" + lookForString , true );

    http.onreadystatechange = recommend;
    http.send( null );
}

function recommend() {
    if ( http.readyState == 4 ) {
        var htmlResponse = http.responseText;
        document.getElementById( "dbRecommender" ).innerHTML = htmlResponse;
    }
}