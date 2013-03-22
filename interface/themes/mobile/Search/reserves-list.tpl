{if !$recordCount}
  <ul class="pageitem">
    <li>{translate text="course_reserves_empty_list"}</li>
  </ul>
{else}
  {* Display just like regular search results: *}
  {include file="Search/list.tpl"}
{/if}