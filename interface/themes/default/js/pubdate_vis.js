function loadVis(facetFields, searchParams, baseURL, zooming) {
    // options for the graph, TODO: make configurable
    var options = {
        bars: {
           show: true,
           align: "center",
           fill: true,
           fillColor: "rgb(0,0,0)"
        },
        colors: ["rgba(255,0,0,255)"],
        legend: { noColumns: 2 },
        xaxis: { tickDecimals: 0 },
        yaxis: { min: 0, ticks: [] },
        selection: { mode: "x" },
        grid: { backgroundColor: null /*"#ffffff"*/ }
    };

    // AJAX URL

    var url = baseURL + '/AJAX/JSON_Vis?method=getVisData&facetFields=' + encodeURIComponent(facetFields) + '&' + searchParams;

    var callback =
    {
    success: function (transaction) {
        var data = eval('(' + transaction.responseText + ')');
        if (data.status == 'OK') {
            var values = data.data;
            for (var key in values) {
                // plot graph
                var placeholder = YAHOO.util.Dom.get('datevis' + key + 'x');

                //set up the hasFilter variable
                var hasFilter = true;

                //set the has filter
                if (values[key]['min'] == 0 && values[key]['max']== 0) {
                    hasFilter = false;
                }

                //check if the min and max value have been set otherwise set them to the ends of the graph
                if (values[key]['min'] == 0) {
                    values[key]['min'] = values[key]['data'][0][0] - 5;
                }
                if (values[key]['max']== 0) {
                    values[key]['max'] =  parseInt(values[key]['data'][values[key]['data'].length - 1][0], 10) + 5;
                }

                if (zooming) {
                    //check the first and last elements of the data array against min and max value (+padding)
                    //if the element exists leave it, otherwise create a new marker with a minus one value
                    if (values[key]['data'][values[key]['data'].length - 1][0] != parseInt(values[key]['max'], 10) + 5) {
                        values[key]['data'].push([parseInt(values[key]['max'], 10) + 5, -1]);
                    }
                    if (values[key]['data'][0][0] != values[key]['min'] - 5) {
                        values[key]['data'].push([values[key]['min'] - 5, -1]);
                    }
                    //check for values outside the selected range and remove them by setting them to null
                    for (i=0; i<values[key]['data'].length; i++) {
                        if (values[key]['data'][i][0] < values[key]['min'] -5 || values[key]['data'][i][0] > parseInt(values[key]['max'], 10) + 5) {
                            //remove this
                            values[key]['data'].splice(i,1);
                            i--;
                        }
                    }

                } else {
                    //no zooming means that we need to specifically set the margins
                    //do the last one first to avoid getting the new last element
                    values[key]['data'].push([parseInt(values[key]['data'][values[key]['data'].length - 1][0], 10) + 5, -1]);
                    //now get the first element
                    values[key]['data'].push([values[key]['data'][0][0] - 5, -1]);
                }

                var plot = {key: YAHOO.widget.Flot(placeholder, [values[key]], options)};
                if (hasFilter) {
                    // mark pre-selected area
                    plot.key.setSelection({ xaxis: { from: values[key].min , to: values[key].max }});
                }
                // selection handler
                plot.key.subscribe('plotselected', function (ranges) {
                    from = Math.floor(ranges.xaxis.from);
                    to = Math.ceil(ranges.xaxis.to);
                    location.href=values[key]['removalURL'] + '&daterange[]=' + key + '&' + key + 'to=' + PadDigits(to,4) + '&' + key + 'from=' + PadDigits(from,4);
                });

                if (hasFilter) {
                    var newdiv = document.createElement('div');
                    var text = document.getElementById("clearButtonText").innerHTML;
                    newdiv.setAttribute('id', 'clearButton'+ key);
                    newdiv.innerHTML = '<a href="' + values[key]['removalURL'] + '">' + text + '</a>';
                    newdiv.className += "dateVisClear";
                    placeholder.appendChild(newdiv);
                }
            };
        }
    }
    }

    YAHOO.util.Connect.asyncRequest('GET', url, callback, null);
}

function PadDigits(n, totalDigits) 
{ 
    if (n <= 0){
        n= 1;
    }
    n = n.toString(); 
    var pd = ''; 
    if (totalDigits > n.length) 
    { 
        for (i=0; i < (totalDigits-n.length); i++) 
        { 
            pd += '0'; 
        } 
    } 
    return pd + n; 
}