<div data-role="page" id="Record-view">
  {include file="header.tpl"}
  <div class="record" data-role="content" data-record-id="{$id}">
    {if $action == 'Home' || $action == 'Holdings'}
      {include file=$coreMetadata}
    {else}
      <h3>
        {if is_array($coreShortTitle)}
            {$coreShortTitle.0|escape}
        {else}
            {$coreShortTitle|escape}
        {/if}
        {if $coreSubtitle}
            {if is_array($coreSubtitle)}
                {$coreSubtitle.0|escape}
            {else}
                {$coreSubtitle|escape}
            {/if}
        {/if}
        {if $coreTitleSection}
            {if is_array($coreTitleSection)}
                {$coreTitleSection.0|escape}
            {else}
                {$coreTitleSection|escape}
            {/if}
        {/if}
      </h3>
    {/if}
    {include file="Record/$subTemplate"}
    {* Show the "Tag this" button only on Record/Home or Record/Holdings *} 
    {if $action == 'Home' || $action == 'Holdings'}
      <div data-role="controlgroup">
        <a href="{$path}/Record/{$id}/Save" data-role="button" rel="external">{translate text="Add to favorites"}</a>
        <a href="{$path}/Record/{$id}/AddTag" data-role="button" rel="external">{translate text="Add Tag"}</a>
      </div>
    {/if}
  </div>    
  {include file="footer.tpl"}
</div>
