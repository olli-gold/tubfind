{assign var=summId value=$id|escape}
{*if !empty($holdingURLs) || $holdingsOpenURL*}
  <h3>{translate text="Internet"}</h3>
  {if $doi}<a href="http://dx.doi.org/{$doi}" target="_new">http://dx.doi.org/{$doi}</a>{/if}

  {if !empty($pcURLs) && empty($doi)}
    {foreach from=$pcURLs item=pcurl}
      <a href="{$pcurl|escape}" class="fulltext" target="new">{$pcurl|escape}</a>
    {/foreach}
  {/if}

  <br/>

  {if $printed.status == "2"}
      <span id="printed{$summId|escape}">{translate text='Also available printed'}</span>
  {/if}
  {if $printed.status == "3"}
      <span id="printed{$summId|escape}">{translate text='Maybe also available printed'}</span>
  {/if}
  {if !empty($printed.signature)}
    <br/><span id="signatur{$summId|escape}label">{translate text='Call Number'}: {$printed.signature|escape}</span>
  {/if}
  {if $printed.location}
    <br/><span id="locationtub{$summId|escape}label">{translate text='Located'}: {$printed.location}</span>
  {/if}

  {*
  {if !empty($holdingURLs)}
    {foreach from=$holdingURLs item=desc key=url name=loop}
      {if $desc != "C"}
          <a href="{if $proxy}{$proxy}/login?url={$url|escape:"url"}{else}{$url|escape}{/if}">{$desc|escape}</a><br/>
      {/if}
    {/foreach}
  {/if}
  *}
  {if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}
  {/if}
{*/if*}
{if $sfxmenu && $sfxbutton && empty($pcURLs) && empty($doi)}
  <span class="hidden" id="sfxmenu{$id|escape}"><a href="{$sfxmenu}"><img src="{$sfxbutton}" alt="SFX" /></a></span>
{/if}
<br/>
{foreach from=$articleVol.docs item=artvol}
    {translate text="This article is printed in volume"}: <a href="{$url}/Record/{$artvol.id}">{$artvol.series2.0}</a><br/>
{/foreach}
{foreach from=$printedEbook.docs item=printbook}
    {translate text="This eBook is also available printed"}: <a href="{$url}/Record/{$printbook.id}">{$printbook.title.0}</a><br/>
{/foreach}
