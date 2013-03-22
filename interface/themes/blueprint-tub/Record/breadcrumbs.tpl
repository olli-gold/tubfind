{if $lastsearch}
<a href="{$lastsearch|escape}#record{$id|escape:"url"}">{translate text="Search"}</a> <span>&gt;</span>
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
