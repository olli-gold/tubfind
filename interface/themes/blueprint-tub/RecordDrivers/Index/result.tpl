<div class="result recordId" id="record{$summId|escape}">
{*
  {if $bookBag}
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$summId|escape}" class="checkbox_ui"/>
  <input type="hidden" name="idsAll[]" value="{$summId|escape}" />
  {/if}
*}

  <div class="span-2">
  {if $summThumb}
    <img src="{$summThumb|escape}" class="summcover" alt="{translate text='Cover Image'}"/>
    {else}
    <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
  {/if}
  </div>
  <div class="span-9">

    <div class="resultItemLine1">
      <a href="{$url}/Record/{$summId|escape:"url"}" class="title">
      {if !empty($summHighlightedTitle)}
          {if is_array($summHighlightedTitle)}
              <b class="listtitle">{$summHighlightedTitle.0|addEllipsis:$summShortTitle|highlight}</b>
          {else}
              <b class="listtitle">{$summHighlightedTitle|addEllipsis:$summShortTitle|highlight}</b>
          {/if}
      {elseif !$summShortTitle}
          {translate text='Title not available'}
      {elseif is_array($summShortTitle)}
          <b class="listtitle">{$summShortTitle.0|truncate:90:"..."|escape}</b>
      {else}
          <b class="listtitle">{$summShortTitle|truncate:90:"..."|escape}</b>
      {/if}
      </a>
    </div>


    <div class="resultItemLine2">
      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Search/Results?lookfor={$summAuthor|escape:"url"}&type=Author&localonly=1">{$summAuthor|escape}</a>
      {/if}

      {if $summDate} {$summDate.0|escape}{/if}
    </div>

    {assign  var="showCall" value="false"}
    {foreach from=$summFormats item=format}
        {if $format!="Serial" && $format!="Electronic" && $format!="eBook"}
            {assign var="showCall" value="true"}
        {/if}
    {/foreach}

    <div class="span-14 last">
     {* {if !empty($summSnippetCaption)}<strong>{translate text=$summSnippetCaption}:</strong>{/if} *}
     {* {if !empty($summSnippet)}<span class="quotestart">&#8220;</span>...{$summSnippet|highlight}...<span class="quoteend">&#8221;</span><br/>{/if} *}
        {if $summAjaxStatus}
          {if $showCall == "true"}
          <span id="callnumber{$summId|escape}label">{translate text='Call Number'}: <span class="ajax_availability hide" id="callnumber{$summId|escape}">{translate text='Loading'}...</span><br/></span>
          {/if}
          <span id="location{$summId|escape}label">{translate text='Located'}: <span class="ajax_availability hide" id="location{$summId|escape}">{translate text='Loading'}...</span></span>
        {elseif !empty($summCallNo)}
          {translate text='Call Number'}: {$summCallNo|escape}
        {/if}
<!--
      {if $summOpenUrl || !empty($summURLs)}
        {if $summOpenUrl}
          <br/>
          {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
        {/if}
        {foreach from=$summURLs key=recordurl item=urldesc}
          <br/><a href="{$recordurl|escape}" class="fulltext" target="new">{$urldesc|escape}</a>
        {/foreach}
      {/if}
-->
      <br/>
      {foreach from=$summFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
      
      {*if !$summOpenUrl && empty($summURLs)*}
      {*if $showLoc=="true"*}
        <span class="ajax_availability hide" id="status{$summId|escape}">{translate text='Loading'}...</span>
      {*/if*}
      {*/if*}
    </div>

    {if $showPreviews}
      {if (!empty($summLCCN)|!empty($summISBN)|!empty($summOCLC))}
      <div class="span-3 last">
        {if $showGBSPreviews}      
          <div class="previewDiv"> 
            <a title="{translate text='Preview from'} Google Books" class="hide previewGBS{if $summISBN} ISBN{$summISBN}{/if}{if $summLCCN} LCCN{$summLCCN}{/if}{if $summOCLC} OCLC{$summOCLC|@implode:' OCLC'}{/if}" target="_blank">
              <img src="https://www.google.com/intl/en/googlebooks/images/gbs_preview_button1.png" alt="{translate text='Preview'}"/>
            </a>
          </div>
        {/if}
        {if $showOLPreviews}
          <div class="previewDiv">
            <a title="{translate text='Preview from'} Open Library" class="hide previewOL{if $summISBN} ISBN{$summISBN}{/if}{if $summLCCN} LCCN{$summLCCN}{/if}{if $summOCLC} OCLC{$summOCLC|@implode:' OCLC'}{/if}" target="_blank">
              <img src="{$path}/images/preview_ol.gif" alt="{translate text='Preview'}"/>
            </a>
          </div> 
        {/if}
        {if $showHTPreviews}
          <div class="previewDiv">
            <a title="{translate text='Preview from'} HathiTrust" class="hide previewHT{if $summISBN} ISBN{$summISBN}{/if}{if $summLCCN} LCCN{$summLCCN}{/if}{if $summOCLC} OCLC{$summOCLC|@implode:' OCLC'}{/if}" target="_blank">
              <img src="{$path}/images/preview_ht.gif" alt="{translate text='Preview'}"/>
            </a>
          </div> 
        {/if}
        <span class="previewBibkeys{if $summISBN} ISBN{$summISBN}{/if}{if $summLCCN} LCCN{$summLCCN}{/if}{if $summOCLC} OCLC{$summOCLC|@implode:' OCLC'}{/if}"></span>
      </div>
      {/if}
    {/if}     
  </div>

  <div class="span-4 last">
    <!--<a id="saveRecord{$summId|escape}" href="{$url}/Record/{$summId|escape:"url"}/Save" class="fav tool saveRecord" title="{translate text='Add to favorites'}">{translate text='Add to favorites'}</a>-->
    {* Display the lists that this record is saved to *}
    <div class="savedLists info hide" id="savedLists{$summId|escape}">
      <strong>{translate text="Saved in"}:</strong>
    </div>
    {if $bookBag}
      <div class="bookbagLightbox info hide" id="inbookbag{$summId|escape}">
        <strong>{translate text="in Book Bag"}</strong>
      </div>
      <a id="keepRecord{$summId|escape}" href="#" class="recordCart bookbagAdd offscreen" title="{translate text='Add to Book Bag'}" keepId="{$summId|escape}">{translate text='Add to Book Bag'}</a>
    {/if}
  </div>
  
  <div class="clear"></div>
</div>

{if $summCOinS}<span class="Z3988" title="{$summCOinS|escape}"></span>{/if}
