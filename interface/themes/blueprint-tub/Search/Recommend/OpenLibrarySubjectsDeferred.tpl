<div id="openLibraryDeferredRecommend">
    <p>{translate text="Loading"}... <img src="{$path}/interface/themes/blueprint/images/ajax_loading.gif" /></p>
    <script>
    var url = path + "/AJAX/Recommend?mod=OpenLibrarySubjects&params=" +
        "{$deferredOLSubjectsParams|escape:"url"|escape:"javascript"}" +
        "&" + "{$deferredOLSubjectsSearchParam|escape:"url"|escape:"javascript"}" + "=" +
        "{$deferredOLSubjectsSearchString|escape:"url"|escape:"javascript"}" + "&type=" +
        "{$deferredOLSubjectsSearchType|escape:"url"|escape:"javascript"}";
    $('#openLibraryDeferredRecommend').load(url);
    </script>
</div>