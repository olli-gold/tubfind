var edhttp = null;
if (window.XMLHttpRequest) {
    edhttp = new XMLHttpRequest();
} else if (window.ActiveXObject) {
    edhttp = new ActiveXObject("Microsoft.XMLHTTP");
}

if (edhttp != null) {
    var recString = edgup( 'Record' );
    edhttp.open( "GET" , "/AJAX/Editions?id=" + recString , true );

    edhttp.onreadystatechange = edrecommend;
    edhttp.send( null );
}

function edrecommend() {
    if ( edhttp.readyState == 4 ) {
        var htmlResponse = edhttp.responseText;
        document.getElementById( "othereditions" ).innerHTML = htmlResponse;
    }
}

function edgup( name )
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
