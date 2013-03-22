<div class="searchHomeContent">    
  <div class="searchHomeForm">
    {include file="Search/searchbox.tpl"}
  </div>
</div>

{if $facetList}
<div class="searchHomeBrowse">
  {assign var=columns value=0}
  {foreach from=$facetList item=details key=field}
    {assign var=list value=$details.sortedList}
    {if $field == 'callnumber-first'}{assign var=currentSize value=10}{else}{assign var=currentSize value=5}{/if}
    {assign var=columns value=$columns+$currentSize}
    <h2 class="span-{$currentSize}">{translate text="home_browse"} {translate text=$details.label}</h2> 
  {/foreach}
  {if $columns > 0 && $columns < 24}
    <div class="span-{math equation="24 - x" x=$columns} last"><!-- pad out header row --></div>
  {/if}
  {foreach from=$facetList item=details key=field}
    {assign var=list value=$details.sortedList}
    <ul class="span-5">
      {* Special case: two columns for LC call numbers... *}
      {if $field == "callnumber-first"}
        {foreach from=$list item=url key=value name="callLoop"}
          <li><a href="{$url|escape}">{$value|escape}</a></li>
          {if $smarty.foreach.callLoop.iteration == 10}
            </ul>
            <ul class="span-5">
          {/if}
        {/foreach}
      {else}
        {assign var=break value=false}
        {foreach from=$list item=url key=value name="listLoop"}
          {if $smarty.foreach.listLoop.iteration > 12}
            {if !$break}
              <li><a href="{$path}/Search/Advanced"><strong>{translate text="More options"}...</strong></a></li>
              {assign var=break value=true}
            {/if}
          {else}
            <li><a href="{$url|escape}">{$value|escape}</a></li>
          {/if}
        {/foreach}
      {/if}
    </ul>
  {/foreach}
  <div class="clear"></div>
</div>
{/if}