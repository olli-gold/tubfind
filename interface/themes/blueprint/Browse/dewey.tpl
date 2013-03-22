<div id="list1container" class="span-5 browseNav">
  {include file="Browse/top_list.tpl" currentAction="Dewey"}
</div>

<div id="list2container" class="span-5 browseNav">
  <ul class="browse" id="list2">
  {foreach from=$defaultList item=area}
    <li><a href="" title="&quot;{$area.0|escape}&quot;" class="loadOptions query_field:dewey-hundreds facet_field:dewey-tens next_query_field:dewey-tens next_facet_field:dewey-ones target:list3container next_target:list4container">{$area.0|escape} ({$area.1})</a></li>
  {/foreach}  
  </ul>
</div>

<div id="list3container" class="span-5"></div>

<div id="list4container" class="span-5"></div>

<div class="clear"></div>
