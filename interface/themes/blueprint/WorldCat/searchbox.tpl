<div class="searchform">
  {if $searchType == 'WorldCatAdvanced'}
    <a href="{$path}/WorldCat/Advanced?edit={$searchId}" class="small">{translate text="Edit this Advanced Search"}</a> |
    <a href="{$path}/WorldCat/Advanced" class="small">{translate text="Start a new Advanced Search"}</a> |
    <a href="{$path}/WorldCat/Home" class="small">{translate text="Start a new Basic Search"}</a>
    <br/>{translate text="Your search terms"} : "<strong>{$lookfor|escape:"html"}</strong>"
  {else}
    <form method="get" action="{$path}/WorldCat/Search" name="searchForm" class="search">
      <label for="searchForm_lookfor" class="offscreen">{translate text="Search Terms"}</label>
      <input id="searchForm_lookfor" type="text" name="lookfor" size="40" value="{$lookfor|escape:"html"}"/>
      <label for="searchForm_type" class="offscreen">{translate text="Search Type"}</label>
      <select id="searchForm_type" name="type">
        {foreach from=$worldCatSearchTypes item=searchDesc key=searchVal}
          <option value="{$searchVal}"{if $searchIndex == $searchVal} selected="selected"{/if}>{translate text=$searchDesc}</option>
        {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text="Find"}"/>
      <a href="{$path}/WorldCat/Advanced" class="small">{translate text="Advanced"}</a>
      {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
    </form>
  {/if}
</div>