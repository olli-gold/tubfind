{if $similarAuthors}
<div class="authorbox">
  <p>{translate text='Author Results for'} <strong>{$lookfor|escape}</strong></p>
  <div class="span-5">
    {foreach from=$similarAuthors.list item=author name=authorLoop}
      {if $smarty.foreach.authorLoop.iteration == 6}
        <a href="{$similarAuthors.lookfor|escape}"><strong>{translate text='see all'}{if $similarAuthors.count} {$similarAuthors.count}{/if} &raquo;</strong></a>
        </div>
        <div class="span-5 last">
      {/if}
      <a href="{$author.url|escape}">{$author.value|escape}</a>
      {if !$smarty.foreach.authorLoop.last}<br/>{/if}
    {/foreach}
  </div>
  <div class="clear"></div>
</div>
{/if}