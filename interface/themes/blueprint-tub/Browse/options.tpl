{if !empty($facets)}
<ul class="browse">
  {foreach from=$facets item=facet}
    <li>
      <a class="viewRecords" href="{$url}/Search/Results?lookfor=%22{$facet.0|escape:'url'}%22&amp;type={$facet_field|escape:'url'}&amp;filter[]={$query|escape:'url'}">{translate text='View Records'}</a>
      {if $next_query_field}
        <a title="{$query|escape} AND {$next_query_field|escape}:&quot;{$facet.0|escape}&quot;" href="" class="loadOptions facet_field:{$next_facet_field} target:{$next_target}">{$facet.0|escape} ({$facet.1})</a>
      {else}
        <a title="&quot;{$facet.0|escape}&quot;" href="{$url}/Search/Results?lookfor=%22{$facet.0|escape:'url'}%22&amp;type={$facet_field|escape:'url'}&amp;filter[]={$query|escape:'url'}">{$facet.0|escape} ({$facet.1})</a>
      {/if}
    </li>
  {/foreach}  
</ul>
{/if}