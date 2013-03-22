{if $expandFacetSet}
<ul class="pageitem">
{foreach from=$expandFacetSet item=cluster key=title}
  <li class="textbox"><span class="header">{translate text=$cluster.label}</span></li>
    {foreach from=$cluster.list item=thisFacet}
      <li class="menu"><a class="noeffect" href="{$thisFacet.expandUrl|escape}"><span class="name">{$thisFacet.value|escape}</span><span class="arrow"></span></a></li>
    {/foreach}
{/foreach}
</ul>
{/if}