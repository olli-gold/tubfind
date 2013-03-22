<div class="span-5 browseNav">
  {include file="Browse/top_list.tpl" currentAction="Era"}
</div>

<div class="span-5 browseNav">
  <ul class="browse" id="list2">
    <li><a href="{$url}/Browse/Era" class="loadAlphabet query_field:era_facet facet_field:era_facet include_numbers:true target:list3container">{translate text="By Alphabetical"}</a></li>
    {if $topicEnabled}<li><a href="{$url}/Browse/Era" class="loadSubjects query_field:era_facet facet_field:topic_facet target:list3container">{translate text="By Topic"}</a></li>{/if}
    {if $genreEnabled}<li><a href="{$url}/Browse/Era" class="loadSubjects query_field:era_facet facet_field:genre_facet target:list3container">{translate text="By Genre"}</a></li>{/if}
    {if $regionEnabled}<li><a href="{$url}/Browse/Era" class="loadSubjects query_field:era_facet facet_field:geographic_facet target:list3container">{translate text="By Region"}</a></li>{/if}
  </ul>
</div>

<div id="list3container" class="span-5">
</div>

<div id="list4container" class="span-5">
</div>

<div class="clear"></div>
