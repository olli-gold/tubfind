{if !empty($dbRecommendations)}
<div class="sidegroup">
  <h4>{translate text="Databases"}</h4>
<div class="authorbox">
  <p>{translate text='database_recommendations'}</p>
  <ul>
  {foreach from=$dbRecommendations item='current'}
    <li><a href="{$current.url|escape}">{$current.name|escape}</a>
    {*({$current.group|escape})*}
    {if $current.rank}
        {if substr_count($current.rank, '*') == 3}
            <img src="{$url}/interface/themes/blueprint-tub/images/sternchen.png" alt="Ranking" /><img src="{$url}/interface/themes/blueprint-tub/images/sternchen.png" alt="Ranking" /><img src="{$url}/interface/themes/blueprint-tub/images/sternchen.png" alt="Ranking" />
        {elseif substr_count($current.rank, '*') == 2}
            <img src="{$url}/interface/themes/blueprint-tub/images/sternchen.png" alt="Ranking" /><img src="{$url}/interface/themes/blueprint-tub/images/sternchen.png" alt="Ranking" />
        {elseif substr_count($current.rank, '*') == 1}
            <img src="{$url}/interface/themes/blueprint-tub/images/sternchen.png" alt="Ranking" />
        {/if}
        {*&nbsp;{$current.rank|escape}*}
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