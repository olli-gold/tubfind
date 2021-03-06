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
      <a href="{$url}/Record/{$summId|escape:"url"}" class="title" title="{if !empty($summTitle)}{$summTitle|escape}{/if}">
      <b class="listtitle">
      {if !empty($summTitle)}
          {$summTitle|truncate:100:"..."|escape}
      {else}
          {translate text="No title"}
      {/if}
      </b>
      {if !empty($summAddTitle)}
          {$summAddTitle}
      {/if}
      {if !empty($summSeries)}
          {foreach from=$summSeries item=ser}
              ({$ser.name} {$ser.number})
          {/foreach}
      {/if}
      </a>
    </div>

    <div class="resultItemLine2">
      {if !empty($volumename)}
        {translate text='Volume title'}: 
          {foreach from=$volumename item=field name=loop}
            {$field|escape} 
          {/foreach}
        <br/>
      {/if}

      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Search/Results?lookfor={$summAuthor|escape:"url"}&type=Author&localonly=1">{$summAuthor|escape}</a>
      {/if}
      {if $summDateSpan} {translate text='erschienen von'}: {$summDateSpan|escape}
      {else}
      {if $summDate} {$summDate.0|escape}{/if}
      {/if}
      {if $summArticleRef}
        <br/>
        {$summArticleRef}
      {/if}
    </div>

    <div class="span-14 last">
        {assign var="electronicResource" value="0"}
        {assign  var="showAvail" value="true"}
        {assign var="showCallNumber" value="1"}
        {foreach from=$summFormats item=format}  {*$format=="eBook" ||*}
            {if $format=="Serial" || $format=="Journal" || $format=="Electronic" || $format=="Aufsätze" || $format=="eBook" || $format=="Elektronische Aufsätze"}
                {*assign var="showAvail" value="false"*}
            {/if}
            {if $format=="Electronic" || $format=="eBook" || $format=="Elektronische Aufsätze" || $format=="Elektronische Ressource" || $format=="electronic Article" }
                {assign var="summInterlibraryLoan" value="0"}
                {assign var="electronicResource" value="1"}
            {/if}
            {if $format=="Elektronische Aufsätze"}
                {assign var="showAvail" value="false"}
                {assign var="showAllLinks" value="1"}
            {/if}
            {if $format=="Elektronische Ressource"}
                {assign var="showCallNumber" value="0"}
            {/if}
            {if $format=="Book"}
                {assign var="showAcqProp" value="1"}
            {/if}
        {/foreach}
    {if $showAvail=="true" && $summInterlibraryLoan==0}
      {if $summAjaxStatus}
        {if $showCallNumber == "1"}
            <span id="callnumber{$summId|escape}label">{translate text='Call Number'}:</span> <span class="ajax_availability hide" id="callnumber{$summId|escape}">{translate text='Loading'}...</span><br/>
        {/if}
        <span id="location{$summId|escape}label">{translate text='Located'}:</span> <span class="ajax_availability hide" id="location{$summId|escape}">{translate text='Loading'}...</span>
      {elseif !empty($summCallNo)}
        <span id="callnumber{$summId|escape}label">{translate text='Call Number'}: {$summCallNo|escape}</span>
      {/if}
    {/if}

      {if $nlurls}
          {*<br/>{translate text="Available via German National license."}*}
          {foreach from=$nlurls key=recordurl item=urldesc}
              <br/>{translate text="NL"}: <a href="{$recordurl}">{$urldesc}</a>
          {/foreach}
      {/if}
      {if $summOpenUrl}
          <br/>
          {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
      {/if}

      {if !empty($summURLs) && $electronicResource == "1" && $showAllLinks == "1" && !$nlurls && $showCallNumber == "1"}
        {foreach from=$summURLs key=recordurl item=urldesc}
          <br/><a href="{$recordurl|escape}" class="fulltext" target="new">
          {*{if $recordurl == $urldesc}{translate text='Get full text'}{else}*}
          {$urldesc|escape}
          {*/if*}
          </a>
        {/foreach}
      {/if}

      <br/>
      {foreach from=$summFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}

      {*if !$summOpenUrl && empty($summURLs)*}
      {if $showAvail=="true" && $summInterlibraryLoan==0 && $electronicResource != "1"}
          <span class="ajax_availability hide" id="status{$summId|escape}">{translate text='Loading'}...</span>
      {/if}

      {if $summInterlibraryLoan=="1"}
          <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$summId}" target="_blank">{translate text="interlibrary loan"}</a></span>
          {if $showAcqProp=="1"}
              <span> | <a href="{translate text="Erwerbungsvorschlag_Url"}{$summOpenUrl|escape}&gvk_ppn={$summId}" target="_blank">{translate text="Erwerbungsvorschlag"}</a></span>
          {/if}
      {/if}

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
    {if $user}
        <div id="saveRecordBox{$summId|escape}">
            <a id="saveRecord{$summId|escape}" href="{$url}/Record/{$summId|escape:"url"}/Save" class="fav tool saveRecord" title="{translate text='Add to favorites'}">{translate text='Add to favorites'}</a>
        </div>
    {/if}
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
