{* Main Listing *}
<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  {if $errorMsg || $infoMsg}
    <div class="messages">
      {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
      {if $infoMsg}<div class="info">{$infoMsg|translate}</div>{/if}
    </div>
  {/if}
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
      {if $instructor || $course}
      ({if $instructor}{translate text='Instructor'}: <strong>{$instructor|escape}</strong>{if $course}, {/if}{/if}
      {if $course}{translate text='Course'}: <strong>{$course|escape}</strong>{/if})
      {/if}
    </div>

    <div class="span-5 last">
      <div class="limitSelect">
        {if $limitList|@count gt 1}
          <form action="{$path}/Search/LimitResults" method="post">
            <label for="limit">{translate text='Results per page'}</label>
            <select id="limit" name="limit" onChange="document.location.href = this.options[this.selectedIndex].value;">
              {foreach from=$limitList item=limitData key=limitLabel}
                <option value="{$limitData.limitUrl|escape}"{if $limitData.selected} selected="selected"{/if}>{$limitData.desc|escape}</option>
              {/foreach}
            </select>
            <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
          </form>
        {/if}
        <form action="{$path}/Search/SortResults" method="post">
          <label for="sort_options_1">{translate text='Sort'}</label>
          <select id="sort_options_1" name="sort" class="jumpMenu">
            {foreach from=$sortList item=sortData key=sortLabel}
              <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected="selected"{/if}>{translate text=$sortData.desc}</option>
            {/foreach}
          </select>
          <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
        </form>
      </div>
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
      <a href="{$rssLink|escape}" class="feed">{translate text='Get RSS Feed'}</a>
      <a href="{$url}/Search/Email" class="mailSearch mail" title="{translate text='Email this Search'}">{translate text='Email this Search'}</a>
    </div>
  {/if}
</div>
{* End Main Listing *}

{* Narrow Search Options *}
<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
</div>
{* End Narrow Search Options *}

<div class="clear"></div>