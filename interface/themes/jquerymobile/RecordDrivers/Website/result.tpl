{foreach from=$summURLs item=recordurl}{/foreach}
<a rel="external" href="{$recordurl}">
  <div class="result recordId" id="record{$summId|escape}">
  <h3>
    {if $summTitleGer}
        <img src="{$url}/interface/themes/blueprint-tub/images/de.gif" border="0" alt="Deutsch" title="Deutsch"/>
        {if is_array($summTitleGer)}
            {$summTitleGer.0|truncate:180:"..."|highlight:$lookfor}
        {else}
            {$summTitleGer|truncate:180:"..."|highlight:$lookfor}
        {/if}
        {if $summTitleEng}
            <br/>
            <img src="{$url}/interface/themes/blueprint-tub/images/uk.gif" border="0" alt="English" title="English"/>
            {if is_array($summTitleEng)}
                {$summTitleEng.0|truncate:180:"..."|highlight:$lookfor}
            {else}
                {$summTitleEng|truncate:180:"..."|highlight:$lookfor}
            {/if}
        {/if}
    {else}
        {if !summTitle}{translate text='Title not available'}{else}
            {if is_array($summTitle)}
                {$summTitle.0|truncate:180:"..."|highlight:$lookfor}
            {else}
                {$summTitle|truncate:180:"..."|highlight:$lookfor}
            {/if}
        {/if}
    {/if}
  </h3>
  {if !empty($summAuthor)}
    <p>{translate text='by'} {$summAuthor}</p>
  {/if}
    <p>{translate text='Located'}: <span>{translate text='Website TUBHH'}</span></p>
    <p>{$recordurl}</p>
  </div>
</a>
