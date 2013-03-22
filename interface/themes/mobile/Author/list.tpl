{if $recordCount}
  <span class="graytitle">
    {translate text="Showing"}
    <b>{$recordStart}</b> - <b>{$recordEnd}</b>
    {* total record count is not currently reliable due to Solr facet paging
       limitations -- for now, displaying it is disabled.
    {translate text='of'} <b>{$recordCount}</b>
     *}
    {translate text='for search'} <b>'{$lookfor|escape}'</b>
  </span>
{/if}

<ul class="pageitem autolist">
  {foreach from=$recordSet item=record name="recordLoop"}
    <li class="menu">
      <a class="noeffect" href="{$url}/Author/Home?author={$record.0|escape:"url"}">
        <span class="name">{$record.0|escape:"html"} ({$record.1})</span>
        <span class="arrow"></span>
      </a>
    </li>
  {/foreach}
  {if $pageLinks.all}<li class="autotext"><div class="pagination">{$pageLinks.all}</div></li>{/if}
</ul>
