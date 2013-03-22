<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  {if $info}
    <div class="authorbio">
      <h2>{$info.name|escape}</h2>

      {if $info.image}
        <img src="{$info.image}" alt="{$info.altimage|escape}" width="150px" class="alignleft recordcover"/>
      {/if}
      
      {$info.description|truncate_html:4500:"...":false}

      <div class="providerLink"><a class="wikipedia" href="http://{$wiki_lang}.wikipedia.org/wiki/{$info.name|escape:"url"}" target="new">{translate text='wiki_link'}</a></div>

      <div class="clear"></div>  
    </div>
  {/if}

  {if $topRecommendations}
    {foreach from=$topRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}

  {* Listing Options *}
  <div class="resulthead">
    <div class="floatleft">
      {if $recordCount}
        {translate text="Showing"}
        <strong>{$recordStart}</strong> - <strong>{$recordEnd}</strong>
        {translate text='of'} <strong>{$recordCount}</strong>
        {translate text='for search'}: <strong>'{$authorName|escape:"html"}'</strong>,
      {/if}
      {translate text='query time'}: {$qtime}s

    </div>

    <div class="floatright">
      <div class="viewButtons">
      {if $viewList|@count gt 1}
        {foreach from=$viewList item=viewData key=viewLabel}
          {if !$viewData.selected}<a href="{$viewData.viewUrl|escape}" title="{translate text='Switch view to'} {translate text=$viewData.desc}" >{/if}<img src="{$path}/images/view_{$viewData.viewType}.png" {if $viewData.selected}title="{translate text=$viewData.desc} {translate text='view already selected'}"{/if}/>{if !$viewData.selected}</a>{/if}
        {/foreach}
      {/if}
      </div>
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
      </div>
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
</div>
  
{* Recommendations *}
<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
</div>
{* End Recommendations *}

<div class="clear"></div>
