  {if $doi}<a href="http://dx.doi.org/{$doi}" target="_new">http://dx.doi.org/{$doi}</a>{/if}

  {if !empty($pcURLs) && empty($doi)}
    {foreach from=$pcURLs item=pcurl}
      <a href="{$pcurl|escape}" class="fulltext" target="new">{$pcurl|escape}</a>
    {/foreach}
  {/if}

