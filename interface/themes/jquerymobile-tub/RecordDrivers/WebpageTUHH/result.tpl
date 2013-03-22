{foreach from=$summURLs item=recordurl}{/foreach}
<a rel="external" href="{$recordurl}">
  <div class="result recordId" id="record{$summId|escape}">
  <h3 class="ui-li-heading">
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
    <p><a href="{$recordurl}" target="_blank">{$recordurl|truncate:54:'...':true:true}</a></p>
    <p>{$summContent.0|truncate:300|highlight:$lookfor}</p>
    <p>{translate text='Located'}: <span>{translate text='TUHH Websites'}</span></p>
  </div>
</a>
