<script src="http://www.google.com/jsapi?key={$googleKey}" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
{literal}
    google.load("search", "1");

    function OnLoad() {
      // Create a search control
      var coreSearch = new GSearchControl();
      coreSearch.setLinkTarget(GSearch.LINK_TARGET_SELF);
      coreSearch.setResultSetSize(GSearch.LARGE_RESULTSET);

      // Define Web Search
      var siteSearch = new GwebSearch();
      siteSearch.setLinkTarget(GSearch.LINK_TARGET_SELF);
      siteSearch.setResultSetSize(GSearch.LARGE_RESULTSET);
      siteSearch.setUserDefinedLabel("Library Web");
      //siteSearch.setNoResultsString("Your search did not match any of the library web pages.");
{/literal}

      siteSearch.setSiteRestriction("{$domain}");
      {if $queryAddition}
        siteSearch.setQueryAddition("{$queryAddition|escape}");
      {/if}

{literal}
      // Define Web Search Options
      var options = new GsearcherOptions();
      options.setExpandMode(GSearchControl.EXPAND_MODE_OPEN);

      // Add Web Search
      coreSearch.addSearcher(siteSearch, options);

      // Define Output Options
      var drawOptions = new GdrawOptions();
      //drawOptions.setSearchFormRoot(document.getElementById('searchbar'));
      drawOptions.setSearchFormRoot('empty');

      // Tell the searcher to draw itself and tell it where to attach
      coreSearch.draw(document.getElementById("searchcontrol"), drawOptions);
{/literal}

      // Execute an inital search
      coreSearch.execute("{$lookfor|escape:"javascript"}");

{literal}
    }
{/literal}

    google.setOnLoadCallback(OnLoad);
//]]>
</script>


<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  <div id="searchcontrol">{translate text='Loading'}...</div>
</div>
   
<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
  {foreach from=$recommendations item=current}
    {include file=$current}
  {/foreach}
</div>

<div class="clear"></div>
