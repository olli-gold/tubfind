<div class="sidegroup">
  {if $recordCount > 0}<h4> </h4>{/if}
  {if isset($checkboxFilters) && count($checkboxFilters) > 0}
      {foreach from=$checkboxFilters item=current}
        {* Die Eingrenzung auf lokale Medien soll nur eingeblendet werden, wenn GBV Central genutzt wurde *}
        {assign var="gbvcentral" value="0"}
        {foreach from=$shards item=shardEnabled key=shard}
            {if $shard=="GBV Central" && $shardEnabled=="1"}
                {assign var="gbvcentral" value="1"}
            {/if}
        {/foreach}
      {*if ($current.desc=="Only locally available items" && $gbvcentral=="1") || $current.desc!="Only locally available items"*}
        <div class="checkboxFilter{if $recordCount < 1 && !$current.selected && !$current.alwaysVisible} hide{/if}">
              <input type="checkbox" name="filter[]" value="{$current.filter|escape}"
                {if $current.selected}checked="checked"{/if} id="{$current.desc|replace:' ':''|escape}"
                onclick="document.location.href='{$current.toggleUrl|escape}';" />
              <label for="{$current.desc|replace:' ':''|escape}">{translate text=$current.desc}</label>
        </div>
      {*/if*}
      {/foreach}
  {/if}
<!--
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
-->
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
        <fieldset class="publishDateLimit round-corners" id="{$title|escape}">
          <legend>{translate text=$cluster.label}</legend>
          <label for="{$title|escape}from">{translate text='date_from'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="{$title|escape}from" id="{$title|escape}from" value="{if $dateFacets.$title.0}{$dateFacets.$title.0|escape}{/if}" />
          <br/>
          <label for="{$title|escape}to">{translate text='date_to'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="{$title|escape}to" id="{$title|escape}to" value="{if $dateFacets.$title.1}{$dateFacets.$title.1|escape}{/if}" />
          <div id="{$title|escape}Slider" class="dateSlider"></div>
          <input type="submit" value="{translate text='Set'}" id="{$title|escape}goButton"/>
        </fieldset>
      </form>
    {else}
      <dl class="narrowList navmenu collapsed">
        <dt><img id="fplus" class="facetTitleImg hidden" src="{$path}/images/facet_shuffle_plus.png" alt=""><img id="fminus" class="facetTitleImg hidden" src="{$path}/images/facet_shuffle_minus.png" alt=""> {translate text=$cluster.label}</dt>
        {foreach from=$cluster.list item=thisFacet name="narrowLoop"}
          {if $smarty.foreach.narrowLoop.iteration == 6}
          <dd id="more{$title}" {if $cluster.hide == 1}class="offscreen"{/if}><a href="#" onclick="moreFacets('{$title}'); return false;">{translate text='more'} ...</a></dd>
        </dl>
        <dl class="narrowList navmenu offscreen" id="narrowGroupHidden_{$title}">
          {/if}
          <dd class="facetList"  id="facetList_material_access">
          {if $thisFacet.isApplied}
            <dl class="facet applied facetHoverCSS">
            <dd {if $cluster.hide == 1}class="offscreen"{/if} ><!--<img src="{$path}/images/silk/tick.png" alt="selected"/>--><a href="{$thisFacet.url|escape}"><span class="sprite-checkbox checked"></span> {translate text=$thisFacet.value|escape}</a></dd>
          {else}
            <dl class="facet facetHoverCSS">
            <dd {if $cluster.hide == 1}class="offscreen"{/if}><a href="{$thisFacet.url|escape}" 
            {if $thisFacet.value|cat:'_hint'|translate != $thisFacet.value|cat:'_hint'}
                title="{$thisFacet.value|cat:'_hint'|translate}"
            {/if}
            ><span class="sprite-checkbox unchecked"></span> {$thisFacet.value|escape}</a> ({$thisFacet.count})</dd>
          {/if}
          </dl>
        {/foreach}
        {if $smarty.foreach.narrowLoop.total > 5}<dd {if $cluster.hide == 1}class="offscreen"{/if}><a href="#" onclick="lessFacets('{$title}'); return false;">{translate text='less'} ...</a></dd>{/if}
        </dd>
      </dl>
    {/if}
    {/foreach}
  {/if}
</div>

{if $dbrenabled == 1}
<div id="dbRecommender">
    <h4>{translate text="Databases"}</h4>
    <span class="ajax_availability" id="dbrWait">{translate text='Loading'}...</span>
</div>
{/if}