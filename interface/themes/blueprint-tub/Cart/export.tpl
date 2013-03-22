{if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
{if $infoMsg}<div class="info">{$infoMsg|translate}</div>{/if}

{if empty($exportOptions)}
  <div class="error">{translate text="bulk_export_not_supported"}</div>
{else}
<form action="{$url}/Cart/Home?export" method="POST" name="exportForm" title="{translate text='Export Items'}">

  {foreach from=$exportList item=exportItem}
  <strong>{translate text='Title'}:</strong>
  {$exportItem.title|escape}<br />
  {/foreach}        
  
  <label for="format">{translate text='Format'}:</label>      
  <select name="format" id="format">
  {foreach from=$exportOptions item=exportOption}
    <option value="{$exportOption|escape}">{translate text=$exportOption}</option>
   {/foreach}
  </select>
  
  {foreach from=$exportIDS item=exportID}
    <input type="hidden" name="ids[]" value="{$exportID|escape:"URL"}" />
  {/foreach}
  
  {if $followupModule}
    <input type="hidden" name="followup" value="1" />
    <input type="hidden" name="followupModule" value="{$followupModule|escape}" />
  {/if}
  {if $followupAction}
    <input type="hidden" name="followupAction" value="{$followupAction|escape}" />
  {/if}
  <br />
  <input class="submit" type="submit" name="submit" value="{translate text='Export'}">

</form>
{/if}
