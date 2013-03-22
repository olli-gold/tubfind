<div id="EuropeanaDeferredRecommend">
    <p>{translate text="Loading"}... <img src="{$path}/images/loading.gif" /></p>
    <script>
    var url = path + "/AJAX/Recommend?mod=EuropeanaResults&params=" +
    "{$deferredEuropeanaResultsParams|escape:"url"|escape:"javascript"}&lookfor="+
    "{$deferredEuropeanaResultsSearchString|escape:"url"|escape:"javascript"}";
        URLtoDOM(url, 'EuropeanaDeferredRecommend', '');
    </script>
</div>

