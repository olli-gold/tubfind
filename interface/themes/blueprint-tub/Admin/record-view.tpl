<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="View Record"}</h1>

  {if $record}
    <table class="citation">
    {foreach from=$record item=value key=field}
      {if is_array($value)}
        {foreach from=$value item=current}
        <tr>
          <th>{$field}: </th>
          <td>
            <div class="fieldValue">{$current|regex_replace:"/[\x1D\x1E\x1F]/":""|escape}</div>
          </td>
        </tr>
        {/foreach}
      {else}
        <tr>
          <th>{$field}: </th>
          <td>
            <div class="fieldValue">{$value|regex_replace:"/[\x1D\x1E\x1F]/":""|escape}</div>
          </td>
        </tr>
      {/if}
    {/foreach}
    </table>
  {else}
    <p>Could not load record {$recordId|escape}.</p>
  {/if}
</div>

<div class="clear"></div>
