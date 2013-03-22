<a href="{$url}/MyResearch/Home">{translate text='Your Account'}</a> <span>&gt;</span>
{if $pageTemplate == 'view-alt.tpl'}
<em>{$pageTitle}</em>
{else}
<em>{$pageTemplate|replace:'.tpl':''|capitalize|translate}</em>
{/if}
<span>&gt;</span>
<div style="text-align:right;margin-right:2em;">
{*.
{$recordCount}
{translate text="Showing"}
{translate text='query time'}: {$qtime}s
*}
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