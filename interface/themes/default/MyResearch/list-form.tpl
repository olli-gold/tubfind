<div align="left">
{if $listError}<p class="error">{$listError|translate}</p>{/if}
<form method="post" action="{$url}/MyResearch/ListEdit" name="listForm"
      onSubmit='addList(this, &quot;{translate text='add_list_fail'}&quot;); return false;'>
  <label for="list_title">{translate text="List"}:</label><br>
  <input type="text" id="list_title" name="title" value="{$list->title|escape:"html"}" size="50"><br>
  <label for="list_desc">{translate text="Description"}:</label><br>
  <textarea id="list_desc" name="desc" rows="3" cols="50">{$list->desc|escape:"html"}</textarea><br>
  {translate text="Access"}:<br>
  <input type="radio" id="public1" name="public" value="1"> <label for="public1">{translate text="Public"}</label>
  <input type="radio" id="public0" name="public" value="0" checked> <label for="public0">{translate text="Private"}</label><br />
  <input type="submit" name="submit" value="{translate text="Save"}">
  <input type="hidden" name="recordId" value="{$recordId}">
  <input type="hidden" name="followupModule" value="{$followupModule}">
  <input type="hidden" name="followupAction" value="{$followupAction}">
  <input type="hidden" name="followupId" value="{$followupId}">
  <input type="hidden" name="followupText" value="{translate text='Add to favorites'}">
</form>
</div>
