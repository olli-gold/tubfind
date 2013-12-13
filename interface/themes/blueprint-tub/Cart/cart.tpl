{if $smarty.post.mylang}
{assign var="jsFileName" value="check_item_statuses_"|cat:$smarty.post.mylang|cat:".js"}
{elseif $smarty.cookies.language}
{assign var="jsFileName" value="check_item_statuses_"|cat:$smarty.cookies.language|cat:".js"}
{else}
{assign var="jsFileName" value="check_item_statuses_de.js"}
{/if}
{js filename=$jsFileName}
{js filename="check_save_statuses.js"}
{js filename="jquery.cookie.js"}
{js filename="cart.js"}
{js filename="openurl.js"}
{if $showPreviews}
{js filename="preview.js"}
{/if}

{assign var=records value=$bookBag->getRecordDetails()}
{if !empty($records)}
  <ul class="cartContent">
  {foreach from=$records item=record}
    {* assuming we're dealing with VuFind records *}
    <li class="result recordId" id="record{$record.id|escape}">
      <label for="checkbox_{$record.id|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
      <input id="checkbox_{$record.id|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$record.id|escape}" class="checkbox"/>
      <input type="hidden" name="idsAll[]" value="{$record.id|escape}" />
      <a title="{translate text='View Record'}" href="{$url}/Record/{$record.id|escape}">{$record.title.0|escape}</a>
    <div class="span-2">
    {if $listThumb}
      <img src="{$listThumb|escape}" class="summcover" alt="{translate text='Cover Image'}"/>
    {else}
      <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
    {/if}
    </div>
    <div class="span-10">
      <a href="{$url}/Record/{$record.id|escape:"url"}" class="title">
      {if is_array($record.title)}
          {$record.title.0|escape}
      {else}
          {$record.title|escape}
      {/if}
      </a><br/>
      {if $listAuthor}
        {translate text='by'}: <a href="{$url}/Search/Results?lookfor={$listAuthor|escape:"url"}&type=Author&localonly=1">{$listAuthor|escape}</a><br/>
      {/if}

      {foreach from=$listFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
    </div>
    <div class="span-14 last">
        {assign var="electronicResource" value="0"}
        {assign  var="showAvail" value="true"}
        {assign var="showCallNumber" value="1"}
        {assign var="listAjaxStatus" value="1"}
        {foreach from=$listFormats item=format}  {*$format=="eBook" ||*}
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
      {if $listAjaxStatus}
        {if $showCallNumber == "1"}
            <span id="callnumber{$record.id|escape}label">{translate text='Call Number'}:</span> <span class="ajax_availability hide" id="callnumber{$record.id|escape}">{translate text='Loading'}...</span><br/>
        {/if}
        <span id="location{$record.id|escape}label">{translate text='Located'}:</span> <span class="ajax_availability hide" id="location{$record.id|escape}">{translate text='Loading'}...</span>
      {elseif !empty($listCallNo)}
        <span id="callnumber{$record.id|escape}label">{translate text='Call Number'}: {$listCallNo|escape}</span>
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

      {*if !$summOpenUrl && empty($summURLs)*}
      {if $showAvail=="true" && $summInterlibraryLoan==0 && $electronicResource != "1"}
          <span class="ajax_availability hide" id="status{$record.id|escape}">{translate text='Loading'}...</span>
      {/if}

      {if $summInterlibraryLoan=="1"}
          <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$record.id}" target="_blank">{translate text="interlibrary loan"}</a></span>
          {if $showAcqProp=="1"}
              <span> | <a href="{translate text="Erwerbungsvorschlag_Url"}{$summOpenUrl|escape}&gvk_ppn={$summId}" target="_blank">{translate text="Erwerbungsvorschlag"}</a></span>
          {/if}
      {/if}

    </div>
<div class="clear">&nbsp;</div>
    </li>
  {/foreach}
  </ul>
{else}
  <p>{translate text='bookbag_is_empty'}.</p>
{/if}



