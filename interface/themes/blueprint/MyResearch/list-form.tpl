{if $listError}<p class="error">{$listError|translate}</p>{/if}
<form method="post" action="{$url}/MyResearch/ListEdit" name="listEdit" id="listEdit">
  <label class="displayBlock" for="list_title">{translate text="List"}:</label>
  <input id="list_title" type="text" name="title" value="{$list->title|escape:"html"}" size="50" 
      class="mainFocus {jquery_validation required='This field is required'}"/>
  <label class="displayBlock" for="list_desc">{translate text="Description"}:</label>
  <textarea id="list_desc" name="desc" rows="3" cols="50">{$list->desc|escape:"html"}</textarea>
  <fieldset>
    <legend>{translate text="Access"}:</legend> 
    <input id="list_public_1" type="radio" name="public" value="1"/> <label for="list_public_1">{translate text="Public"}</label>
    <input id="list_public_0" type="radio" name="public" value="0" checked="checked"/> <label for="list_public_0">{translate text="Private"}</label> 
  </fieldset>
  <input class="button" type="submit" name="submit" value="{translate text="Save"}"/>
  <input type="hidden" name="recordId" value="{$recordId}"/>
  <input type="hidden" name="followupModule" value="{$followupModule}"/>
  <input type="hidden" name="followupAction" value="{$followupAction}"/>
  <input type="hidden" name="followupId" value="{$followupId}"/>
  <input type="hidden" name="followupText" value="{translate text='Add to favorites'}"/>
  {if $bulkIDs}
    {foreach from=$bulkIDs item="bulkID"}
      <input type="hidden" name="ids[]" value="{$bulkID}"/>
    {/foreach}
  {/if}
</form>
