<div id="record{$summId|escape}" class="gridRecordBox recordId" >
    <span class="gridImageBox" >
    <a href="{$url}/Record/{$summId|escape:"url"}">
    {if $summThumbLarge}
    <img src="{$summThumbLarge|escape}" class="gridImage" alt="{translate text='Cover Image'}" />
    {elseif $summThumb}
    <img src="{$summThumb|escape}" class="gridImage" alt="{translate text='Cover Image'}" />
    {else}
    <img src="{$path}/bookcover.php" class="gridImage" alt="{translate text='No Cover Image'}"/>
    {/if}
    </a>
    </span>
    <div class="gridTitleBox" >
      <a class="gridTitle" href="{$url}/Record/{$summId|escape:"url"}" >
        {if !$summTitle}{translate text='Title not available'}{elseif !empty($summHighlightedTitle)}{$summHighlightedTitle|addEllipsis:$summTitle|highlight}{else}{$summTitle|truncate:80:"..."|escape}{/if}
      </a>
      {if $summOpenUrl || !empty($summURLs)}
        {if $summOpenUrl}
          {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
        {/if}
        {foreach from=$summURLs key=recordurl item=urldesc}
          <a href="{if $proxy}{$proxy}/login?qurl={$recordurl|escape:"url"}{else}{$recordurl|escape}{/if}" class="fulltext" target="new">{if $recordurl == $urldesc}{translate text='Get full text'}{else}{$urldesc|escape}{/if}</a><br/>
        {/foreach}
      {else}
        <div class="status">
            <span class="ajax_availability hide" id="status{$summId|escape}">{translate text='Loading'}...</span>
        </div>
      {/if}
    </div>
</div>

{if $summCOinS}<span class="Z3988" title="{$summCOinS|escape}"></span>{/if}
