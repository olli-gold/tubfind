{assign var=summId value=$id|escape}

<h3>{translate text="Internet"}</h3>

{if $doi}<a href="http://dx.doi.org/{$doi}" target="_new">http://dx.doi.org/{$doi}</a>{/if}

{if !empty($pcURLs) && empty($doi)}
    {foreach from=$pcURLs item=pcurl}
        <a href="{$pcurl|escape}" class="fulltext" target="new">{$pcurl|escape}</a>
    {/foreach}
{/if}

<br/>

{if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}<br/>
{/if}

{if $sfxmenu && $sfxbutton && empty($pcURLs) && empty($doi)}
  <br/><span class="hidden" id="sfxmenu{$id|escape}"><a href="{$sfxmenu}"><img src="{$sfxbutton}" alt="SFX" /></a></span><br/>
{/if}

<br/>

{assign var=printedS value=0}

{foreach from=$articleVol.docs item=artvol}
    {if in_array('Journal', $artvol.format)} {translate text="This article is printed in journal"}
    {else}
    {translate text="This article is printed in volume"}
    {/if}
    : <a href="{$url}/Record/{$artvol.id}">
    {if $coreEdition}
        {$coreEdition}
    {else}
        {$artvol.series2}
    {/if}
    </a>
    {assign var=printedS value=1}
    {if in_array('Journal', $artvol.format)} {translate text="no_volume_ref_given"}{/if}
    <br/>
{/foreach}
{foreach from=$printedEbook.docs item=printbook}
    {translate text="This eBook is also available printed"}: <a href="{$url}/Record/{$printbook.id}">
    {if $printbook.title.0}
        {$printbook.title.0} {$printbook.publishDate.0}
    {else}
        {translate text="Title not found"}
    {/if}
    </a><br/>
    {assign var=printedS value=1}
{/foreach}

{if $printedS == 0}
    {if $printed.status == "2"}
        <span id="printed{$summId|escape}">{translate text='Also available printed'}</span>
    {/if}
    {if $printed.status == "3"}
        <span id="printed{$summId|escape}">{translate text='Maybe also available printed'}</span>
    {/if}
    {if !empty($printed.signature)}
        <br/><span id="signatur{$summId|escape}label">{translate text='Call Number'}: {$printed.signature|escape}</span>
    {/if}
{/if}
{if $gbvppn}
    <br/><a href="{$url}/Record/{$gbvppn|escape:"url"}?shard[]=GBV Central&shard[]=wwwtub&shard[]=tubdok&refer=pc" class="title">{if $locally}{translate text="This record at TUHH"}{else}{translate text="This record in the GBV"}{/if}</a>
{/if}