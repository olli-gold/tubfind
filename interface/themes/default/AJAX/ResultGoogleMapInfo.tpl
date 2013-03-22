<div class="mapInfoWrapper">
  <h2>{translate text='map_results_label'}</h2>
  <div class="mapInfoResults">
    {foreach from=$recordSet item=record name="recordLoop"}
      <div class="mapInfoResult {if ($smarty.foreach.recordLoop.iteration % 2) == 0}alt {/if}record{$smarty.foreach.recordLoop.iteration}">
        <div class="mapInfoResultThumb">
          {if $record.thumbnail}<img class="mapInfoResultThumbImg" src="{$record.thumbnail|escape}" style="display:block"/>{/if}
        </div>

        <div class="mapInfoResultText">
        <a href="{$url}/Record/{$record.id|escape:"url"}">{$record.title|truncate:65}</a><br/>
        {translate text="by"} <a href="{$url}/Author/Home?author={$record.author|escape:"url"}">{$record.author|truncate:63}</a>
        </div>

      </div>
      <div class="clearer"></div>
    {/foreach}
  </div>
  {if $recordCount >= 6}
    <div class="mapSeeAllDiv">
      <a href="{$completeListUrl|escape}">{translate text='see all'} {$recordCount|escape}...</a>
    </div>
  {/if}
</div>
