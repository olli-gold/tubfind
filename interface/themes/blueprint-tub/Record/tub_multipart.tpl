{if !empty($multipartPart)}
<h2>{$multipartIS} {foreach from=$multipartPart item=mpPart name=loop}Band: {$mpPart}{/foreach}</h2>
{foreach from=$multipartLink item=mpLink name=loop}
<a href="{$url}/Record/{$mpLink}">Gesamtaufnahme</a>
{/foreach}
{/if}

{if !empty($multipartChildren)}
<ul>
{foreach from=$multipartChildren item=mpChild name=loop}
<li><a href="{$url}/Record/{$mpChild.id}">Band {$mpChild.part}: {$mpChild.title}</a></li>
{/foreach}
</ul>
{/if}

