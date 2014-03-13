<div class="listentry recordId" id="record{$listId|escape}">
    <label for="checkbox_{$listId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
    <input id="checkbox_{$listId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$listId|escape}" class="checkbox"/>
    <input type="hidden" name="idsAll[]" value="{$listId|escape}" />
    <div class="span-2">
    {if $listThumb}
      <img src="{$listThumb|escape}" class="summcover" alt="{translate text='Cover Image'}"/>
    {else}
      <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
    {/if}
    </div>
    <div class="span-10">
      <a href="{$url}/Record/{$listId|escape:"url"}" class="title">
      {if is_array($listTitle)}
          {$listTitle.0|escape}
      {else}
          {$listTitle|escape}
      {/if}
      </a><br/>
      {if $listAuthor}
        {translate text='by'}: <a href="{$url}/Search/Results?lookfor={$listAuthor|escape:"url"}&type=Author&localonly=1">{$listAuthor|escape}</a><br/>
      {/if}
      {if $listTags}
        <strong>{translate text='Your Tags'}:</strong>
        {foreach from=$listTags item=tag name=tagLoop}
          <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape:"html"}</a>{if !$smarty.foreach.tagLoop.last},{/if}
        {/foreach}
        <br/>
      {/if}
      {if $listNotes}
        <strong>{translate text='Notes'}:</strong>
        {if count($listNotes) > 1}<br/>{/if}
        {foreach from=$listNotes item=note}
          {$note|escape:"html"}<br/>
        {/foreach}
      {/if}

      {foreach from=$listFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
    </div>

    <div class="span-13 last">
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
            <span id="callnumber{$listId|escape}label">{translate text='Call Number'}:</span> <span class="ajax_availability hide" id="callnumber{$listId|escape}">{translate text='Loading'}...</span><br/>
        {/if}
        <span id="location{$listId|escape}label">{translate text='Located'}:</span> <span class="ajax_availability hide" id="location{$listId|escape}">{translate text='Loading'}...</span>
      {elseif !empty($listCallNo)}
        <span id="callnumber{$listId|escape}label">{translate text='Call Number'}: {$listCallNo|escape}</span>
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
          <span class="ajax_availability hide" id="status{$listId|escape}">{translate text='Loading'}...</span>
      {/if}

      {if $summInterlibraryLoan=="1"}
          <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$listId}" target="_blank">{translate text="interlibrary loan"}</a></span>
          {if $showAcqProp=="1"}
              <span> | <a href="{translate text="Erwerbungsvorschlag_Url"}{$summOpenUrl|escape}&gvk_ppn={$summId}" target="_blank">{translate text="Erwerbungsvorschlag"}</a></span>
          {/if}
      {/if}

    </div>


  {if $listEditAllowed}
    <div class="floatright">
      <a href="{$url}/MyResearch/Edit?id={$listId|escape:"url"}{if !is_null($listSelected)}&amp;list_id={$listSelected|escape:"url"}{/if}" class="edit tool">{translate text='Edit'}</a>
      {* Use a different delete URL if we're removing from a specific list or the overall favorites: *}
      <a
      {if is_null($listSelected)}
        href="{$url}/MyResearch/Favorites?delete={$listId|escape:"url"}"
      {else}
        href="{$url}/MyResearch/MyList/{$listSelected|escape:"url"}?delete={$listId|escape:"url"}"
      {/if}
      class="delete tool" onclick="return confirm('{translate text='confirm_delete'}');">{translate text='Delete'}</a>
    </div>
  {/if}

  <div class="clear"></div>
</div>