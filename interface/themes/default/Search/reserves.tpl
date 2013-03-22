<div id="bd">
  <div id="yui-main" class="content">
    <div class="yui-b first">
      <b class="btop"><b></b></b>
      <div class="resulthead"><h3>{translate text='Search For Items on Reserve'}</h3></div>
      <div class="page">
        {if $useReservesIndex}
          <form method="get" action="{$path}/Search/Reserves" name="searchForm" class="search">
            <div class="hiddenLabel"><label for="reservesSearchForm_lookfor">{translate text="Search For"}:</label></div>
            {* TODO: make autocomplete work here *}
            <input id="reservesSearchForm_lookfor" type="text" name="lookfor" size="30" value="{$reservesLookfor|escape:"html"}">
            <input type="submit" name="submit" value="{translate text="Find"}"/>
          </form>

          {* Listing Options *}
          <div class="yui-gc resulthead">
            <div class="yui-u first">
              {if $recordCount}
                {translate text="Showing"}
                <strong>{$recordStart|escape}</strong> - <strong>{$recordEnd|escape}</strong>
                {translate text='of'} <strong>{$recordCount|escape}</strong>
                {if $searchType == 'Reserves'}{translate text='for search'}: <strong>'{$reservesLookfor|escape:"html"}'</strong>,{/if}
              {/if}
              {translate text='query time'}: {$qtime|escape}s
            </div>

            <div class="yui-u toggle">
              <form action="{$path}/Search/SortResults" method="post">
                <label for="sort_options_1">{translate text='Sort'}</label>
                <select id="sort_options_1" name="sort" class="jumpMenu">
                  {foreach from=$sortList item=sortData key=sortLabel}
                    <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected="selected"{/if}>{translate text=$sortData.desc}</option>
                  {/foreach}
                </select>
                <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
              </form>
            </div>
          </div>
          {* End Listing Options *}

          {if $recordCount == 0}
            <div class="resulthead"><h3>{translate text='nohit_heading'}</h3></div>
            <p class="error">{translate text='nohit_prefix'} - <strong>{$reservesLookfor|escape:"html"}</strong> - {translate text='nohit_suffix'}</p>

            {if $parseError}
              <p class="error">{translate text='nohit_parse_error'}</p>
            {/if}

            {if $spellingSuggestions}
            <div class="correction">{translate text='nohit_spelling'}:<br/>
              {foreach from=$spellingSuggestions item=details key=term name=termLoop}
                {$term|escape} &raquo; {foreach from=$details.suggestions item=data key=word name=suggestLoop}<a href="{$data.replace_url|escape}">{$word|escape}</a>{if $data.expand_url} <a href="{$data.expand_url|escape}"><img src="{$path}/images/silk/expand.png" alt="{translate text='spell_expand_alt'}"/></a> {/if}{if !$smarty.foreach.suggestLoop.last}, {/if}{/foreach}{if !$smarty.foreach.termLoop.last}<br/>{/if}
              {/foreach}
            </div>
            {/if}
          {else}
            <table class="datagrid reserves">
            <tr>
              <th class="department">{translate text='Department'}</th>
              <th class="course">{translate text='Course'}</th>
              <th class="instructor">{translate text='Instructor'}</th>
              <th class="items">{translate text='Items'}</th>
            </tr>
            {foreach from=$recordSet item=record}
            <tr>
              <td class="department"><a href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.department|escape}</a></td>
              <td class="course"><a href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.course|escape}</a></td>
              <td class="instructor"><a href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.instructor|escape}</a></td>
              <td class="items">{$record.bib_id|@count}</td>
            </tr>
            {/foreach}
            </table>
            {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
          {/if}
        {else}
          <table class="citation">
            <tr>
              <th align="right">{translate text='By Course'}: </th>
              <td>
                <form method="GET" action="{$url}/Search/Reserves" class="search">
                  <select name="course">
                    <option></option>
                    {foreach from=$courseList item=courseName key=courseId}
                      <option value="{$courseId|escape}">{$courseName|escape}</option>
                    {/foreach}
                  </select>
                  &nbsp;&nbsp;<input type="submit" name="submit" value="{translate text='Find'}">
                  </form>
              </td>
            </tr>
            <tr>
              <th align="right">{translate text='By Instructor'}: </th>
              <td>
                <form method="GET" action="{$url}/Search/Reserves" class="search">
                  <select name="inst">
                    <option></option>
                    {foreach from=$instList item=instName key=instId}
                      <option value="{$instId|escape}">{$instName|escape}</option>
                    {/foreach}
                  </select>
                  &nbsp;&nbsp;<input type="submit" name="submit" value="{translate text='Find}">
                </form>
              </td>
            </tr>
            <tr>
              <th align="right">{translate text='By Department'}: </th>
              <td>
                <form method="GET" action="{$url}/Search/Reserves" class="search">
                  <select name="dept">
                    <option></option>
                    {foreach from=$deptList item=deptName key=deptId}
                      <option value="{$deptId|escape}">{$deptName|escape}</option>
                    {/foreach}
                  </select>
                  &nbsp;&nbsp;<input type="submit" name="submit" value="{translate text='Find'}">
                </form>
              </td>
            </tr>
          </table>
        {/if}
      </div>
      <b class="bbot"><b></b></b>
    </div>
  </div>
  {* Narrow Search Options *}
  <div class="yui-b">
    {if $sideRecommendations}
      {foreach from=$sideRecommendations item="recommendations"}
        {include file=$recommendations}
      {/foreach}
    {/if}
  </div>
  {* End Narrow Search Options *}
</div>