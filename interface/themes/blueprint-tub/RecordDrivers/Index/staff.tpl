<table class="citation">
  {foreach from=$details key='field' item='values'}
    <tr>
      <th>{$field|escape}</th>
      <td>
        {foreach from=$values item='value'}
          {$value|escape}<br />
        {/foreach}
      </td>
    </tr>
  {/foreach}
</table>