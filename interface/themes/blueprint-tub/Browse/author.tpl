<div id="list1container" class="span-5 browseNav">
  {include file="Browse/top_list.tpl" currentAction="Author"}
</div>

<div id="list2container" class="span-5 browseNav">
  <ul class="browse" id="list2">
    <li><a href="{$url}/Browse/Author" class="loadAlphabet query_field:authorStr facet_field:author-letter target:list3container">{translate text="By Alphabetical"}</a></li>
    {if $lccEnabled}<li><a href="{$url}/Browse/Author" class="loadSubjects query_field:authorStr facet_field:callnumber-first target:list3container">{translate text="By Call Number"}</a></li>{/if}
    {if $topicEnabled}<li><a href="{$url}/Browse/Author" class="loadSubjects query_field:authorStr facet_field:topic_facet target:list3container">{translate text="By Topic"}</a></li>{/if}
    {if $genreEnabled}<li><a href="{$url}/Browse/Author" class="loadSubjects query_field:authorStr facet_field:genre_facet target:list3container">{translate text="By Genre"}</a></li>{/if}
    {if $regionEnabled}<li><a href="{$url}/Browse/Author" class="loadSubjects query_field:authorStr facet_field:geographic_facet target:list3container">{translate text="By Region"}</a></li>{/if}
    {if $eraEnabled}<li><a href="{$url}/Browse/Author" class="loadSubjects query_field:authorStr facet_field:era_facet target:list3container">{translate text="By Era"}</a></li>{/if}
  </ul>
</div>

<div id="list3container" class="span-5">
</div>

<div id="list4container" class="span-5">
</div>

<div class="clear"></div>
