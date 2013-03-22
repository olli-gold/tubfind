<div class="span-18">
  {if $user->cat_username}
    <h3>{translate text='Your Profile'}</h3>
    <span class="span-3"><strong>{translate text='First Name'}:</strong></span> {$profile.firstname|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Last Name'}:</strong></span> {$profile.lastname|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='E-Mail'}:</strong></span> {$profile.email|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Street'}:</strong></span> {$profile.address2|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Zip'}:</strong></span> {$profile.zip|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Expiration'}:</strong></span> {$profile.expiration|escape}<br class="clear"/>
    <span class="span-3"><strong>{translate text='Status'}:</strong></span> {translate text="status-"|cat:$profile.status|escape}<br class="clear"/>
    {if $profile.message}
        <span class="span-3"><strong>{translate text='Nachricht'}:</strong></span> {$profile.message|escape}<br class="clear"/>
    {/if}
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>

<div class="span-5 last">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>