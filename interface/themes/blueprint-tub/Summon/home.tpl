<div class="searchHomeContent">    
  <div class="searchHomeForm">
    {include file="Summon/searchbox.tpl"}
  </div>
</div>

<div class="searchHomeBrowse">
  <h2 class="span-5">{translate text="home_browse"} {translate text='Format'}</h2>
  <h2 class="span-5">{translate text="home_browse"} {translate text='Language'}</h2> 
  <div class="clear"></div>
  <ul class="span-5">
    {foreach from=$formatList.counts item=format}
      <li><a href="{$path}/Summon/Search?type=all&amp;filter[]={$formatList.displayName|escape:"url"}:{$format.value|escape:"url"}">{$format.value|escape}</a></li>
    {/foreach}
  </ul>
  <ul class="span-5">
    {foreach from=$languageList.counts item=language}
      <li><a href="{$path}/Summon/Search?type=all&amp;filter[]={$languageList.displayName|escape:"url"}:{$language.value|escape:"url"}">{$language.value|escape}</a></li>
    {/foreach}
  </ul>
  <div class="clear"></div>
</div>
