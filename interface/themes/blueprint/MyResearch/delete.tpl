<form action="{$url}/MyResearch/Delete" method="post" name="bulkDelete">
  {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
  {if $infoMsg}<div class="info">{$infoMsg|translate}</div>{/if}

  <div id="popupMessages"></div>
  <div id="popupDetails">
     {if !$listID}
      <div class="info">{translate text='fav_delete_warn'}</div>
    {else}
      <h2>{translate text="List"}: {$list->title|escape}</h2>
    {/if}
    
      {foreach from=$deleteList item=favorite}
        <strong>{translate text='Title'}:</strong>
        {$favorite.title|escape}<br />
      {/foreach}
      <br />
      <input class="submit" type="submit" name="submit" value="{translate text='Delete'}"/>
      {foreach from=$deleteIDS item=deleteID}
        <input type="hidden" name="ids[]" value="{$deleteID|escape}" />
      {/foreach}
      <input type="hidden" name="listID" value="{if $listID}{$listID|escape}{/if}" />
      {if $followupModule}
      <input type="hidden" name="followupModule" value="{$followupModule|escape}" />
      {/if}
      {if $followupAction}
      <input type="hidden" name="followupAction" value="{$followupAction|escape}" />
      {/if}
  </div>
</form>