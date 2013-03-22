<table class="summHoldings">
<tbody>
<tr>
  <th class="locationColumn">{translate text='Location'}</th>
  <th class="callnumColumn">{translate text='Call Number'}</th>
  <th class="statusColumn">{translate text='Status'}</th>
</tr>
{foreach from=$statusItems item=item name="itemLoop"}
  {if $smarty.foreach.itemLoop.iteration < 6}
  <tr>
    <td class="locationColumn">{$item.location|escape}</td>
    <td class="callnumColumn">{$item.callnumber|escape}</td>
    <td class="statusColumn">
      {if $item.availability}
        <span class="available">{if $item.reserve=='Y'}{translate text="On Reserve"}{else}{translate text="Available"}{/if}</span>
      {else}
        <span class="checkedout">{translate text=$item.status}</span>
      {/if}
    </td>
  </tr>
  {/if}
{/foreach}
</tbody>
</table>
{if count($statusItems) > 5}
  <a class="summHoldings" href="{$url}/Record/{$statusItems.0.id}">{math equation="x - 5" x=$statusItems|@count} {translate text='more'} ...</a>
{/if}
