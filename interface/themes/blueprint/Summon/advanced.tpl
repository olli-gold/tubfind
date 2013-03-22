<form method="get" action="{$url}/Summon/Search" id="advSearchForm" name="searchForm" class="search">
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

      {if !empty($checkboxFilters)}
        <h3>{translate text='Limit To'}</h3>
        {foreach from=$checkboxFilters item=current}
          <input type="checkbox" name="filter[]" value="{$current.filter|escape}"
            id="{$current.desc|replace:' ':''|escape}"
            {if $current.selected}checked="checked"{/if} />
          <label for="{$current.desc|replace:' ':''|escape}">{translate text=$current.desc}</label>
          <br/>
        {/foreach}
        <br/>
      {/if}

      {if $dateRangeLimit}
        {* Load the publication date slider UI widget *}
        {js filename="pubdate_slider.js"}
        <input type="hidden" name="daterange[]" value="PublicationDate"/>
        <fieldset class="PublicationDateLimit span-5" id="PublicationDate">
          <legend>{translate text='adv_search_year'}</legend>
          <label for="PublicationDatefrom">{translate text='date_from'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="PublicationDatefrom" id="PublicationDatefrom" value="{if $dateRangeLimit.0}{$dateRangeLimit.0|escape}{/if}" />
          <label for="PublicationDateto">{translate text='date_to'}:</label>
          <input type="text" size="4" maxlength="4" class="yearbox" name="PublicationDateto" id="PublicationDateto" value="{if $dateRangeLimit.1}{$dateRangeLimit.1|escape}{/if}" />
          <div id="PublicationDateSlider" class="dateSlider"></div>
        </fieldset>
        <div class="clear"></div>
      {/if}

      {if !empty($checkboxFilters) || $dateRangeLimit}
        <input type="submit" name="submit" value="{translate text="Find"}">
      {/if}
    </div>
    {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
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
      <a href="{$url}/Help/Home?topic=advsearch" class="advsearchHelp">{translate text="Help with Advanced Search"}</a><br />
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