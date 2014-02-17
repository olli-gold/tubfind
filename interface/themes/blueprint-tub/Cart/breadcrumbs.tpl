{if $lastsearch}
<a href="{$lastsearch|escape}{if $refer=="pc"}&shard[]=Primo Central{/if}#record{if $refer=="pc"}PCgbv{/if}{$id|escape:"url"}">{translate text="Search"}</a> <span>&gt;</span>
{/if}
{if $pageTitle}
<em>{$pageTitle|escape}</em>
<span>&gt;</span>
{/if}