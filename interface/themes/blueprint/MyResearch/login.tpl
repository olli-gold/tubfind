<h2>{translate text='Login'}</h2>
{if $message}<div class="error">{$message|translate}</div>{/if}
{if $authMethod != 'Shibboleth'}
  <form method="post" action="{$url}/MyResearch/Home" name="loginForm" id="loginForm">
    <label class="span-2" for="login_username">{translate text='Username'}:</label>
    <input id="login_username" type="text" name="username" value="{$username|escape}" size="15" class="mainFocus {jquery_validation required='This field is required'}"/>
    <br class="clear"/>
    <label class="span-2" for="login_password">{translate text='Password'}:</label>
    <input id="login_password" type="password" name="password" size="15" class="{jquery_validation required='This field is required'}"/>
    <br class="clear"/>
    <input class="push-2 button" type="submit" name="submit" value="{translate text='Login'}"/>
    {if $followup}<input type="hidden" name="followup" value="{$followup}"/>{/if}
    {if $followupModule}<input type="hidden" name="followupModule" value="{$followupModule}"/>{/if}
    {if $followupAction}<input type="hidden" name="followupAction" value="{$followupAction}"/>{/if}
    {if $recordId}<input type="hidden" name="recordId" value="{$recordId|escape:"html"}"/>{/if}
    {if $extraParams}
      {foreach from=$extraParams item=item}
        <input type="hidden" name="extraParams[]" value="{$item.name|escape}|{$item.value|escape}" />
      {/foreach}
    {/if}
    <div class="clear"></div>
  </form>
  <script>
    {literal}
    $(document).ready(function() {
      $('#loginForm').validate();
    });
    {/literal}
  </script>
  {if $authMethod == 'DB'}<a class="new_account" href="{$url}/MyResearch/Account">{translate text='Create New Account'}</a>{/if}
{/if}
