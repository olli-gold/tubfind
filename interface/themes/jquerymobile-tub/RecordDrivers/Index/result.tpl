<a rel="external" href="{$path}/Record/{$summId|escape:'url'}">
  <div class="result recordId" id="record{$summId|escape}">
  <h3>
    {if !empty($summHighlightedTitle)}{$summHighlightedTitle|trim:':/'|highlight}{else}{$summTitle|trim:':/'|escape}{/if}
  </h3>
  {if !empty($summAuthor)}
    <p>{translate text='by'} {$summAuthor}</p>
  {/if}
  {if $summAjaxStatus}
    <p id="callnumAndLocation{$summId|escape}"><strong>{translate text='Call Number'}:</strong> <span class="ajax_availability hide callnumber{$summId|escape}">{translate text='Loading'}...</span><br/>
    <strong>{translate text='Located'}:</strong> <span class="ajax_availability hide location{$summId|escape}">{translate text='Loading'}...</span></p>
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