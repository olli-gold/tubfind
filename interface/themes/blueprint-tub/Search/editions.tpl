  {if is_array($editions)}
    <h4>{translate text="Other Editions"}</h4>
    <ul class="similar">
      {foreach from=$editions item=edition}
      <li>
        {if is_array($edition.format)}
          <span class="{$edition.format[0]|lower|regex_replace:"/[^a-z0-9]/":""}">
        {else}
          <span class="{$edition.format|lower|regex_replace:"/[^a-z0-9]/":""}">
        {/if}
        {if is_array($edition.title) || is_array($edition.journal)}
          <a href="{$url}/Record/{$edition.id|escape:"url"}">
          {if $edition.title.0 != ""}
            {$edition.title.0|escape}
          {else}
            {if $edition.journal.0 != ""}
                {translate text="Journal volume"}.
                {$edition.journal.0}.
                {if $edition.publishDate.0 != ""}
                    {$edition.publishDate.0}
                {else}
                    {translate text="Year unknown"}
                {/if}
            {else}
                {translate text="Title unknown"}
            {/if}
          {/if}
          </a>
        {else}
          <a href="{$url}/Record/{$edition.id|escape:"url"}">
          {if $edition.title != ""}
            {$edition.title|escape}
          {else}
            {translate text="Title unknown"}
          {/if}
          </a>
        {/if}
        </span>
        {$edition.edition|escape}
        {if $edition.publishDate}({$edition.publishDate.0|escape}){/if}
      </li>
      {/foreach}
    </ul>
  {/if}
