<div class="span-18">
  {* Recommendations *}
  {if $topRecommendations}
    {foreach from=$topRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
  <div class="resulthead"><h3>{translate text='nohit_heading'}</h3></div>
  <p class="error">{translate text='nohit_prefix'} - <strong>{$lookfor|escape:"html"}</strong> - {translate text='nohit_suffix'}</p>

  {if $parseError}
    <p class="error">{translate text='nohit_parse_error'}</p>
  {/if}


  <div class="searchtipps"><strong>{translate text='nohit_tipps'}</strong>
    <ul>
        {if $tab == "all"}
            <li>{translate text='nohit_index_selected'} <em>{translate text="Books and more"}</em> {translate text='nohit_index_also'} <em><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=Primo Central&tab=primo">{translate text="Articles and more"}</a></em>!</li>
        {/if}
        {if $tab == "primo"}
            <li>{translate text='nohit_index_selected'} <em>{translate text="Articles and more"}</em> {translate text='nohit_index_also'} <em><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=GBV Central&shard[]=TUBdok&shard[]=wwwtub&tab=all">{translate text="Books and more"}</a></em>!</li>
        {/if}
        {if !$checkboxFilters.showAll.selected}
            <li>{translate text='nohit_tipp_expand'} - <a href="{$checkboxFilters.showAll.toggleUrl}">{translate text=$checkboxFilters.showAll.desc}</a></li>
        {/if}
        {if $spellingSuggestions}
          <li class="correction">{translate text='nohit_spelling'}</li>
          {foreach from=$spellingSuggestions item=details key=term name=termLoop}
            {$term|escape} &raquo; {foreach from=$details.suggestions item=data key=word name=suggestLoop}<a href="{$data.replace_url|escape}">{$word|escape}</a>{if $data.expand_url} 
            <!--<a href="{$data.expand_url|escape}"><img src="{$path}/images/silk/expand.png" alt="{translate text='spell_expand_alt'}"/></a>-->
            {/if}{if !$smarty.foreach.suggestLoop.last}, {/if}{/foreach}{if !$smarty.foreach.termLoop.last}<br/>{/if}
          {/foreach}
        {/if}
    </ul>
    <br/>
    <strong>{translate text='nohit_still_nothing'}</strong><br/>
    {translate text='nohit_contact'}
  </div>
</div>

  
{* Narrow Search Options *}
<div class="span-5 last">
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
</div>
{* End Narrow Search Options *}

<div class="clear"></div>
