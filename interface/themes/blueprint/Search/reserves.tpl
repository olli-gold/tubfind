<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  <h3>{translate text='Search For Items on Reserve'}</h3>
  {if $useReservesIndex}
    <form method="get" action="{$path}/Search/Reserves" name="searchForm" class="search">
      <label for="reservesSearchForm_lookfor" class="offscreen">{translate text="Your search terms"}</label>
      <input id="reservesSearchForm_lookfor" type="text" name="lookfor" size="40" value="{$reservesLookfor|escape}" {if $autocomplete}class="autocomplete type:Reserves"{/if} />
      <input type="submit" name="submit" value="{translate text="Find"}"/>
    </form>
    <script type="text/javascript">$("#reservesSearchForm_lookfor").focus()</script>

    {* Listing Options *}
    <div class="resulthead">
      <div class="floatleft">
        {if $recordCount}
          {translate text="Showing"}
          <strong>{$recordStart|escape}</strong> - <strong>{$recordEnd|escape}</strong>
          {translate text='of'} <strong>{$recordCount|escape}</strong>
          {if $searchType == 'Reserves'}{translate text='for search'}: <strong>'{$reservesLookfor|escape:"html"}'</strong>,{/if}
        {/if}
        {translate text='query time'}: {$qtime|escape}s
      </div>

      <div class="floatright">
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
      <div class="clear"></div>
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
    <form method="get" action="{$url}/Search/Reserves" name="searchForm" class="search">
      <label class="span-3" for="reserves_by_course">{translate text='By Course'}:</label>
      <select name="course" id="reserves_by_course">
        <option></option>
        {foreach from=$courseList item=courseName key=courseId}
          <option value="{$courseId|escape}">{$courseName|escape}</option>
        {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text='Find'}"/>
      <div class="clear"></div>
    </form>

    <form method="get" action="{$url}/Search/Reserves" name="searchForm" class="search">
      <label class="span-3" for="reserves_by_inst">{translate text='By Instructor'}:</label>
      <select name="inst" id="reserves_by_inst">
        <option></option>
        {foreach from=$instList item=instName key=instId}
          <option value="{$instId|escape}">{$instName|escape}</option>
        {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text='Find}"/>
      <div class="clear"></div>
    </form>

    <form method="get" action="{$url}/Search/Reserves" name="searchForm" class="search">
      <label class="span-3" for="reserves_by_dept">{translate text='By Department'}:</label>
      <select name="dept" id="reserves_by_dept">
        <option></option>
        {foreach from=$deptList item=deptName key=deptId}
          <option value="{$deptId|escape}">{$deptName|escape}</option>
        {/foreach}
      </select>
      <input type="submit" name="submit" value="{translate text='Find'}"/>
      <div class="clear"></div>
    </form>
  {/if}
</div>

{* Narrow Search Options *}
<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
  {if $sideRecommendations}
    {foreach from=$sideRecommendations item="recommendations"}
      {include file=$recommendations}
    {/foreach}
  {/if}
</div>
{* End Narrow Search Options *}

<div class="clear"></div>