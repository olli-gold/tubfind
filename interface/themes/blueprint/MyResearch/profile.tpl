{if count($pickup) > 1}
  {assign var='showHomeLibForm' value=true}
{else}
  {assign var='showHomeLibForm' value=false}
{/if}
<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  {if $user->cat_username}
    <h3>{translate text='Your Profile'}</h3>
    {if $userMsg}
      <p class="success">{translate text=$userMsg}</p>
    {/if}
    {if $showHomeLibForm}
      <form method="post" action="{$url}/MyResearch/Profile" id="profile_form">
    {/if}
    <span class="span-3"><strong>{translate text='First Name'}:</strong></span> {$profile.firstname|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Last Name'}:</strong></span> {$profile.lastname|escape}<br class="clear"/>
    {if $showHomeLibForm}
    <span class="span-3"><label for="home_library">{translate text="Preferred Library"}:</label></span>
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
      <br class="clear"/>
    {/if}
    <span class="span-3"><strong>{translate text='Address'} 1:</strong></span> {$profile.address1|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Address'} 2:</strong></span> {$profile.address2|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Zip'}:</strong></span> {$profile.zip|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Phone Number'}:</strong></span> {$profile.phone|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Group'}:</strong></span> {$profile.group|escape}<br class="clear"/>
    {if $showHomeLibForm}
      <input type="submit" value="{translate text='Save Profile'}" />
      </form>
    {/if}
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>

<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>