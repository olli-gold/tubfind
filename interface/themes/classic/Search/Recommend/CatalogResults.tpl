{if !empty($catalogResults)}
<div class="box submenu catalogMini">
  <h4>{translate text='Catalog Results'}</h4>
  
  <ul class="similar">
    {foreach from=$catalogResults item=record}
    <li>
      {if is_array($record.format)}
        <span class="{$record.format[0]|lower|regex_replace:"/[^a-z0-9]/":""}">
      {else}
        <span class="{$record.format|lower|regex_replace:"/[^a-z0-9]/":""}">
      {/if}
      <a href="{$url}/Record/{$record.id|escape:"url"}">{if $record._highlighting.title.0}{$record._highlighting.title.0|addEllipsis:$record.title|highlight}{else}{$record.title|escape}{/if}</a>
      </span>
      <span style="font-size: .8em">
      {if $record.author}
      <br>{translate text='By'}: {if $record._highlighting.author.0}{$record._highlighting.author.0|highlight}{else}{$record.author|escape}{/if}
      {/if}
      {if $record.publishDate}
      <br>{translate text='Published'}: ({$record.publishDate.0|escape})
      {/if}
      </span>
    </li>
    {/foreach}
  </ul>
  <hr>
  <p><a href="{$catalogSearchUrl|escape}">{translate text='More catalog results'}...</a></p>
</div>
{/if}