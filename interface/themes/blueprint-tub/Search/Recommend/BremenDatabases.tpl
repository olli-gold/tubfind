{if !empty($dbRecommendations)}
<div class="sidegroup">
  <h4>{translate text="Databases"}</h4>
<div class="authorbox">
  <p>{translate text='database_recommendations'}</p>
  <ul>
  {foreach from=$dbRecommendations item='current'}
    <li><a href="{$current.url|escape}">{$current.name|escape}</a>
    {if $current.rank}
    &nbsp;{$current.rank|escape}
    {/if}
    {if $current.description}
    <br/>{$current.description|escape}
    {/if}
    </li>
  {/foreach}
  </ul>
</div>
</div>
{/if}