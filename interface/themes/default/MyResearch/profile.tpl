{if count($pickup) > 1}
  {assign var='showHomeLibForm' value=true}
{else}
  {assign var='showHomeLibForm' value=false}
{/if}
<div id="bd">
  <div id="yui-main" class="content">
    <div class="yui-b first">
    <b class="btop"><b></b></b>
    {if $user->cat_username}
      <div class="resulthead"><h3>{translate text='Your Profile'}</h3></div>
      <div class="page">
      {if $userMsg}
        <p class="userMsg">{translate text=$userMsg}</p>
      {/if}
      {if $showHomeLibForm}
      <form method="post" action="{$url}/MyResearch/Profile" id="profile_form">
      {/if}
      <table class="citation" width="100%">
        <tr><th style="width:100px;">{translate text='First Name'}:</th><td>{$profile.firstname|escape}</td></tr>
        <tr><th>{translate text='Last Name'}:</th><td>{$profile.lastname|escape}</td></tr>
        {if $showHomeLibForm}
        <tr><th><label for="home_library">{translate text="Preferred Library"}:</label></th>
          <td>
            {if count($pickup) > 1}
              {if $profile.home_library != ""}
                {assign var='selected' value=$profile.home_library}
              {else}
                {assign var='selected' value=$defaultPickUpLocation}
              {/if}
              <select id="home_library" name="home_library">
                {foreach from=$pickup item=lib name=loop}
                  <option value="{$lib.locationID|escape}" {if $selected == $lib.locationID}selected="selected"{/if}>{$lib.locationDisplay|escape}</option>
                {/foreach}
              </select>
            {else}
              {$pickup.0.locationDisplay}
            {/if}
          </td>
        </tr>
        {/if}
        <tr><th>{translate text='Address'} 1:</th><td>{$profile.address1|escape}</td></tr>
        <tr><th>{translate text='Address'} 2:</th><td>{$profile.address2|escape}</td></tr>
        <tr><th>{translate text='Zip'}:</th><td>{$profile.zip|escape}</td></tr>
        <tr><th>{translate text='Phone Number'}:</th><td>{$profile.phone|escape}</td></tr>
        <tr><th>{translate text='Group'}:</th><td>{$profile.group|escape}</td></tr>
      </table>
      {if $showHomeLibForm}
        <input type="submit" value="{translate text='Save Profile'}" />
        </form>
      {/if}
    {else}
      <div class="page">
      {include file="MyResearch/catalog-login.tpl"}
    {/if}</div>
    <b class="bbot"><b></b></b>
    </div>
  
</div>
  {include file="MyResearch/menu.tpl"}

</div>