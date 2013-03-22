{if $subrecords}
    <h3>{translate text="Associated volumes"}</h3>
    <ul>
    {foreach from=$subrecords item=item}
        {assign var=length value=$item.spelling|@strlen}
        {if $item.id!=$id}
            <li>
                {if $item.record_url}
                    <a href="{$item.record_url}">
                {else}
                    <a href="{$url}/Record/{$item.id}">
                {/if}
                {if $item.contents.0}
                    {$item.contents.0}
                {else}
                    {if $item.title_full}
                        {$item.title_full.0}
                    {else}
                        {$item.spelling|substr:0:$length-17|escape}
                    {/if}
                {/if}
                {if $item.publishDate.0}
                    {$item.publishDate.0}
                {/if}
                </a>
            </li>
        {/if}
    {/foreach}
    </ul>
{/if}