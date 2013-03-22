{if $mlaDetails.authors}{$mlaDetails.authors|escape}. {/if}
<span style="font-style: italic;">{$mlaDetails.title|escape}</span>{if $mlaDetails.periodAfterTitle}.{/if} 
{if $mlaDetails.edition}{$mlaDetails.edition|escape} {/if}
{if $mlaDetails.publisher}{$mlaDetails.publisher|escape}{/if}
{if $mlaDetails.year}{if $mlaDetails.publisher}, {/if}{$mlaDetails.year|escape}{/if}{if $mlaDetails.publisher || $mlaDetails.year}.{/if}
