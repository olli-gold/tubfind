{if $lastsearch}
  <div id="leftnavbody"><a href="{$lastsearch|escape}" class="backtosearch">{translate text="Back to Search Results"}</a></div><br /><br />
{/if}

{if $info}
<ul class="pageitem">
  <li class="textbox"><span class="header">{$info.name|escape}</span></li>
  <li style="padding: 0 0 10px 10px;">
{if $info.image}
<img src="{$info.image}" alt="{$info.altimage|escape}" style="width:80px;float:left; padding-right:5px;"/>
{/if}
{$info.description|truncate_html:1000:"...":false}
  <br style="clear:all;"/><a href="http://{$wiki_lang}.wikipedia.org/wiki/{$info.name|escape:"url"}" target="new"><span class="note">{translate text='wiki_link'}</span></a>
  </li>
</ul>
{/if}

<span class="graytitle">
  {translate text="Showing"}
  <b>{$recordStart}</b> - <b>{$recordEnd}</b>
  {translate text='of'} <b>{$recordCount}</b>
  {translate text='for search'}: <b>'{$authorName|escape:"html"}'</b>
</span>
<ul class="pageitem autolist">
{include file="Search/list-list.tpl"}

{if $pageLinks.all}
<li class="autotext"><div class="pagination">{$pageLinks.all}</div></li>
{/if}
</ul>

{* Recommendations *}
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
{* End Recommendations *}

