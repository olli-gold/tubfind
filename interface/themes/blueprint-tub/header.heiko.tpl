<a id="logo" href="{$url}"></a>
<!-- Toplinks  --> 
&nbsp;
<a href="http://www.tub.tu-harburg.de" target="_blank">{translate text="Library Homepage"}</a> |
<a href="http://www.tub.tu-harburg.de/anmeldung" target="_blank">{translate text="Get a Library Card"}</a> |
<a href="{$path}/MyResearch/Account">{translate text="Create New Account"}</a> |
<a href="{$path}/Search/History">{translate text='Search History'}</a>


<div id="headerRight">
<a href="http://www.tu-harburg.de"><img src="{$path}/interface/themes/blueprint-tub/images/tuhh-rz-logo.gif"  alt="TUHH-Home" title="TUHH-Home"></a>

  <div id="logoutOptions"{if !$user} class="hide"{/if}>
    <a class="account" href="{$path}/MyResearch/Home">{translate text="Your Account"}</a> |
    <a class="logout" href="{$path}/MyResearch/Logout">{translate text="Log Out"}</a>
  </div>

  <div id="loginOptions"{if $user} class="hide"{/if}>
  {if $authMethod == 'Shibboleth'}
    <a class="login" href="{$sessionInitiator}">{translate text="Institutional Login"}</a>
  {else}
    <a class="login" href="{$path}/MyResearch/Home">{translate text="Login"}</a>
  {/if}
  </div>

</div>


<div class="clear"></div>clear was here ;-)

  {if is_array($allLangs) && count($allLangs) > 1}
  <form method="post" name="langForm" action="" id="langForm">
    <label for="langForm_mylang">{translate text="Language"}:</label>
    <select id="langForm_mylang" name="mylang" class="jumpMenu">
      {foreach from=$allLangs key=langCode item=langName}
        <option value="{$langCode}"{if $userLang == $langCode} selected="selected"{/if}>{translate text=$langName}</option>
      {/foreach}
    </select>
    <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
  </form>
  {/if}



