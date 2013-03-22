{if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
{if $infoMsg}<div class="info">{$infoMsg|translate}</div>{/if}

<form method="POST" action="{$url}/Cart/Home" name="bulkSave">

  <input type="hidden" name="saveCart" value="1" />
  {foreach from=$itemIDS item=formID}
  <input type="hidden" name="ids[]" value="{$formID|escape:"URL"}" />
  {/foreach}


  {foreach from=$itemList item=itemDetails}
  <label for="save_list{$itemDetails.id}">{translate text='Title'}</label>
  <span id="save_list{$itemDetails.id|escape}">{$itemDetails.title|escape}</span>
  <br />
  {/foreach}

  {if $lists}
  <label class="displayBlock" for="save_list">{translate text='Choose a List'}</label>
  <select id="save_list" name="list">
  {foreach from=$lists item=listDetails}
    <option value="{$listDetails->id}"{if $listDetails->id == $lastListUsed} selected="selected"{/if}>{$listDetails->title|escape:"html"}</option>
    {foreachelse}
    <option value="">{translate text='My Favorites'}</option>
  {/foreach}
  </select>
  {/if}

  <a href="{$url}/MyResearch/ListEdit?{$idURL|escape}" class="listEdit" id="listEdit" title="{translate text='Create new list'}">{translate text="or create a new list"}</a>

  {if $lists}
  <label class="displayBlock" for="add_mytags">{translate text='Add Tags'}</label>
  <input class="mainFocus" id="add_mytags" type="text" name="mytags" value="" size="50"/>
  <br/>
  <input name="submit" class="submit" type="submit" value="{translate text='Save'}">
  {/if}

</form>
