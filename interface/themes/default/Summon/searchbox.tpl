<div class="searchform">
  {if $searchType == 'SummonAdvanced'}
    <a href="{$path}/Summon/Advanced?edit={$searchId}" class="small">{translate text="Edit this Advanced Search"}</a> |
    <a href="{$path}/Summon/Advanced" class="small">{translate text="Start a new Advanced Search"}</a> |
    <a href="{$path}/Summon/Home" class="small">{translate text="Start a new Basic Search"}</a>
    <br>{translate text="Your search terms"} : "<b>{$lookfor|escape:"html"}</b>"
  {else}
    <form method="GET" action="{$path}/Summon/Search" name="searchForm" id="searchForm" class="search">
      <div class="hiddenLabel"><label for="lookfor">{translate text="Search For"}:</label></div>
      <input id="lookfor" type="text" name="lookfor" size="30" value="{$lookfor|escape:"html"}">
      <div class="hiddenLabel"><label for="type">{translate text="in"}:</label></div>
      <select id="type" name="type">
        {foreach from=$summonSearchTypes item=searchDesc key=searchVal}
          <option value="{$searchVal}"{if $searchIndex == $searchVal} selected{/if}>{translate text=$searchDesc}</option>
        {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text="Find"}">
      <a href="{$path}/Summon/Advanced" class="small">{translate text="Advanced"}</a>

      {* Do we have any checkbox filters? *}
      {assign var="hasCheckboxFilters" value="0"}
      {if isset($checkboxFilters) && count($checkboxFilters) > 0}
        {foreach from=$checkboxFilters item=current}
          {if $current.selected}
            {assign var="hasCheckboxFilters" value="1"}
          {/if}
        {/foreach}
      {/if}
      {if $filterList || $hasCheckboxFilters}
        <div class="keepFilters">
          <input id="retainAll" type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} onclick="filterAll(this);" />
          <label for="retainAll">{translate text="basic_search_keep_filters"}</label>
          <div style="display:none;">
            {foreach from=$filterList item=data key=field}
              {foreach from=$data item=value}
                <input type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} name="filter[]" value='{$value.field|escape}:&quot;{$value.value|escape}&quot;' />
              {/foreach}
            {/foreach}
            {foreach from=$checkboxFilters item=current}
              {if $current.selected}
                <input type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} name="filter[]" value="{$current.filter|escape}" />
              {/if}
            {/foreach}
          </div>
        </div>
      {/if}
      {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
    </form>
  {/if}
</div>