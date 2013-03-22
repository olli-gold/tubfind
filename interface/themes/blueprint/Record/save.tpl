<h2>{translate text='add_favorite_prefix'} {$record.title|escape:"html"} {translate text='add_favorite_suffix'}</h2>
<form method="get" action="{$url}/Record/{$id|escape}/Save" name="saveRecord">
  <input type="hidden" name="submit" value="1" />
  <input type="hidden" name="id" value="{$id|escape}" />
  {if !empty($containingLists)}
  <p>{translate text='This item is already part of the following list/lists'}:</p>
  <ul>
  {foreach from=$containingLists item="list"}
    <li><a href="{$url}/MyResearch/MyList/{$list.id}">{$list.title|escape:"html"}</a></li>
  {/foreach}
  </ul>
  {/if}

{* Only display the list drop-down if the user has lists that do not contain
 this item OR if they have no lists at all and need to create a default list *}
{if (!empty($nonContainingLists) || (empty($containingLists) && empty($nonContainingLists))) }
  {assign var="showLists" value="true"}
{/if}

  {if $showLists}
    <label class="displayBlock" for="save_list">{translate text='Choose a List'}</label>
    <select id="save_list" name="list">
      {foreach from=$nonContainingLists item="list"}
        <option value="{$list.id}"{if $list.id==$lastListUsed} selected="selected"{/if}>{$list.title|escape:"html"}</option>
        {foreachelse}
        <option value="">{translate text='My Favorites'}</option>
      {/foreach}
    </select>
  {/if}
  <a href="{$url}/MyResearch/ListEdit?id={$id|escape:"url"}" class="listEdit" id="listEdit{$id|escape}" title="{translate text='Create a List'}">{translate text="or create a new list"}</a>
  
  {if $showLists}
    <label class="displayBlock" for="add_mytags">{translate text='Add Tags'}</label>
    <input class="mainFocus" id="add_mytags" type="text" name="mytags" value="" size="50"/>
    <p>{translate text='add_tag_note'}</p>
    <label class="displayBlock" for="add_notes">{translate text='Add a Note'}</label>
    <textarea id="add_notes" name="notes" rows="3" cols="50"></textarea>
    <br/>
    <input class="button" type="submit" value="{translate text='Save'}"/>
  {/if}
</form>
