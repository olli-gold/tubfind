<div class="span-18">
  {if $info}
    <div class="authorbio">
      <h2>{$info.name|escape}</h2>

      {if $info.image}
        <img src="{$info.image}" alt="{$info.altimage|escape}" width="150px" class="alignleft recordcover"/>
      {/if}
      
      {$info.description|truncate_html:4500:"...":false}

      <div class="providerLink"><a class="wikipedia" href="http://{$wiki_lang}.wikipedia.org/wiki/{$info.name|escape:"url"}" target="new">{translate text='wiki_link'}</a></div>

      <div class="clear"></div>  
    </div>
  {/if}

  {if $topRecommendations}
    {foreach from=$topRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}

  <div class="resulthead">
    {translate text="Showing"}
    <strong>{$recordStart}</strong> - <strong>{$recordEnd}</strong>
    {translate text='of'} <strong>{$recordCount}</strong>
    {translate text='for search'}: <strong>'{$authorName|escape:"html"}'</strong>,
    {translate text='query time'}: {$qtime}s
  </div>

  {include file=Search/list-list.tpl}

  {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}

  <div class="searchtools">
    <strong>{translate text='Search Tools'}:</strong>
    <a href="{$rssLink|escape}" class="feed">{translate text='Get RSS Feed'}</a>
    <a href="{$url}/Search/Email" class="mailSearch mail" title="{translate text='Email this Search'}">{translate text='Email this Search'}</a>
  </div>
</div>
  
{* Recommendations *}
<div class="span-5 last">
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
</div>
{* End Recommendations *}

<div class="clear"></div>
