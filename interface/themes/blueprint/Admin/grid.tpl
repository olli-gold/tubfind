<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="Delete Suppressed"}</h1>
  <table class="datagrid">
  <tr>
    <th>{translate text="Record ID"}</th>
    <th>{translate text="Status"}</th>
  </tr>
  {foreach from=$resultList item=result}
  <tr>
    <td>{$result.id|escape}</td>
    <td>{if $result.status}:){else}X{/if}</td>
  </tr>
  {/foreach}
  </table>
</div>

<div class="clear"></div>
