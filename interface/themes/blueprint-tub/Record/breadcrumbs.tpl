{if $lastsearch}
<a href="{$lastsearch|escape}{if $refer=="pc"}&shard[]=Primo Central{/if}#record{if $refer=="pc"}PCgbv{/if}{$id|escape:"url"}">{translate text="Result List"}</a> <span>&gt;</span>
{/if}
{if $breadcrumbText}
    {if is_array($breadcrumbText)}
        <em>{$breadcrumbText.0|truncate:30:"..."|escape}</em> <span>&gt;</span>
    {else}
        <em>{$breadcrumbText|truncate:30:"..."|escape}</em> <span>&gt;</span>
    {/if}
{/if}
{if $subTemplate!=""}
<em>{$subTemplate|replace:'view-':''|replace:'.tpl':''|replace:'../MyResearch/':''|capitalize|translate}</em> 
{/if}
