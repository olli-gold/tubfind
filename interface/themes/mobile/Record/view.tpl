<ul class="pageitem">
  <li>{include file=$coreMetadata}</li>
  <li>{include file="Record/$subTemplate"}</li>
</ul>

<ul class="pageitem">
  <li class="textbox"><span class="header">{translate text="Similar Items"}</span></li>
  {if is_array($similarRecords)}
    {foreach from=$similarRecords item=similar}
      <li class="menu"><a class="noeffect" href="{$url}/Record/{$similar.id|escape:"url"}"><span class="name">{$similar.title|escape}</span><span class="arrow"></span></a></li>
    {/foreach}
  {else}
    <li>{translate text='Cannot find similar records'}</li>
  {/if}
</ul>
{if is_array($editions)}
  <ul class="pageitem">
    <li class="textbox"><span class="header">{translate text="Other Editions"}</span></li>
    {foreach from=$editions item=edition}
       <li class="menu">
         <a class="noeffect" href="{$url}/Record/{$edition.id|escape:"url"}"><span class="name">{$edition.title|escape}</span><span class="arrow"></span></a>
       </li>
    {/foreach}
  </ul>
{/if}
{if $showPreviews}
{if $showGBSPreviews} 
<script src="https://encrypted.google.com/books?jscmd=viewapi&amp;bibkeys={if $isbn}ISBN{$isbn}{/if}{if $holdingLCCN}{if $isbn},{/if}LCCN{$holdingLCCN}{/if}{if $holdingArrOCLC}{if $isbn|$holdingLCCN},{/if}{foreach from=$holdingArrOCLC item=holdingOCLC name=oclcLoop}OCLC{$holdingOCLC}{if !$smarty.foreach.oclcLoop.last},{/if}{/foreach}{/if}&amp;callback=ProcessGBSBookInfo" type="text/javascript"></script>
{/if}
{if $showOLPreviews}
<script src="http://openlibrary.org/api/books?bibkeys={if $isbn}ISBN{$isbn}{/if}{if $holdingLCCN}{if $isbn},{/if}LCCN{$holdingLCCN}{/if}{if $holdingArrOCLC}{if $isbn|$holdingLCCN},{/if}{foreach from=$holdingArrOCLC item=holdingOCLC name=oclcLoop}OCLC{$holdingOCLC}{if !$smarty.foreach.oclcLoop.last},{/if}{/foreach}{/if}&amp;callback=ProcessOLBookInfo" type="text/javascript"></script>
{/if}
{if $showHTPreviews}
<script src="http://catalog.hathitrust.org/api/volumes/brief/json/id:HT{$id|escape};{if $isbn}isbn:{$isbn}{/if}{if $holdingLCCN}{if $isbn};{/if}lccn:{$holdingLCCN}{/if}{if $holdingArrOCLC}{if $isbn || $holdingLCCN};{/if}{foreach from=$holdingArrOCLC item=holdingOCLC name=oclcLoop}oclc:{$holdingOCLC}{if !$smarty.foreach.oclcLoop.last};{/if}{/foreach}{/if}&amp;callback=ProcessHTBookInfo" type="text/javascript"></script>
{/if}
{/if}
