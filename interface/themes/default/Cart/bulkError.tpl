<div class="alignleft">
  {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
  {if $infoMsg}<div class="userMsg">{$infoMsg|translate}</div>{/if}

  <div id="popupMessages"></div>
  <div id="popupDetails">
    {if $detailedMsg}
      <div class="userMsg">{translate text=$detailedMsg}</div>
    {/if}
  </div>
</div>
