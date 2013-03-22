<div class="searchHomeContent">
  <h1 style="text-align:center;">
  Universitätsbibliothek der TU Hamburg-Harburg<br/><br/>
  <img src="{$url}/interface/themes/blueprint-tub/images/tubfind_logo.jpg" alt="TUBfind - Suchportal der Universitätsbibliothek der TU Hamburg-Harburg"/>
  </h1>
  <div class="searchHomeForm">
    {include file="Search/searchbox.tpl"}
  </div>
</div>

<!--
{if $facetList}
<div class="searchHomeBrowse">
  {foreach from=$facetList item=details key=field}
    {assign var=list value=$details.sortedList}
    <h2 class="{if $field == 'callnumber-first' || $field == 'dewey-hundreds'}span-10{else}span-5{/if}">{translate text="home_browse"} {translate text=$details.label}</h2> 
  {/foreach}
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
-->