{* Main Listing *}
<div class="{if $tab == "all"}resultsetBooks{else}{if $tab == "primo"}resultsetArticles{else}span-18{/if}{/if}">
    {if $topRecommendations}
    {foreach from=$topRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
  
  {* Listing Options *}
  <div class="resulthead">
    <div class="floatleft">
      
      {if $spellingSuggestions}
      <div class="correction">
        {translate text='spell_suggest'}:
        {foreach from=$spellingSuggestions item=details key=term name=termLoop}
          <br/><img src="{$path}/images/silk/expand.png" alt="{translate text='spell_expand_alt'}"/> {$term|escape} &raquo; {foreach from=$details.suggestions item=data key=word name=suggestLoop}<a href="{$data.replace_url|escape}">{$word|escape}</a>{if !$smarty.foreach.suggestLoop.last}, {/if}{/foreach}
        {/foreach}
      </div>
      {/if}
      
    </div>

<!--
    <div class="floatright">
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
-->
    <div class="clear"></div>
  </div>

  
  {* End Listing Options *}

  {if $subpage}
    {include file=$subpage}
  {else}
    {$pageContent}
  {/if}

  {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
  
  {*include file="Search/Recommend/BremenDatabases.tpl"*}

  <div class="searchtools">
    <strong>{translate text='Search Tools'}:</strong>
    <a href="{$rssLink|escape}" class="feed">{translate text='Get RSS Feed'}</a>
    <a href="{$url}/Search/Email" class="mailSearch mail" id="mailSearch{$searchId|escape}" title="{translate text='Email this Search'}">{translate text='Email this Search'}</a>
    {if $savedSearch}<a href="{$url}/MyResearch/SaveSearch?delete={$searchId}" class="delete">{translate text='save_search_remove'}</a>{else}<a href="{$url}/MyResearch/SaveSearch?save={$searchId}" class="add">{translate text='save_search'}</a>{/if}
    <p style="text-align:center;">
    {translate text="Showing"}:{$recordCount}        {translate text='query time'}:{$qtime}s
    </p>
    
    
  </div>
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
