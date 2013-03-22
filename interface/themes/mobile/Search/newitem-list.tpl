{if !$recordCount}
  <ul class="pageitem">
    <li>{translate text="No new item information is currently available."}</li>
  </ul>
{else}
  {* Display just like regular search results: *}
  {if !$subpage}
    {assign var="subpage" value="Search/list-list.tpl"}
  {/if}
  {include file="Search/list.tpl"}
{/if}
