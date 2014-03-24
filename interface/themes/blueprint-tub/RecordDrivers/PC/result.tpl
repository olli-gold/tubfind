<div class="result recordId" id="record{$summId|escape}">
  {* hide until complete
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="id[]" value="{$summId|escape}" class="checkbox addToCartCheckbox"/>
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
      <b class="listtitle">
      {if !empty($summHighlightedTitle)}{$summHighlightedTitle|addEllipsis:$summShortTitle|highlight}
      {elseif !$summShortTitle}{translate text='Title not available'}
      {elseif is_array($summShortTitle)}{$summShortTitle.0|truncate:90:"...":true:true|escape}
      {else}{$summShortTitle|truncate:90:"...":true:true|escape}{/if}
      </b>
      {if $titleSeries}
          ({$titleSeries})
      {/if}
      </a>
    </div>

    <div class="resultItemLine2">
      {if !empty($summAuthor.0)}
      {translate text='by'}
      {foreach from=$summAuthor item=aut}
        <a href="{$url}/Search/Results?lookfor={$aut|escape:"url"}">
            {$aut|escape}
        </a>
      {/foreach}
      {if $multiaut > 1}
        {translate text='and others'}{if $summDate.0};{/if}
      {/if}
      {/if}

      {if $summDate} {$summDate.0|escape}{/if}

      {if $doi}<br/><a href="http://dx.doi.org/{$doi}" target="_new">http://dx.doi.org/{$doi}</a>{/if}

      {if !empty($pcURLs) && empty($doi)}
        {foreach from=$pcURLs item=pcurl}
          <br/><a href="{$pcurl|escape}" class="fulltext" target="new">{$pcurl|escape}</a>
        {/foreach}
      {/if}

      {if $summArticleRef}
        <br/>
        {$summArticleRef}
      {/if}
    </div>

    <div class="span-13">
        Score: {$score}
    </div>

    <div class="span-14 last">
        {assign  var="showAvail" value="true"}
        {foreach from=$summFormats item=format}
            {if $format=="Serial" || $format=="Journal" || $format=="Electronic" || $format=="eBook" || $format=="Aufsätze"}
                {assign var="showAvail" value="false"}
            {/if}
        {/foreach}
        <div class="ajax_printed hide" id="printed{$summId|escape}">
            <span id="printed{$summId|escape}-availability" class="hide"></span>
            <span id="printed{$summId|escape}-volume" class="hide"></span>
            <span id="callnumber{$summId|escape}label" class="hide">{translate text='Call Number'}:</span> <span class="hide" id="callnumber{$summId|escape}"></span>
            <span id="location{$summId|escape}label" class="hide">{translate text='Located'}:</span> <span class="hide" id="location{$summId|escape}"></span>
        </div>

      {if $summOpenUrl}
          <br/>
          {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
      {/if}

      {if empty($pcURLs) && empty($doi)}
          <span class="hidden" id="sfxmenu{$summId|escape}"><a href="{$sfxmenu}"><img src="{$sfxbutton}" alt="SFX" /></a></span>
      {/if}
      {if $gbvppn}
        <br/><a href="{$url}/Record/{$gbvppn|escape:"url"}?shard[]=GBV Central&shard[]=wwwtub&shard[]=tubdok&refer=pc" class="title">{if $locally}{translate text="This record at TUHH"}{else}{translate text="This record in the GBV"}{/if}</a>
      {/if}

      <br/>
      {foreach from=$summFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}

      {if !$summOpenUrl && empty($summURLs) && $showAvail=="true"}
      <span class="ajax_availability hide" id="status{$summId|escape}">{translate text='Loading'}...</span>
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
