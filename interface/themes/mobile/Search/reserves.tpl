<form method="GET" action="{$url}/Search/Reserves" name="searchForm" class="search">
<span class="graytitle">{translate text="Search For Items on Reserve"}</span>
{if $useReservesIndex}
  <ul class="pageitem">
    <li class="form">
      <input type="text" name="lookfor" value="{$reservesLookfor|escape}"/>
    </li>
    <li class="form">
      <input type="submit" name="submit" value="{translate text="Find"}"/>
    </li>
  </ul>
  {if $recordCount == 0}
    {include file='Search/list-none.tpl' lookfor=$reservesLookfor}
  {else}
    <ul class="pageitem">
      {foreach from=$recordSet item=record}
        <li class="menu">
          <a href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.department|escape} {$record.course|escape} {$record.instructor|escape} ({$record.bib_id|@count})</a>
        </li>
      {/foreach}
      {if $pageLinks.all}<li class="autotext"><div class="pagination">{$pageLinks.all}</div></li>{/if}
    </ul>
  {/if}
{else}
  <ul class="pageitem">
    <li class="menu">
      <select name="course">
        <option value="">{translate text="By Course"}:</option>
        {foreach from=$courseList item=courseName key=courseId}
          <option value="{$courseId|escape}">{$courseName|escape}</option>
        {/foreach}
      </select>
    </li>
    <li class="menu">
      <select name="inst">
        <option value="">{translate text="By Instructor"}:</option>
          {foreach from=$instList item=instName key=instId}
            <option value="{$instId|escape}">{$instName|escape}</option>
          {/foreach}
      </select>
      </li>
    <li class="menu">
      <select name="dept">
        <option value="">{translate text="By Department"}:</option>
        {foreach from=$deptList item=deptName key=deptId}
          <option value="{$deptId|escape}">{$deptName|escape}</option>
        {/foreach}
      </select>
    </li>
    <li class="form">
      <input type="submit" name="submit" value="{translate text='Find'}"/><br>
    </li>
  </ul>
{/if}
</form>
