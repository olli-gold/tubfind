<div class="span-13">
  {* Display Title *}
  <h1 class="recordtitle">
  {if is_array($coreShortTitle)}
    {$coreShortTitle.0|escape}
  {else}
    {$coreShortTitle|escape}
  {/if}
    {if $coreSubtitle}
        {if is_array($coreSubtitle)}
            {$coreSubtitle.0|escape}
        {else}
            {$coreSubtitle|escape}
        {/if}
    {/if}
  {if $coreTitleSection}{$coreTitleSection|escape}{/if}
  {* {if $coreTitleStatement}{$coreTitleStatement|escape}{/if} *}
  </h1>
  {* End Title *}
  {* nur in Tab Beschreibung linken *}

  {if $coreSummary}<p><a href='{$url}/Record/{$id|escape:"url"}/Description#tabs'>{translate text='Full description'}</a></p>{/if}

  {* Display Main Details *}
  <table cellpadding="2" cellspacing="0" border="0" class="citation" summary="{translate text='Bibliographic Details'}">
    {if !empty($coreNextTitles)}
    <tr valign="top">
      <th>{translate text='New Title'}: </th>
      <td>
        {foreach from=$coreNextTitles item=field name=loop}
          <a href="{$url}/Search/Results?lookfor=%22{$field|escape:"url"}%22&amp;type=Title">{$field|escape}</a><br/>
        {/foreach}
      </td>
    </tr>
    {/if}

    {if !empty($corePrevTitles)}
    <tr valign="top">
      <th>{translate text='Previous Title'}: </th>
      <td>
        {foreach from=$corePrevTitles item=field name=loop}
          <a href="{$url}/Search/Results?lookfor=%22{$field|escape:"url"}%22&amp;type=Title">{$field|escape}</a><br/>
        {/foreach}
      </td>
    </tr>
    {/if}

    {if !empty($coreMainAuthor)}
    <tr valign="top">
      <th>{translate text='Main Author'}: </th>
      <td><a href="{$url}/Search/Results?lookfor={$coreMainAuthor|escape:"url"}&type=Author&localonly=1">{$coreMainAuthor|escape}</a></td>
    </tr>
    {/if}

    {if !empty($coreCorporateAuthor)}
    <tr valign="top">
      <th>{translate text='Corporate Author'}: </th>
      <td><a href="{$url}/Search/Results?lookfor={$coreCorporateAuthor|escape:"url"}&type=Author&localonly=1">{$coreCorporateAuthor|escape}</a></td>
    </tr>
    {/if}

    {if !empty($coreContributors)}
    <tr valign="top">
      <th>{translate text='Other Authors'}: </th>
      <td>
        {foreach from=$coreContributors item=field name=loop}
          <a href="{$url}/Search/Results?lookfor={$field|escape:"url"}&type=Author&localonly=1">{$field|escape}</a>{if !$smarty.foreach.loop.last}; {/if}
        {/foreach}
      </td>
    </tr>
    {/if}

    {if !empty($coreISBNs) || !empty($coreISSNs)}
    <tr valign="top">
      <th>{translate text='ISBN/ISSN'}: </th>
      <td>
        {foreach from=$coreISBNs item=isbn name=loop}
          {$isbn|escape}<br/>
        {/foreach}
        {foreach from=$coreISSNs item=issn name=loop}
          {$issn|escape}<br/>
        {/foreach}
      </td>
    </tr>
    {/if}

    {if !empty($coreUniv)}
      <tr valign="top">
        <th>{translate text='University'}: </th>
        <td>
          {$coreUniv|escape}
        </td>
      </tr>
    {/if}

    <tr valign="top">
      <th>{translate text='Format'}: </th>
      <td>
       {if is_array($recordFormat)}
        {foreach from=$recordFormat item=displayFormat name=loop}
          <span class="iconlabel {$displayFormat|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$displayFormat}</span>
        {/foreach}
      {else}
        <span class="iconlabel {$recordFormat|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$recordFormat}</span>
      {/if}
      </td>
    </tr>

    {if $coreArticleRef}
      <tr valign="top">
        <th> </th>
        <td>{$coreArticleRef}</td>
      </tr>
    {/if}

    {if $recordLanguage}
    <tr valign="top">
      <th>{translate text='Language'}: </th>
      <td>{foreach from=$recordLanguage item=lang}{$lang|escape}<br/>{/foreach}</td>
    </tr>
    {/if}

    {if !empty($corePublications)}
    <tr valign="top">
      <th>{translate text='Year published'}: </th>
      <td>
        {foreach from=$corePublications item=field name=loop}
            {$field|escape}<br/>
        {/foreach}
      </td>
    </tr>
    {/if}

    {if !empty($coreDateSpan)}
    <tr valign="top">
      <th>{translate text='Publishing Period'}: </th>
      <td>
        {foreach from=$coreDateSpan item=field name=loop}
            {$field|escape}<br/>
        {/foreach}
      </td>
    </tr>
    {/if}

    {if !empty($coreEdition)}
    <tr valign="top">
      <th>{translate text='Edition'}: </th>
      <td>
        {$coreEdition|escape}
      </td>
    </tr>
    {/if}

    {* Display series section if at least one series exists. *}
    {if !empty($coreSeries)}
    <tr valign="top">
      <th>{translate text='Series'}: </th>
      <td>
        {if $parentRecord && $showAssociated == 3}
            {foreach from=$parentRecord item=paRec name=pR}
            {if $paRec.id!=$id}
                {if $paRec.record_url}
                    <a href="{$paRec.record_url}">
                {else}
                    <a href="{$url}/Record/{$paRec.id}">
                {/if}
                {if $paRec.contents.0}
                    {$paRec.contents.0}
                {else}
                    {if $paRec.title_full}
                        {$paRec.title_full.0}
                    {else}
                        {$paRec.spelling|substr:0:$length-17|escape}
                    {/if}
                {/if}
                </a>
                {if $paRec.number}
                    {$paRec.number}
                {/if}
                <br/>
            {/if}
            {/foreach}
        {else}
            {foreach from=$coreSeries item=field name=loop}
              {* Depending on the record driver, $field may either be an array with
                 "name" and "number" keys or a flat string containing only the series
                 name.  We should account for both cases to maximize compatibility. *}
              {if is_array($field)}
                {if !empty($field.name)}
                  <!--<a href="{$url}/Search/Results?lookfor=%22{$field.name|escape:"url"}%22&amp;type=Series">{$field.name|escape}</a>-->
                  {$field.name|escape}
                  {if !empty($field.number)}
                    {$field.number|escape}
                  {/if}
                  <br/>
                {/if}
              {else}
                <!--<a href="{$url}/Search/Results?lookfor=%22{$field|escape:"url"}%22&amp;type=Series">{$field|escape}</a><br/>-->
                {$field|escape}<br/>
              {/if}
            {/foreach}
        {/if}
      </td>
    </tr>
    {/if}

    {if !empty($coreSubjects)}
    <tr valign="top">
      <th>{translate text='Subjects'}: </th>
      <td>
        {foreach from=$coreSubjects item=field name=loop}
        <div class="subjectLine">
          {assign var=subject value=""}
          {foreach from=$field item=subfield name=subloop}
            {if !$smarty.foreach.subloop.first} &gt; {/if}
            {assign var=subject value="$subject $subfield"}
            <a title="{$subject|escape}" href="{$url}/Search/Results?lookfor=%22{$subject|escape:"url"}%22&amp;type=Subject" class="subjectHeading">{$subfield|escape}</a>
          {/foreach}
        </div>
        {/foreach}
      </td>
    </tr>
    {/if}
<!--
    {if !empty($coreURLs) || $coreOpenURL}
    <tr valign="top">
      <th>{translate text='Online Access'}: </th>
      <td>
        {foreach from=$coreURLs item=desc key=currentUrl name=loop}
          <a href="{if $proxy}{$proxy}/login?url={$currentUrl|escape:"url"}{else}{$currentUrl|escape}{/if}">{$desc|escape}</a><br/>
        {/foreach}
        {if $coreOpenURL}
          {include file="Search/openurl.tpl" openUrl=$coreOpenURL}<br/>
        {/if}
      </td>
    </tr>
    {/if}
-->
{*
    <tr valign="top">
      <th>{translate text='Tags'}: </th>
      <td>
        <span style="float:right;">
          <a href="{$url}/Record/{$id|escape:"url"}/AddTag" class="tool add tagRecord" title="{translate text='Add Tag'}" id="tagRecord{$id|escape}">{translate text='Add Tag'}</a>
        </span>
        <div id="tagList">
          {if $tagList}
            {foreach from=$tagList item=tag name=tagLoop}
          <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape:"html"}</a> ({$tag->cnt}){if !$smarty.foreach.tagLoop.last}, {/if}
            {/foreach}
          {else}
            {translate text='No Tags'}, {translate text='Be the first to tag this record'}!
          {/if}
        </div>
      </td>
    </tr>

    <tr valign="top">
      <th>{translate text='QR-Code'}: </th>
      <td>
        <span class="showqr">
          <a href="#" onClick='document.getElementById("qrcode").style.display = "block"; document.getElementById("showqr").style.display = "none"; document.getElementById("hideqr").style.display = "block";' id="showqr">{translate text="Show QR-Code"}</a>
          <a href="#" onClick='document.getElementById("qrcode").style.display = "none"; document.getElementById("hideqr").style.display = "none"; document.getElementById("showqr").style.display = "block";' style="display:none;" id="hideqr">{translate text="Hide QR-Code"}</a>
        </span>
        <div id="qrcode" style="display:none">
          <img alt="QR-Image of this book" src="{$path}/qr_img.php?d={$qr}">
        </div>
      </td>
    </tr>
*}
  </table>
  {* End Main Details *}
</div>

<div class="span-4 last">
  {* Display Cover Image *}
  {if $coreThumbMedium}
    {if $coreThumbLarge}<a href="{$coreThumbLarge|escape}">{/if}
      <img alt="{translate text='Cover Image'}" class="recordcover" src="{$coreThumbMedium|escape}"/>
    {if $coreThumbLarge}</a>{/if}
  {/if}
  {* End Cover Image *}

  {* Display the lists that this record is saved to *}
  <div class="savedLists info hide" id="savedLists{$id|escape}">
    <strong>{translate text="Saved in"}:</strong>
  </div>

  {if $showPreviews && (!empty($holdingLCCN) || !empty($isbn) || !empty($holdingOCLC))}
    {if $showGBSPreviews}
      <div class="previewDiv">
        <a title="{translate text='Preview from'} Google Books" class="hide previewGBS{if $isbn} ISBN{$isbn}{/if}{if $holdingLCCN} LCCN{$holdingLCCN}{/if}{if $holdingOCLC} OCLC{$holdingOCLC|@implode:' OCLC'}{/if}" target="_blank">
          <img src="https://www.google.com/intl/en/googlebooks/images/gbs_preview_button1.png" alt="{translate text='Preview'}"/>
        </a>
      </div>
    {/if}
    {if $showOLPreviews}
      <div class="previewDiv">
        <a title="{translate text='Preview from'} Open Library" href="" class="hide previewOL{if $isbn} ISBN{$isbn}{/if}{if $holdingLCCN} LCCN{$holdingLCCN}{/if}{if $holdingOCLC} OCLC{$holdingOCLC|@implode:' OCLC'}{/if}" target="_blank">
          <img src="{$path}/images/preview_ol.gif" alt="{translate text='Preview'}"/>
        </a>
      </div>
    {/if}
    {if $showHTPreviews}
      <div class="previewDiv">
        <a title="{translate text='Preview from'} HathiTrust" class="hide previewHT{if $isbn} ISBN{$isbn}{/if}{if $holdingLCCN} LCCN{$holdingLCCN}{/if}{if $holdingOCLC} OCLC{$holdingOCLC|@implode:' OCLC'}{/if}" target="_blank">
          <img src="{$path}/images/preview_ht.gif" alt="{translate text='Preview'}"/>
        </a>
      </div>
    {/if}
    <span class="previewBibkeys{if $isbn} ISBN{$isbn}{/if}{if $holdingLCCN} LCCN{$holdingLCCN}{/if}{if $holdingOCLC} OCLC{$holdingOCLC|@implode:' OCLC'}{/if}"></span>
  {/if}
</div>

<div class="clear"></div>