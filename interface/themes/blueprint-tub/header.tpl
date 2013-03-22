<a id="logo" href="{$url}"></a>
<!-- Toplinks  --> 
&nbsp;
<b>Testsystem Vufind 1.3</b>
<a href="http://www.tub.tu-harburg.de" target="_blank">{translate text="Library Homepage"}</a> |
<a href="http://www.tub.tu-harburg.de/anmeldung" target="_blank">{translate text="Get a Library Card"}</a> |
<!--<a href="{$path}/MyResearch/Account">{translate text="Create Test Account"}</a> |-->
{if !$user}
  {if $authMethod == 'Shibboleth'}
    <a class="login" href="{$sessionInitiator}">{translate text="Institutional Login"}</a>
  {else}
    <a class="login" href="{$path}/MyResearch/Home">{translate text="Favorites"}</a>
  {/if}
{else}
  <a class="account" href="{$path}/MyResearch/Home">{translate text="Favorites"}</a> |
  <a class="logout" href="{$path}/MyResearch/Logout">{translate text="Log Out"}</a>
{/if}

<div id="headerRight">
<a href="http://www.tu-harburg.de"><img src="{$path}/interface/themes/blueprint-tub/images/TUHH-Logo_195x53.svg" width="195"  alt="TUHH-Home" title="TUHH-Home"></a>
</div>


<div class="clear"></div>

  {* wird ausgeblendet ueber nicht erfuellte if-Klausel ;-) *}
  {if $thisWillNeverBeShown}
  {*if is_array($allLangs) && count($allLangs) > 1*}
  <form method="post" name="langForm" action="" id="langForm">
    <!--<label for="langForm_mylang">{translate text="Language"}:</label>-->
    <select id="langForm_mylang" name="mylang" class="jumpMenu">
      {foreach from=$allLangs key=langCode item=langName}
        <option value="{$langCode}"{if $userLang == $langCode} selected="selected"{/if}>{translate text=$langName}</option>
      {/foreach}
    </select>
    <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
  </form>
  {/if}

<br/>

<div id="headerRight">
    <form method="post" name="langForm" action="" id="langForm">
    {foreach from=$allLangs key=langCode item=langName}
        <button name="mylang" value="{$langCode}" style="background: none; border: none;"><img src="{$path}/interface/themes/blueprint-tub/images/langcode_{$langCode}.gif" /></button>
    {/foreach}
    </form>
</div>

<br/>

