<div class="searchform">
  {if $searchType == 'advanced'}
    <a href="{$path}/Search/Advanced?edit={$searchId}" class="small">{translate text="Edit this Advanced Search"}</a> |
    <a href="{$path}/Search/Advanced" class="small">{translate text="Start a new Advanced Search"}</a> |
    <a href="{$path}/" class="small">{translate text="Start a new Basic Search"}</a>
    <br/>{translate text="Your search terms"} : "<strong>{$lookfor|escape:"html"}</strong>"
  {else}
    <form method="get" action="{$path}/Search/Results" name="searchForm" id="searchForm" class="search">
      <label for="searchForm_lookfor" class="offscreen">{translate text="Search Terms"}</label>
      <input id="searchForm_lookfor" type="text" name="lookfor" size="40" value="{$lookfor|escape}" {if $autocomplete}class="autocomplete typeSelector:searchForm_type"{/if}/>
      <label for="searchForm_type" class="offscreen">{translate text="Search Type"}</label>
      <select id="searchForm_type" name="type">
      {foreach from=$basicSearchTypes item=searchDesc key=searchVal}
        <option value="{$searchVal}"{if $searchIndex == $searchVal} selected="selected"{/if}>{translate text=$searchDesc}</option>
      {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text="Find"}"/>
      <a href="{$path}/Search/Advanced" class="small">{translate text="Advanced"}</a>

      {* Do we have any checkbox filters? *}
      {assign var="hasCheckboxFilters" value="0"}
      {if isset($checkboxFilters) && count($checkboxFilters) > 0}
        {foreach from=$checkboxFilters item=current}
          {if $current.selected}
            {assign var="hasCheckboxFilters" value="1"}
          {/if}
        {/foreach}
      {/if}
      {if $shards}
        <br />
        {foreach from=$shards key=shard item=isSelected}
          <input type="checkbox" {if $isSelected}checked="checked" {/if}name="shard[]" value='{$shard|escape}' /> {$shard|translate}
        {/foreach}
      {/if}
      {if ($filterList || $hasCheckboxFilters) && !$disableKeepFilterControl}
        <div class="keepFilters">
          <input type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} id="searchFormKeepFilters"/> <label for="searchFormKeepFilters">{translate text="basic_search_keep_filters"}</label>
          <div class="offscreen">
            {foreach from=$filterList item=data key=field name=filterLoop}
              {foreach from=$data item=value}
                <input id="applied_filter_{$smarty.foreach.filterLoop.iteration}" type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} name="filter[]" value="{$value.field|escape}:&quot;{$value.value|escape}&quot;" />
                <label for="applied_filter_{$smarty.foreach.filterLoop.iteration}">{$value.field|escape}:&quot;{$value.value|escape}&quot;</label>
              {/foreach}
            {/foreach}
            {foreach from=$checkboxFilters item=current name=filterLoop}
              {if $current.selected}
                <input id="applied_checkbox_filter_{$smarty.foreach.filterLoop.iteration}" type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} name="filter[]" value="{$current.filter|escape}" />
                <label for="applied_checkbox_filter_{$smarty.foreach.filterLoop.iteration}">{$current.filter|escape}</label>
              {/if}
            {/foreach}
          </div>
        </div>
      {/if}
      {* Load hidden limit preference from Session *}
      {if $lastLimit}<input type="hidden" name="limit" value="{$lastLimit|escape}" />{/if}
      {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
    </form>
    <script type="text/javascript">$("#searchForm_lookfor").focus()</script>
  {/if}
</div>
