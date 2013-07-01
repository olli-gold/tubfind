  {if is_array($similarRecords)}
    <h4>{translate text="Similar Items"}</h4>
    <ul class="similar">
      {foreach from=$similarRecords item=similar}
      <li>
        {if is_array($similar.format)}
        <span class="{$similar.format[0]|lower|regex_replace:"/[^a-z0-9]/":""}">
        {else}
        <span class="{$similar.format|lower|regex_replace:"/[^a-z0-9]/":""}">
        {/if}
        {if is_array($similar.title)}
            <a href="{$url}/Record/{$similar.id|escape:"url"}">
            {if $similar.title[0] != ""}
              {$similar.title[0]|escape}
            {else}
              {translate text="Title unknown"}
            {/if}
            </a>
        {else}
            <a href="{$url}/Record/{$similar.id|escape:"url"}">
            {if $similar.title != ""}
              {$similar.title|escape}
            {else}
              {translate text="Title unknown"}
            {/if}
            </a>
        {/if}
        </span>
        {if $similar.author}<br/>{translate text='By'}: {$similar.author|escape}{/if}
        {if $similar.publishDate} ({$similar.publishDate.0|escape}){/if}
      </li>
      {/foreach}
    </ul>
    {*
    {else}
      <p>{translate text='Cannot find similar records'}</p>
    *}
  {/if}
