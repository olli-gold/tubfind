{if $searchId}
<em>{translate text="Search"}: {$lookfor|capitalize|escape:"html"}</em>
{elseif $pageTemplate=="newitem.tpl" || $pageTemplate=="newitem-list.tpl"}
<em>{translate text="New Items"}</em>
{elseif $pageTemplate=="view-alt.tpl"}
<em>{translate text=$subTemplate|replace:'.tpl':''|capitalize|translate}</em>
{elseif $pageTemplate!=""}
<em>{translate text=$pageTemplate|replace:'.tpl':''|capitalize|translate}</em>
{/if}
{if $recordCount > 0}
<span>></span><em>{$recordCount} {translate text="Showing"}</em>
{/if}

{* geht nicht mit Sortoptions
<p style="text-align:center;">
{$recordCount}
{translate text="Showing"}
{translate text='query time'}: {$qtime}s
</p>
*}

<div style="text-align:right;margin-right:2em;"> 
<!-- <div class="floatright"> -->
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

