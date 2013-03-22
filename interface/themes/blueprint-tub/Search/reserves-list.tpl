{* Main Listing *}
<div class="span-18">
  {if !$recordCount}
    <p>{translate text="course_reserves_empty_list"}</p>
  {else}
  {* Recommendations *}
  {if $topRecommendations}
    {foreach from=$topRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}

  {* Listing Options *}
  <div class="resulthead">
    <div class="span-12">
      {translate text="Showing"}
      <strong>{$recordStart}</strong> - <strong>{$recordEnd}</strong>
      {translate text='of'} <strong>{$recordCount}</strong>
      {translate text='Reserves'}
    </div>
  
    <div class="span-5 last">
      <form action="{$path}/Search/SortResults" method="post">
        {translate text='Sort'}
        <select name="sort" class="jumpMenu">
          {foreach from=$sortList item=sortData key=sortLabel}
            <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected="selected"{/if}>{translate text=$sortData.desc}</option>
          {/foreach}
        </select>
        <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
      </form>
    </div>
    <div class="clear"></div>
  </div>
  {* End Listing Options *}
  
  {if $subpage}
    {include file=$subpage}
  {else}
    {$pageContent}
  {/if}
  
  {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
    <div class="searchtools">
      <strong>{translate text='Search Tools'}:</strong>
      <a href="{$url}/Search/{$action}?lookfor={$lookfor|escape}&amp;type={$type}&amp;view=rss" class="feed">{translate text='Get RSS Feed'}</a>
      <a href="{$url}/Search/Email" class="mailSearch mail" title="{translate text='Email this Search'}">{translate text='Email this Search'}</a>
    </div>
  {/if}
</div>      
{* End Main Listing *}

{* Narrow Search Options *}
<div class="span-5 last">
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
</div>
{* End Narrow Search Options *}

<div class="clear"></div>