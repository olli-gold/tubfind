  
  {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
  {if $infoMsg}<div class="success">{$infoMsg|translate}</div>{/if}
  
  {if $showExport} <div class="success"><a class="save" target="_new" href="{$url}/Cart/Export?exportInit">{translate text="export_save"}</a></div>{/if}
