{js filename="ajax_common.js"}
{js filename="search.js"}

{* Main Listing *}
<div id="bd">
  <div id="yui-main" class="content">
    <div class="yui-b first contentbox">

      {if $errorMsg || $infoMsg}
      <div class="messages">
      {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
      {if $infoMsg}<div class="userMsg">{$infoMsg|translate}</div>{/if}
      </div>
      {/if}

      {if !$recordCount}
        <div class="page">{translate text="course_reserves_empty_list"}</div>
      {else}
        {* Recommendations *}
        {if $topRecommendations}
          {foreach from=$topRecommendations item="recommendations"}
            {include file=$recommendations}
          {/foreach}
        {/if}

        {* Listing Options *}
        <div class="yui-gc resulthead">
          <div class="yui-u first">
            {translate text="Showing"}
            <b>{$recordStart}</b> - <b>{$recordEnd}</b>
            {translate text='of'} <b>{$recordCount}</b>
            {translate text='Reserves'}
            {if $instructor || $course}
            ({if $instructor}{translate text='Instructor'}: <strong>{$instructor|escape}</strong>{if $course}, {/if}{/if}
            {if $course}{translate text='Course'}: <strong>{$course|escape}</strong>{/if})
            {/if}
          </div>

          <div class="yui-u toggle">
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
            <label for="sort_options_1">{translate text='Sort'}</label>
            <select id="sort_options_1" name="sort" onChange="document.location.href = this.options[this.selectedIndex].value;">
            {foreach from=$sortList item=sortData key=sortLabel}
              <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected{/if}>{translate text=$sortData.desc}</option>
            {/foreach}
            </select>
          </div>

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
          <a href="{$url}/Search/Email" class="mail" onClick="getLightbox('Search', 'Email', null, null, '{translate text="Email this"}'); return false;">{translate text='Email this Search'}</a>
        </div>
      {/if}
    </div>
    {* End Main Listing *}
  </div>

  {* Narrow Search Options *}
  <div class="yui-b">
    {if $sideRecommendations}
      {foreach from=$sideRecommendations item="recommendations"}
        {include file=$recommendations}
      {/foreach}
    {/if}
  </div>
  {* End Narrow Search Options *}

</div>