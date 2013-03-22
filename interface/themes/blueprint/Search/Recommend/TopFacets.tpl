{if $topFacetSet}
  {foreach from=$topFacetSet item=cluster key=title}
  <div class="authorbox">
    <strong>{translate text=$cluster.label}</strong>{translate text="top_facet_suffix"}
    {foreach from=$cluster.list item=thisFacet name="narrowLoop"}
      {if $smarty.foreach.narrowLoop.iteration == ($topFacetSettings.rows * $topFacetSettings.cols) + 1}
        <br class="clear"/>
        <a id="more{$title}" href="#" onclick="moreFacets('{$title}'); return false;">{translate text='more'} ...</a>
        <div class="offscreen" id="narrowGroupHidden_{$title}">
          <br/>
          <strong>{translate text="top_facet_additional_prefix"}{translate text=$cluster.label}</strong>{translate text="top_facet_suffix"}
      {/if}
      {if $smarty.foreach.narrowLoop.iteration % $topFacetSettings.cols == 1}
        <br/>
      {/if}
      <span class="span-5">
      {if $thisFacet.isApplied}
        {$thisFacet.value|escape} <img src="{$path}/images/silk/tick.png" alt="Selected"/>
      {else}
        <a href="{$thisFacet.url|escape}">{$thisFacet.value|escape}</a> ({$thisFacet.count})
      {/if}
      </span>
      {if $smarty.foreach.narrowLoop.total > ($topFacetSettings.rows * $topFacetSettings.cols) && $smarty.foreach.narrowLoop.last}
          <br class="clear"/>
          <a href="#" onclick="lessFacets('{$title}'); return false;">{translate text='less'} ...</a>
        </div>
      {/if}
    {/foreach}
    <div class="clear"></div>
  </div>
  {/foreach}
{/if}
