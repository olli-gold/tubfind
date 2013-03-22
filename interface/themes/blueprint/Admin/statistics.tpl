<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="Statistics"}</h1>

  <h2>{translate text="Executive Summary"}</h2>
  <table class="citation">
  <tr>
    <th>{translate text="Total Searches"}: </th>
    <td>{$searchCount}</td>
  </tr>
  <tr>
    <th>{translate text="0 Hit Searches"}: </th>
    <td>{$nohitCount}</td>
  </tr>
  <tr>
    <th>{translate text="Total Record Views"}: </th>
    <td>{$recordViews}</td>
  </tr>
  </table>

  {* This section was introduced in r854, but code has never been written
   * to populate it; commenting it out for now to prevent confusion.
  <h2>Average Usage</h2>
  <table class="citation">
  <tr>
    <th>{translate text="Per Day"}: </th>
    <td>{$avgPerDay}</td>
  </tr>
  <tr>
    <th>{translate text="Per Week"}: </th>
    <td>{$avgPerWeek}</td>
  </tr>
  <tr>
    <th>{translate text="Per Month"}: </th>
    <td>{$avgPerMonth}</td>
  </tr>
  </table>
   *}

  <h2>{translate text="Top Search Terms"}</h2>
  <ul>
  {foreach from=$termList item=term}
    <li>({$term.1}) {$term.0|escape}</li>
  {foreachelse}
    <li>{translate text="No Searches"}</li>
  {/foreach}
  </ul>

  <h2>{translate text="Top Records"}</h2>
  <ul>
  {foreach from=$recordList item=term}
    <li>({$term.1}) {$term.0|escape}</li>
  {foreachelse}
    <li>{translate text="No Record Views"}</li>
  {/foreach}
  </ul>
  
  <h2>{translate text="Usage Summary"}</h2>
  <table class="citation">
  <tr>
    <th>{translate text="Top Browsers"}</th>
    <th>{translate text="Top Users"}</th>
  </tr>
  <tr>
    <td>
      <ul>
      {foreach from=$browserList item=term}
        <li>({$term.1}) {$term.0|escape}</li>
      {/foreach}
      </ul>
    </td>
    <td>
      <ul>
      {foreach from=$ipList item=term}
        <li>({$term.1}) {$term.0|escape}</li>
      {/foreach}
      </ul>
    </td>
  </tr>
  </table>
</div>

<div class="clear"></div>
