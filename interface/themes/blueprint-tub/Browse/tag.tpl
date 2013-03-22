<div class="span-5 browseNav">
  {include file="Browse/top_list.tpl" currentAction="Tag"}
</div>

<div class="span-5 browseNav">
  <ul class="browse" id="list2">
    <li {if $findby == "alphabetical"} class="active"{/if}><a href="{$url}/Browse/Tag?findby=alphabetical">{translate text="By Alphabetical"}</a></li>
    <li {if $findby == "popularity"} class="active"{/if}><a href="{$url}/Browse/Tag?findby=popularity">{translate text="By Popularity"}</a></li>
    <li {if $findby == "recent"} class="active"{/if}><a href="{$url}/Browse/Tag?findby=recent">{translate text="By Recent"}</a></li>
  </ul>
</div>

{if !empty($alphabetList)}
<div class="span-5 browseNav">
  <ul class="browse" id="list3">
  {foreach from=$alphabetList item=letter}
    <li {if $startLetter == $letter}class="active" {/if}>
      <a href="{$url}/Browse/Tag?findby=alphabetical&amp;letter={$letter|escape:"url"}">{$letter|escape:"html"}</a>
    </li>
  {/foreach}
  </ul>
</div>
{/if}

{if !empty($tagList)}
<div class="span-5 browseNav">
  <ul class="browse" id="list4">
  {foreach from=$tagList item=tag}
    <li><a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape:"html"} ({$tag->cnt})</a></li>
  {/foreach}
  </ul>
</div>
{/if}

<div class="clear"></div>
