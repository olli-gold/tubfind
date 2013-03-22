{if $visFacets}

    {* load jQuery flot *}
    <!--[if IE]>{js filename="flot/excanvas.min.js"}<![endif]--> 
    {js filename="flot/jquery.flot.min.js"}
    {js filename="flot/jquery.flot.selection.min.js"}
    {js filename="pubdate_vis.js"}

    {foreach from=$visFacets item=facetRange key=facetField}
      <div class="authorbox">
        <strong>{translate text=$facetRange.label}</strong>
        {* space the flot visualisation *}     
        <div id="datevis{$facetField}x" style="margin:0 10px;width:auto;height:80px;cursor:crosshair;"></div>
        <div id="clearButtonText" style="display: none">{translate text="Clear"}</div>  
      </div>
    {/foreach}
    <script type="text/javascript">
      loadVis('{$facetFields|escape:'javascript'}', '{$searchParams|escape:'javascript'}', '{$url}', {$zooming});
    </script>

{/if}
