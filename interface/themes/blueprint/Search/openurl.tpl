{if $openUrlEmbed}{assign var="openUrlId" value=$openUrlCounter->increment()}{/if}
<a href="{$openUrlBase|escape}?{$openUrl|escape}" 
{if $openUrlEmbed} 
class="fulltext openUrlEmbed openurl_id:{$openUrlId}"
{elseif $openUrlWindow} 
class="fulltext openUrlWindow window_settings:{$openUrlWindow|escape}"
{/if}
>
  {* put the openUrl here in a span (COinS almost) so we can retrieve it later *}
  <span title="{$openUrl|escape}" class="openUrl"></span>
  {if $openUrlGraphic}
    <img src="{$openUrlGraphic|escape}" alt="{translate text='Get full text'}" style="{if $openUrlGraphicWidth}width:{$openUrlGraphicWidth|escape}px;{/if}{if $openUrlGraphicHeight}height:{$openUrlGraphicHeight|escape}px;{/if}" />
  {else}
    {translate text='Get full text'}
  {/if}
</a>
{if $openUrlEmbed}
  <div id="openUrlEmbed{$openUrlId}" class="resolver hide">{translate text='Loading...'}</div>
{/if}
