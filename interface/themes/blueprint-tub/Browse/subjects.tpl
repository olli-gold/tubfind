{if !empty($facets)}
<ul class="browse">
  {foreach from=$facets item=facet}
    <li>
      <a class="viewRecords" href="{$url}/Search/Results?lookfor=%22{$facet.0|escape:'url'}%22&amp;type={$facet_field|escape:'url'}">{translate text='View Records'}</a>
      <a href="" title="&quot;{$facet.0|escape}&quot;" class="loadOptions query_field:{$facet_field} facet_field:{$query_field} target:list4container">{$facet.0|escape} ({$facet.1})</a>
    </li>
  {/foreach}  
</ul>
{/if}
