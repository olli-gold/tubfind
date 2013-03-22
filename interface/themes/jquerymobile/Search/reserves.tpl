<div data-role="page" id="Search-reserves">
  {include file="header.tpl"}
  <div data-role="content">
    <h3>{translate text='Search For Items on Reserve'}</h3>
    {if $useReservesIndex}
      <form method="get" action="{$path}/Search/Reserves" data-ajax="false">
        <div data-role="fieldcontain">
          <label class="offscreen" for="reservesSearchForm_lookfor">
              {translate text="Search"}
          </label>
          <input type="search" placeholder="{translate text='Search'}" name="lookfor" id="reservesSearchForm_lookfor" value="{$reservesLookfor|escape}"/>
        </div>
        <div data-role="fieldcontain">
          <input type="submit" name="submit" value="{translate text="Find"}"/>
        </div>
      </form>
      {if $recordCount == 0}
        <p>{translate text='nohit_prefix'} - <strong>{$reservesLookfor|escape}</strong> - {translate text='nohit_suffix'}</p>
      {else}
        <ul class="results" data-role="listview" data-split-icon="plus" data-split-theme="c">
          {foreach from=$recordSet item=record}
            <li>
              <a rel="external" href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.department|escape} {$record.course|escape} {$record.instructor|escape} ({$record.bib_id|@count})</a>
            </li>
          {/foreach}
        </ul>
        <div data-role="controlgroup" data-type="horizontal" align="center">
          {if $pageLinks.back}
            {$pageLinks.back|replace:' href=':' class="prevLink" data-role="button" data-rel="back" href='}
          {/if}
          {if $pageLinks.next}
            {$pageLinks.next|replace:' href=':' class="nextLink" rel="external" data-role="button" href='}
          {/if}
        </div>
      {/if}
    {else}
      <form method="get" action="{$path}/Search/Reserves" data-ajax="false">
        <div data-role="fieldcontain">
          <label for="reserves_by_course">{translate text='By Course'}:</label>
          <select name="course" id="reserves_by_course">
            <option value=""></option>
            {foreach from=$courseList item=courseName key=courseId}
              <option value="{$courseId|escape}">{$courseName|escape}</option>
            {/foreach}
          </select>
        </div>
        <div data-role="fieldcontain">
          <input type="submit" name="submit" value="{translate text='Find'}"/>
        </div>
      </form>

      <form method="get" action="{$path}/Search/Reserves" data-ajax="false">
        <div data-role="fieldcontain">
          <label for="reserves_by_inst">{translate text='By Instructor'}:</label>
          <select name="inst" id="reserves_by_inst">
            <option value=""></option>
            {foreach from=$instList item=instName key=instId}
              <option value="{$instId|escape}">{$instName|escape}</option>
            {/foreach}
          </select>
        </div>
        <div data-role="fieldcontain">
          <input type="submit" name="submit" value="{translate text='Find}"/>
        </div>
      </form>

      <form method="get" action="{$path}/Search/Reserves" data-ajax="false">
        <div data-role="fieldcontain">
          <label for="reserves_by_dept">{translate text='By Department'}:</label>
          <select name="dept" id="reserves_by_dept">
            <option value=""></option>
            {foreach from=$deptList item=deptName key=deptId}
              <option value="{$deptId|escape}">{$deptName|escape}</option>
            {/foreach}
          </select>
        </div>
        <div data-role="fieldcontain">
          <input type="submit" name="submit" value="{translate text='Find'}"/>
        </div>
      </form>
    {/if}
  </div>
  {include file="footer.tpl"}
</div>
