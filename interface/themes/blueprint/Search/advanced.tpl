<form method="get" action="{$url}/Search/Results" id="advSearchForm" name="searchForm" class="search">
  <div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
    <h3>{translate text='Advanced Search'}</h3>
    <div class="advSearchContent">
      {if $editErr}
      {assign var=error value="advSearchError_$editErr"}
        <div class="error">{translate text=$error}</div>
      {/if}
  
      <div id="groupJoin" class="searchGroups">
        <div class="searchGroupDetails">
          <label for="groupJoinOptions">{translate text="search_match"}:</label>
          <select id="groupJoinOptions" name="join">
            <option value="AND">{translate text="group_AND"}</option>
            <option value="OR"{if $searchDetails and $searchDetails.0.join == 'OR'} selected="selected"{/if}>{translate text="group_OR"}</option>
          </select>
        </div>
        <strong>{translate text="search_groups"}</strong>:
      </div>
  
      {* An empty div. This is the target for the javascript that builds this screen *}
      <div id="searchHolder">
        {* fallback to a fixed set of search groups/fields if JavaScript is turned off *}
        <noscript>
        {if $searchDetails}
          {assign var=numGroups value=$searchDetails|@count}
        {/if}
        {if $numGroups < 3}{assign var=numGroups value=3}{/if}
        {section name=groups loop=$numGroups}
          {assign var=groupIndex value=$smarty.section.groups.index}
          <div class="group group{$groupIndex%2}" id="group{$groupIndex}">
            <div class="groupSearchDetails">
              <div class="join">
                <label for="search_bool{$groupIndex}">{translate text="search_match"}:</label>
                <select id="search_bool{$groupIndex}" name="bool{$groupIndex}[]">
                  <option value="AND"{if $searchDetails and $searchDetails.$groupIndex.group.0.bool == 'AND'} selected="selected"{/if}>{translate text="search_AND"}</option>
                  <option value="OR"{if $searchDetails and $searchDetails.$groupIndex.group.0.bool == 'OR'} selected="selected"{/if}>{translate text="search_OR"}</option>
                  <option value="NOT"{if $searchDetails and $searchDetails.$groupIndex.group.0.bool == 'NOT'} selected="selected"{/if}>{translate text="search_NOT"}</option>
                </select>
              </div>
            </div>
            <div class="groupSearchHolder" id="group{$groupIndex}SearchHolder">
            {if $searchDetails}
              {assign var=numRows value=$searchDetails.$groupIndex.group|@count}
            {/if}
            {if $numRows < 3}{assign var=numRows value=3}{/if}
            {section name=rows loop=$numRows}
              {assign var=rowIndex value=$smarty.section.rows.index}
              {if $searchDetails}{assign var=currRow value=$searchDetails.$groupIndex.group.$rowIndex}{/if}
              <div class="advRow">
                <div class="label">
                  <label {if $rowIndex > 0}class="offscreen" {/if}for="search_lookfor{$groupIndex}_{$rowIndex}">{translate text="adv_search_label"}:</label>&nbsp;
                </div>
                <div class="terms">
                  <input id="search_lookfor{$groupIndex}_{$rowIndex}" type="text" value="{if $currRow}{$currRow.lookfor|escape}{/if}" size="50" name="lookfor{$groupIndex}[]"/>
                </div>
                <div class="field">
                  <label for="search_type{$groupIndex}_{$rowIndex}">{translate text="in"}</label>
                  <select id="search_type{$groupIndex}_{$rowIndex}" name="type{$groupIndex}[]">
                  {foreach from=$advSearchTypes item=searchDesc key=searchVal}
                    <option value="{$searchVal}"{if $currRow and $currRow.field == $searchVal} selected="selected"{/if}>{translate text=$searchDesc}</option>
                  {/foreach}
                  </select>
                </div>
                <span class="clearer"></span>
              </div>
            {/section}
            </div>
          </div>
        {/section}
        </noscript>
      </div>
  
      <a id="addGroupLink" href="#" class="add offscreen" onclick="addGroup(); return false;">{translate text="add_search_group"}</a>
  
      <br/><br/>
  
      <input type="submit" name="submit" value="{translate text="Find"}"/>
      {if $facetList}
        <h3>{translate text='Limit To'}</h3>
        {foreach from=$facetList item="list" key="label"}
        <div class="{if $label=='Call Number'}span-7{else}span-4{/if}">
          <label class="displayBlock" for="limit_{$label|replace:' ':''|escape}">{translate text=$label}:</label>
          <select id="limit_{$label|replace:' ':''|escape}" name="filter[]" multiple="multiple" size="10">
            {foreach from=$list item="value" key="display"}
              <option value="{$value.filter|escape}"{if $value.selected} selected="selected"{/if}>{$display|escape}</option>
            {/foreach}
          </select>
        </div>
        {/foreach}
        <div class="clear"></div>
      {/if}
      {if $illustratedLimit}
        <fieldset class="span-4">
          <legend>{translate text="Illustrated"}:</legend>
          {foreach from=$illustratedLimit item="current"}
            <input id="illustrated_{$current.value|escape}" type="radio" name="illustration" value="{$current.value|escape}"{if $current.selected} checked="checked"{/if}/>
            <label for="illustrated_{$current.value|escape}">{translate text=$current.text}</label><br/>
          {/foreach}
        </fieldset>
      {/if}
      {if $limitList|@count gt 1}
        <fieldset class="span-4">
          <legend>{translate text='Results per page'}</legend>
          <select id="limit" name="limit">
            {foreach from=$limitList item=limitData key=limitLabel}
              {* If a previous limit was used, make that the default; otherwise, use the "default default" *}
              {if $lastLimit}
                <option value="{$limitData.desc|escape}"{if $limitData.desc == $lastLimit} selected="selected"{/if}>{$limitData.desc|escape}</option>
              {else}
                <option value="{$limitData.desc|escape}"{if $limitData.selected} selected="selected"{/if}>{$limitData.desc|escape}</option>
              {/if}
            {/foreach}
          </select>
        </fieldset>
      {/if}
      {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
      {if $dateRangeLimit}
        {* Load the publication date slider UI widget *}
        {js filename="pubdate_slider.js"}
        <input type="hidden" name="daterange[]" value="publishDate"/>
        <fieldset class="publishDateLimit span-5" id="publishDate">
          <legend>{translate text='adv_search_year'}</legend>
          <label for="publishDatefrom">{translate text='date_from'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="publishDatefrom" id="publishDatefrom" value="{if $dateRangeLimit.0}{$dateRangeLimit.0|escape}{/if}" />
          <label for="publishDateto">{translate text='date_to'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="publishDateto" id="publishDateto" value="{if $dateRangeLimit.1}{$dateRangeLimit.1|escape}{/if}" />
          <div id="publishDateSlider" class="dateSlider"></div>
        </fieldset>
      {/if}
      <div class="clear"></div>
      <input type="submit" name="submit" value="{translate text="Find"}"/>
    </div>
  </div>
  
  <div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
    {if $searchFilters}
      <div class="filterList">
        <h3>{translate text="adv_search_filters"}<br/><span>({translate text="adv_search_select_all"} <input type="checkbox" checked="checked" onclick="filterAll(this, 'advSearchForm');" />)</span></h3>
        {foreach from=$searchFilters item=data key=field}
        <div>
          <h4>{translate text=$field}</h4>
          <ul>
            {foreach from=$data item=value}
            <li><input type="checkbox" checked="checked" name="filter[]" value='{$value.field|escape}:"{$value.value|escape}"' /> {$value.display|escape}</li>
            {/foreach}
          </ul>
        </div>
        {/foreach}
      </div>
    {/if}
    <div class="sidegroup">
      <h4>{translate text="Search Tips"}</h4>
      <a href="{$url}/Help/Home?topic=searchadv" class="advsearchHelp">{translate text="Help with Advanced Search"}</a><br />
      <a href="{$url}/Help/Home?topic=search" class="searchHelp">{translate text="Help with Search Operators"}</a>
    </div>
  </div>

  <div class="clear"></div>
</form>

{* Step 1: Define our search arrays so they are usuable in the javascript *}
<script type="text/javascript">
//<![CDATA[
    var searchFields = new Array();
    {foreach from=$advSearchTypes item=searchDesc key=searchVal}
    searchFields["{$searchVal}"] = "{translate text=$searchDesc}";
    {/foreach}
    var searchJoins = new Array();
    searchJoins["AND"]  = "{translate text="search_AND"}";
    searchJoins["OR"]   = "{translate text="search_OR"}";
    searchJoins["NOT"]  = "{translate text="search_NOT"}";
    var addSearchString = "{translate text="add_search"}";
    var searchLabel     = "{translate text="adv_search_label"}";
    var searchFieldLabel = "{translate text="in"}";
    var deleteSearchGroupString = "{translate text="del_search"}";
    var searchMatch     = "{translate text="search_match"}";
    var searchFormId    = 'advSearchForm';
//]]>
</script>
{* Step 2: Call the javascript to make use of the above *}
{js filename="advanced_search.js"}
{* Step 3: Build the page *}
<script type="text/javascript">
//<![CDATA[
  {if $searchDetails}
    {foreach from=$searchDetails item=searchGroup}
      {foreach from=$searchGroup.group item=search name=groupLoop}
        {if $smarty.foreach.groupLoop.iteration == 1}
    var new_group = addGroup('{$search.lookfor|escape:"javascript"}', '{$search.field|escape:"javascript"}', '{$search.bool}');
        {else}
    addSearch(new_group, '{$search.lookfor|escape:"javascript"}', '{$search.field|escape:"javascript"}');
        {/if}
      {/foreach}
    {/foreach}
  {else}
    var new_group = addGroup();
    addSearch(new_group);
    addSearch(new_group);
  {/if}
  // show the add group link
  $("#addGroupLink").removeClass("offscreen");
//]]>
</script>