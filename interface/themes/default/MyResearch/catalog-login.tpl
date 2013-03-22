<h4>{translate text='Library Catalog Profile'}</h4>
{if $loginError}
  <p class="error">{translate text=$loginError}</p>
{/if}
<p>{translate text='cat_establish_account'}</p>
<form method="post">
  {translate text='Library Catalog Username'}:<br>
  <input type="text" name="cat_username" value="" size="25"><br>
  {translate text='Library Catalog Password'}:<br>
  <input type="text" name="cat_password" value="" size="25"><br>
  <input type="submit" name="submit" value="{translate text="Save"}">

  {if $followup}
    <input type="hidden" name="followup" value="{$followup|escape}"/>
    {if $followupModule}<input type="hidden" name="followupModule" value="{$followupModule|escape}"/>{/if}
    {if $followupAction}<input type="hidden" name="followupAction" value="{$followupAction|escape}"/>{/if}
    {if $id}<input type="hidden" name="recordId" value="{$id|escape:"html"}"/>{/if}
  {/if}

</form>
