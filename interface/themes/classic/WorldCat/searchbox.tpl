<div class="searchbox">
  {if $searchType == 'WorldCatAdvanced'}
    <a href="{$path}/WorldCat/Advanced?edit={$searchId}" class="small">{translate text="Edit this Advanced Search"}</a> |
    <a href="{$path}/WorldCat/Advanced" class="small">{translate text="Start a new Advanced Search"}</a> |
    <a href="{$path}/WorldCat/Home" class="small">{translate text="Start a new Basic Search"}</a>
    <br>{translate text="Your search terms"} : "<b>{$lookfor|escape:"html"}</b>"
  {else}
    <form method="GET" action="{$path}/WorldCat/Search" name="searchForm" class="search">
      <div class="hiddenLabel"><label for="lookfor">{translate text="Search For"}:</label></div>
      <input type="text" id="lookfor" name="lookfor" size="30" value="{$lookfor|escape:"html"}">
      <div class="hiddenLabel"><label for="type">{translate text="in"}:</label></div>
      <select id="type" name="type">
        {foreach from=$worldCatSearchTypes item=searchDesc key=searchVal}
          <option value="{$searchVal}"{if $searchIndex == $searchVal} selected{/if}>{translate text=$searchDesc}</option>
        {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text="Find"}">
      <a href="{$path}/WorldCat/Advanced" class="small">{translate text="Advanced"}</a>
      {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
    </form>
  {/if}
</div>