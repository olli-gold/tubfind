<div class="sidegroup">
  {if $recordCount > 0}<h4>{translate text='Narrow Search'}</h4>{/if}
  {if isset($checkboxFilters) && count($checkboxFilters) > 0}
  <p>
    <table>
      {foreach from=$checkboxFilters item=current name=boxes}
          <tr{if $recordCount < 1 && !$current.selected && !$current.alwaysVisible} style="display: none;"{/if}>
            <td style="vertical-align:top; padding: 3px;">
              <input type="checkbox" id="checkFilter{$smarty.foreach.boxes.index}" name="filter[]" value="{$current.filter|escape}"
                {if $current.selected}checked="checked"{/if}
                onclick="document.location.href='{$current.toggleUrl|escape}';" />
            </td>
            <td>
              <label for="checkFilter{$smarty.foreach.boxes.index}">{translate text=$current.desc}</label><br />
            </td>
          </tr>
      {/foreach}
    </table>
  </p>
  {/if}
  {if $filterList}
    <strong>{translate text='Remove Filters'}</strong>
    <ul class="filters">
    {foreach from=$filterList item=filters key=field}
        {foreach from=$filters item=filter}
      <li>{translate text=$field}: {$filter.display|escape} <a href="{$filter.removalUrl|escape}"><img src="{$path}/images/silk/delete.png" alt="Delete"></a></li>
        {/foreach}
    {/foreach}
    </ul>
  {/if}
  {if $sideFacetSet && $recordCount > 0}
    {foreach from=$sideFacetSet item=cluster key=title}
      <dl class="narrowList navmenu narrow_begin">
        <dt>{translate text=$cluster.label}</dt>
        {if isset($dateFacets.$title)}
          {* Load the publication date slider UI widget *}
          {js filename="yui/slider-min.js"}
          {js filename="pubdate_slider.js"}
          <dd>
            <form name='{$title|escape}Filter' id='{$title|escape}Filter'>
              <input type="hidden" name="daterange[]" value="{$title|escape}"/>
              <label for="{$title|escape}from" class='yearboxlabel'>{translate text='date_from'}:</label>
              <input type="text" size="4" maxlength="4" class="yearbox" name="{$title|escape}from" id="{$title|escape}from" value="{$dateFacets.$title.0|escape}" />
              <label for="{$title|escape}to" class='yearboxlabel'>{translate text='date_to'}:</label>
              <input type="text" size="4" maxlength="4" class="yearbox" name="{$title|escape}to" id="{$title|escape}to" value="{$dateFacets.$title.1|escape}" />
              {foreach from=$smarty.get item=paramValue key=paramName}
                {if is_array($smarty.get.$paramName)}
                  {foreach from=$smarty.get.$paramName item=paramValue2}
                    {if strpos($paramValue2, $title) !== 0}
                      <input type="hidden" name="{$paramName}[]" value="{$paramValue2|escape}" />
                    {/if}
                  {/foreach}
                {else}
                  {if (strpos($paramName, $title) !== 0)
                      && (strpos($paramName, 'module') !== 0)
                      && (strpos($paramName, 'action') !== 0)
                      && (strpos($paramName, 'page') !== 0)}
                    <input type="hidden" name="{$paramName}" value="{$paramValue|escape}" />
                  {/if}
                {/if}
              {/foreach}
              <div id="{$title|escape}Slider" class="yui-h-slider dateSlider" title="{translate text='Range slider'}" style="display:none;">
                  <div id="{$title|escape}slider_min_thumb" class="yui-slider-thumb"><img src="{$path}/images/yui/left-thumb.png"></div>
                  <div id="{$title|escape}slider_max_thumb" class="yui-slider-thumb"><img src="{$path}/images/yui/right-thumb.png"></div>
              </div>
              <input type="submit" value="{translate text='Set'}" id="{$title|escape}goButton">
            </form>
          </dd>
        {else}
          {foreach from=$cluster.list item=thisFacet name="narrowLoop"}
            {if $smarty.foreach.narrowLoop.iteration == 6}
            <dd id="more{$title}"><a href="#" onClick="moreFacets('{$title}'); return false;">{translate text='more'} ...</a></dd>
          </dl>
          <dl class="narrowList navmenu narrowGroupHidden" id="narrowGroupHidden_{$title}">
            {/if}
            {if $thisFacet.isApplied}
              <dd>{$thisFacet.value|escape} <img src="{$path}/images/silk/tick.png" alt="Selected"></dd>
            {else}
              <dd><a href="{$thisFacet.url|escape}">{$thisFacet.value|escape}</a> ({$thisFacet.count})</dd>
            {/if}
          {/foreach}
          {if $smarty.foreach.narrowLoop.total > 5}<dd><a href="#" onClick="lessFacets('{$title}'); return false;">{translate text='less'} ...</a></dd>{/if}
        {/if}
      </dl>
    {/foreach}
  {/if}
</div>
