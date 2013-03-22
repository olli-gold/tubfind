{if !empty($summonResults)}
<div class="box submenu catalogMini">
  <h4>{translate text='Summon Results'}</h4>

  <ul class="similar">
    {foreach from=$summonResults item=record}
    <li>
      <a href="{$url}/Summon/Record?id={$record.ID.0|escape:"url"}">{if !$record.Title.0}{translate text='Title not available'}{else}{$record.Title.0|highlight}</a>{/if}
      <span style="font-size: .8em">
      {if $record.Author}
        <br>{translate text='by'}
        {foreach from=$record.Author item=author name="loop"}
          <a href="{$url}/Summon/Search?type=Author&amp;lookfor={$author|unhighlight|escape:"url"}">{$author|highlight}</a>{if !$smarty.foreach.loop.last},{/if}
        {/foreach}
      {/if}
      </span>
    </li>
    {/foreach}
  </ul>
  <hr>
  <p><a href="{$summonSearchUrl|escape}">{translate text='More Summon results'}...</a></p>
</div>
{/if}