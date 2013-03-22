<div class="sidegroup">
  {if $recordCount > 0}<h4>{translate text='Narrow Search'}</h4>{/if}
  {if isset($checkboxFilters) && count($checkboxFilters) > 0}
      {foreach from=$checkboxFilters item=current}
        <div class="checkboxFilter{if $recordCount < 1 && !$current.selected && !$current.alwaysVisible} hide{/if}">
              <input type="checkbox" name="filter[]" value="{$current.filter|escape}"
                {if $current.selected}checked="checked"{/if} id="{$current.desc|replace:' ':''|escape}"
                onclick="document.location.href='{$current.toggleUrl|escape}';" />
              <label for="{$current.desc|replace:' ':''|escape}">{translate text=$current.desc}</label>
        </div>
      {/foreach}
  {/if}
  {if $filterList}
    <strong>{translate text='Remove Filters'}</strong>
    <ul class="filters">
    {foreach from=$filterList item=filters key=field}
      {foreach from=$filters item=filter}
        <li><a href="{$filter.removalUrl|escape}"><img src="{$path}/images/silk/delete.png" alt="Delete"/></a> <a href="{$filter.removalUrl|escape}">{translate text=$field}: {$filter.display|escape}</a></li>
      {/foreach}
    {/foreach}
    </ul>
  {/if}
  {if $sideFacetSet && $recordCount > 0}
    {foreach from=$sideFacetSet item=cluster key=title}
    {if isset($dateFacets.$title)}
      {* Load the publication date slider UI widget *}
      {js filename="pubdate_slider.js"}
      <form action="" name="{$title|escape}Filter" id="{$title|escape}Filter">
        {* keep existing search parameters as hidden inputs *}
        {foreach from=$smarty.get item=paramValue key=paramName}
          {if is_array($smarty.get.$paramName)}
            {foreach from=$smarty.get.$paramName item=paramValue2}
              {if strpos($paramValue2, $title) !== 0}
                <input type="hidden" name="{$paramName}[]" value="{$paramValue2|escape}" />
              {/if}
            {/foreach}
          {else}
            {if (strpos($paramName, $title)   !== 0)
                && (strpos($paramName, 'module') !== 0)
                && (strpos($paramName, 'action') !== 0)
                && (strpos($paramName, 'page')   !== 0)}
              <input type="hidden" name="{$paramName}" value="{$paramValue|escape}" />
            {/if}
          {/if}
        {/foreach}
        <input type="hidden" name="daterange[]" value="{$title|escape}"/>
        <fieldset class="publishDateLimit" id="{$title|escape}">
          <legend>{translate text=$cluster.label}</legend>
          <label for="{$title|escape}from">{translate text='date_from'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="{$title|escape}from" id="{$title|escape}from" value="{if $dateFacets.$title.0}{$dateFacets.$title.0|escape}{/if}" />
          <label for="{$title|escape}to">{translate text='date_to'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="{$title|escape}to" id="{$title|escape}to" value="{if $dateFacets.$title.1}{$dateFacets.$title.1|escape}{/if}" />
          <div id="{$title|escape}Slider" class="dateSlider"></div>
          <input type="submit" value="{translate text='Set'}" id="{$title|escape}goButton"/>
        </fieldset>
      </form>
    {else}
      <dl class="narrowList navmenu">
        <dt>{translate text=$cluster.label}</dt>
        {foreach from=$cluster.list item=thisFacet name="narrowLoop"}
          {if $smarty.foreach.narrowLoop.iteration == 6}
          <dd id="more{$title}"><a href="#" onclick="moreFacets('{$title}'); return false;">{translate text='more'} ...</a></dd>
        </dl>
        <dl class="narrowList navmenu offscreen" id="narrowGroupHidden_{$title}">
          {/if}
          {if $thisFacet.isApplied}
            <dd>{$thisFacet.value|escape} <img src="{$path}/images/silk/tick.png" alt="Selected"/></dd>
          {else}
            <dd><a href="{$thisFacet.url|escape}">{$thisFacet.value|escape}</a> ({$thisFacet.count})</dd>
          {/if}
        {/foreach}
        {if $smarty.foreach.narrowLoop.total > 5}<dd><a href="#" onclick="lessFacets('{$title}'); return false;">{translate text='less'} ...</a></dd>{/if}
      </dl>
    {/if}
    {/foreach}
  {/if}
</div>
