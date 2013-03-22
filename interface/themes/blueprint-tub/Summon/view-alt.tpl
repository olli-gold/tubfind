<div class="record">
  <a href="{$url}/Summon/Record?id={$id|escape:"url"}" class="backtosearch">&laquo; {translate text="Back to Record"}</a>

  {if $pageTitle}<h1>{$pageTitle}</h1>{/if}
  {include file="Summon/$subTemplate"}
</div>
