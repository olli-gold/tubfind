<div class="span-18">
  {if $user->cat_username}
    <h3>{translate text='Change password'}</h3>

      {if $changeResult && $changeResult.success == false}
        <div class="error">{translate text=$changeResult.status}{if $changeResult.sysMessage} : {translate text=$changeResult.sysMessage|escape}{/if}</div>
      {/if}
      {if $changeResult && $changeResult.success == true}
        <div class="success">{translate text=$changeResult.status}</div>
      {/if}

    <form name="passwordForm" action="{$url|escape}/MyResearch/Password" method="post" id="changePassword">
      <input type="password" name="newsecret" size="12" maxlength="20" />
      <label class="preslabel">neues Passwort</label><br />
      <input type="password" name="newsecret2" size="12" maxlength="20" />
      <label class="preslabel"> Best&auml;tigung</label><br />
      <input type="submit" name="auth" value="Speichern" />
      <input type="reset"  value="Reset" />
    </form>      <!-- formende -->
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>

<div class="span-5 last">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>
