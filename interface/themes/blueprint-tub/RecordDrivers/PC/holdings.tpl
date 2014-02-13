{assign var=summId value=$id|escape}
{if !empty($holdingURLs) || $holdingsOpenURL}
  <h3>{translate text="Internet"}</h3>
  {if $doi}<a href="http://dx.doi.org/{$doi}" target="_new">http://dx.doi.org/{$doi}</a>{/if}

  {if !empty($pcURLs) && empty($doi)}
    {foreach from=$pcURLs item=pcurl}
      <a href="{$pcurl|escape}" class="fulltext" target="new">{$pcurl|escape}</a>
    {/foreach}
  {/if}

  <br/>

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
{/if}
{if $sfxmenu && $sfxbutton && empty($pcURLs) && empty($doi)}
  <span class="hidden" id="sfxmenu{$id|escape}"><a href="{$sfxmenu}"><img src="{$sfxbutton}" alt="SFX" /></a></span>
{/if}