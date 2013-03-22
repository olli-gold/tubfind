<ul class="browse">
  {foreach from=$letters item=letter}
    <li>
      <a href="" title="{$letter|escape}*" class="loadOptions query_field:{$query_field} facet_field:{$facet_field} facet_prefix:{$letter|escape} target:list4container">{$letter|escape}</a>
    </li>
  {/foreach}  
</ul>
