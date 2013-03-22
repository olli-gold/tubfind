<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>System Maintenance</h1>

  <h2>Utilities</h2>
  {if $status}<div class="warning">{$status}</div>{/if}
  <form method="get" action="{$url}/Admin/Maintenance">
    <input type="hidden" name="util" value="deleteExpiredSearches"/>
    <label for="del_daysOld">{translate text="Delete unsaved user search histories older than"}</label>
    <input id="del_daysOld" type="text" name="daysOld" size="5" value="2"/> {translate text="days"}.
    <input type="submit" name="submit" value="{translate text="Submit"}"/>
  </form>
</div>

<div class="clear"></div>
