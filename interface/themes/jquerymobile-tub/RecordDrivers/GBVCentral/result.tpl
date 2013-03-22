{if $nlurls}
<script type="text/javascript">
var nlurls = '';
{foreach from=$nlurls key=recordurl item=urldesc}
    nlurls += '{translate text="NL"}: <a href="{$recordurl}">{$urldesc}</a><br/>';
{/foreach}
document.getElementById("nlurls{$summId|escape}").innerHTML = nlurls;
</script>
{/if}
<a rel="external" href="{$path}/Record/{$summId|escape:'url'}">
  <div class="result recordId" id="record{$summId|escape}">
  <h3>
    {if !empty($summHighlightedTitle)}
        {if is_array($summHighlightedTitle)}
            {$summHighlightedTitle.0|addEllipsis:$summShortTitle|highlight}
        {else}
            {$summHighlightedTitle|addEllipsis:$summShortTitle|highlight}
        {/if}
    {elseif !$summShortTitle}
        {translate text='Title not available'}
    {elseif is_array($summShortTitle)}
        {$summShortTitle.0|truncate:90:"..."|escape}
    {else}
        {$summShortTitle|truncate:90:"..."|escape}
    {/if}
  </h3>
  {if !empty($summAuthor) or !empty($summDate)}
  <p>
  {/if}
  {if !empty($summAuthor)}
    {translate text='by'} {$summAuthor} 
  {/if}
  {if $summDate}{$summDate.0|escape}{/if}
  {if !empty($summAuthor) or !empty($summDate)}
  </p>
  {/if}
  {if $summArticleRef}
    <p>
        {$summArticleRef}
    </p>
  {/if}

  {if $nlurls}
    <p id="nlurls{$summId|escape}">
    </p>
  {/if}
  {if $summOpenUrl}
    <p>
    {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
    </p>
  {/if}

  {if $summAjaxStatus}
    <p id="callnumber{$summId|escape}label"><strong>{translate text='Call Number'}:</strong> <span class="ajax_availability hide callnumber{$summId|escape}">{translate text='Loading'}...</span></p>
    <p id="location{$summId|escape}label"><strong>{translate text='Located'}:</strong> <span class="ajax_availability hide location{$summId|escape}">{translate text='Loading'}...</span></p>
  {elseif !empty($summCallNo)}
    <p><strong>{translate text='Call Number'}:</strong> {$summCallNo|escape}</p>
  {/if}
  {if !empty($summFormats)}
    <p>
    {foreach from=$summFormats item=format}
      <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
    {/foreach}
    {if !$summOpenUrl && empty($summURLs)}
      <span class="ajax_availability hide status{$summId|escape}">{translate text='Loading'}...</span>
    {/if}
    </p>
  {/if}
  </div>
</a>
<a href="#" data-record-id="{$summId|escape}" title="{translate text='Add to book bag'}" class="add_to_book_bag">{translate text="Add to book bag"}</a>