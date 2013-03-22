<div class="result recordId" id="record{$summId|escape}">
  
  {if $bookBag}
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$summId|escape}" class="checkbox_ui"/>
  <input type="hidden" name="idsAll[]" value="{$summId|escape}" />
  {/if}
  
  <div class="span-2">
  {if $summThumb}
    <img src="{$summThumb|escape}" class="summcover" alt="{translate text='Cover Image'}"/>
    {else}
    <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
  {/if}
  </div>
  <div class="span-9">
    <div class="resultItemLine1">
      <a href="{$url}/Record/{$summId|escape:"url"}" class="title">{if !empty($summHighlightedTitle)}{$summHighlightedTitle|addEllipsis:$summTitle|highlight}{elseif !$summTitle}{translate text='Title not available'}{else}{$summTitle|truncate:180:"..."|escape}{/if}</a>
    </div>

    <div class="resultItemLine2">
      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Author/Home?author={$summAuthor|escape:"url"}">{if !empty($summHighlightedAuthor)}{$summHighlightedAuthor|highlight}{else}{$summAuthor|escape}{/if}</a>
      {/if}

      {if $summDate}{translate text='Published'} {$summDate.0|escape}{/if}
    </div>

    <div class="last">
      {if !empty($summSnippetCaption)}<strong>{translate text=$summSnippetCaption}:</strong>{/if}
      {if !empty($summSnippet)}<span class="quotestart">&#8220;</span>...{$summSnippet|highlight}...<span class="quoteend">&#8221;</span><br/>{/if}
      <div id="callnumAndLocation{$summId|escape}">
      {if $summAjaxStatus}
        <strong class="hideIfDetailed{$summId|escape}">{translate text='Call Number'}:</strong> <span class="ajax_availability hide" id="callnumber{$summId|escape}">{translate text='Loading'}...</span><br class="hideIfDetailed{$summId|escape}"/>
        <strong>{translate text='Located'}:</strong> <span class="ajax_availability hide" id="location{$summId|escape}">{translate text='Loading'}...</span>
        <div class="hide" id="locationDetails{$summId|escape}"></div>
      {elseif !empty($summCallNo)}
        <strong>{translate text='Call Number'}:</strong> {$summCallNo|escape}
      {/if}
      </div>

      {if $summOpenUrl || !empty($summURLs)}
        {if $summOpenUrl}
          <br/>
          {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
        {/if}
        {foreach from=$summURLs key=recordurl item=urldesc}
          <br/><a href="{if $proxy}{$proxy}/login?qurl={$recordurl|escape:"url"}{else}{$recordurl|escape}{/if}" class="fulltext" target="new">{if $recordurl == $urldesc}{translate text='Get full text'}{else}{$urldesc|escape}{/if}</a>
        {/foreach}
      {/if}

      <br class="hideIfDetailed{$summId|escape}"/>
      {foreach from=$summFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}

      {if !$summOpenUrl && empty($summURLs)}
      <div class="ajax_availability hide" id="status{$summId|escape}">{translate text='Loading'}...</div>
      {/if}
    </div>

    {if $showPreviews}
      {if (!empty($summLCCN) || !empty($summISBN) || !empty($summOCLC))}
      <div>
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
    <a id="saveRecord{$summId|escape}" href="{$url}/Record/{$summId|escape:"url"}/Save" class="fav tool saveRecord" title="{translate text='Add to favorites'}">{translate text='Add to favorites'}</a>

    {* Display the lists that this record is saved to *}
    <div class="savedLists info hide" id="savedLists{$summId|escape}">
      <strong>{translate text="Saved in"}:</strong>
    </div>
  </div>

  <div class="clear"></div>
</div>

{if $summCOinS}<span class="Z3988" title="{$summCOinS|escape}"></span>{/if}
