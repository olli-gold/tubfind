{if !empty($holdingURLs) || $holdingsOpenURL}
  <h3>{translate text="Internet"}</h3>
  {if !empty($holdingURLs)}
    {foreach from=$holdingURLs item=desc key=url name=loop}
      {if $desc != "C"}
          <a href="{if $proxy}{$proxy}/login?url={$url|escape:"url"}{else}{$url|escape}{/if}">{$desc|escape}</a><br/>
      {/if}
    {/foreach}
  {/if}
  {if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}
  {/if}
  <span id="sfxmenu{$id|escape}"><a href="{$sfxmenu}"><img src="{$sfxbutton}" alt="SFX" /></a></span>
{/if}
