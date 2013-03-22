<div class="span-18">
  <h3>{translate text='Search For Items on Reserve'}</h3>
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
</div>

<div class="span-5 last">
</div>

<div class="clear"></div>      