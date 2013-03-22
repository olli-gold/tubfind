<div class="searchform">
  {if $searchType == 'AuthorityAdvanced'}
    <a href="{$path}/Authority/Advanced?edit={$searchId}" class="small">{translate text="Edit this Advanced Search"}</a> |
    <a href="{$path}/Authority/Advanced" class="small">{translate text="Start a new Advanced Search"}</a> |
    <a href="{$path}/Authority/Home" class="small">{translate text="Start a new Basic Search"}</a>
    <br/>{translate text="Your search terms"} : "<strong>{$lookfor|escape:"html"}</strong>"
  {else}
    <form method="get" action="{$path}/Authority/Search" name="searchForm" id="searchForm" class="search">
      <label for="searchForm_lookfor" class="offscreen">{translate text="Search Terms"}</label>
      <input id="searchForm_lookfor" type="text" name="lookfor" size="40" value="{$lookfor|escape}" {if $autocomplete}class="autocomplete typeSelector:searchForm_type"{/if}/>
      <label for="searchForm_type" class="offscreen">{translate text="Search Type"}</label>
      <select id="searchForm_type" name="type">
      {foreach from=$authSearchTypes item=searchDesc key=searchVal}
        <option value="{$searchVal}"{if $searchIndex == $searchVal} selected="selected"{/if}>{translate text=$searchDesc}</option>
      {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text="Find"}"/>
      {* Not yet supported: <a href="{$path}/Authority/Advanced" class="small">{translate text="Advanced"}</a> *}
      {if $filterList}
        <div class="keepFilters">
          <input type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} id="searchFormKeepFilters"/> 
          <label for="searchFormKeepFilters">{translate text="basic_search_keep_filters"}</label>
          <div class="offscreen">
            {foreach from=$filterList item=data key=field name=filterLoop}
              {foreach from=$data item=value}
                <input id="applied_filter_{$smarty.foreach.filterLoop.iteration}" type="checkbox" {if $retainFiltersByDefault}checked="checked" {/if} name="filter[]" value="{$value.field|escape}:&quot;{$value.value|escape}&quot;" />
                <label for="applied_filter_{$smarty.foreach.filterLoop.iteration}">{$value.field|escape}:&quot;{$value.value|escape}&quot;</label>
              {/foreach}
            {/foreach}
          </div>
        </div>
      {/if}
    </form>
    <script type="text/javascript">$("#searchForm_lookfor").focus()</script>
  {/if}
</div>
