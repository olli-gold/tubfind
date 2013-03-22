{if $validData}
<div class="sidegroup rssResults">
  <div class="suggestionHeader">
  <a href="http://www.europeana.eu/portal/" title="Europeana.eu" target="_blank">
    <img class="suggestionLogo" src="{$path}/images/{$feedTitle|lower}.png"/>
  </a> 
  </div>
  <div class="clearer"></div>
  <div>
  <ul class="suggestion">
    {foreach from=$worksArray item=work key=workKey name="workLoop"}
      <li class="suggestedResult {if ($smarty.foreach.workLoop.iteration % 2) == 0}alt {/if}record{$smarty.foreach.wLoop.iteration}">
        <div class="resultitem">
          {if $work.enclosure}
            <span class="europeanaImg"><img src="{$work.enclosure|escape}" id="europeanaImage{$workKey|escape}" style="display: none;" class="europeanaImage" onload="document.getElementById('europeanaImage{$workKey|escape}').style.display = 'inline';"/></span>
          {/if}
          <a href="{$work.link|escape}" target="_blank">
            <span>{$work.title|truncate:100}</span>
          </a>
        <div class="clearer"></div>
      </li>
    {/foreach}
  </ul>
  <p class="olSubjectMore">
    <a href="{$sourceLink|escape}" title="{$feedTitle|escape}" target="_blank">
      {translate text='more'}...
    </a>
  </p>
 </div> 
</div>
<div class="clearer"></div>
{/if}
