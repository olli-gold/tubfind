<form action="{$url}/MyResearch/Export" method="post" name="bulkExport">
  {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
  {if $infoMsg}<div class="info">{$infoMsg|translate}</div>{/if}

  <div id="popupMessages"></div>
  <div id="popupDetails"> 
      {foreach from=$exportList item=favorite}
        <strong>{translate text='Title'}:</strong>
        {$favorite.title|escape}<br />
      {/foreach}
      <label for="format">{translate text="Format"}:</label>
      <select id="format" name="format">
        {foreach from=$exportOptions item=exportOption}
          <option value="{$exportOption|escape}">{translate text=$exportOption}</option>
        {/foreach}
      </select>
      <br />
      <input class="button" type="submit" name="submit" value="{translate text='Export'}" />
      {foreach from=$exportIDS item=exportID}
        <input type="hidden" name="ids[]" value="{$exportID|escape}" />
      {/foreach}
      {if $listID}
        <input type="hidden" name="listID" value="{$listID|escape}" />
      {/if}
      {if $followupModule}
      <input type="hidden" name="followupModule" value="{$followupModule|escape}" />
      {/if}
      {if $followupAction}
      <input type="hidden" name="followupAction" value="{$followupAction|escape}" />
      {/if}
  </div>
</form>