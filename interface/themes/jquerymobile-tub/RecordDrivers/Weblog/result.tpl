{foreach from=$summURLs item=recordurl}{/foreach}
<a rel="external" href="{$recordurl}">
  <div class="result recordId" id="record{$summId|escape}">
  <h3>
    {if !$summTitle}
        {translate text='Title not available'}
    {else}
        {if is_array($summTitle)}
            {$summTitle.0|truncate:180:"..."|highlight:$lookfor}
        {else}
            {$summTitle|truncate:180:"..."|highlight:$lookfor}
        {/if}
    {/if}
  </h3>
  {if !empty($summAuthor)}
    <p>{translate text='by'} {$summAuthor}</p>
  {/if}
  {if $summDate}<p>{translate text='Published'} {$summDate.0|escape}</p>{/if}
    <p>{translate text='Located'}: <span>{translate text='Weblog TUBHH'}</span></p>
    <p>{$recordurl}</p>
  </div>
</a>
