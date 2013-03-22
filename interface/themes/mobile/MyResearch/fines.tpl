    {if $user->cat_username}
      {if empty($rawFinesData)}
        {translate text='You do not have any fines'}
      {else}
        <table class="datagrid fines" summary="{translate text='Your Fines'}">
        <tr>
          <th>{translate text='Title'}</th>
          <th>{translate text='Checked Out'}</th>
          <th>{translate text='Due Date'}</th>
          <th>{translate text='Fine'}</th>
          <th>{translate text='Fee'}</th>
          <th>{translate text='Balance'}</th>
        </tr>
        {foreach from=$rawFinesData item=record}
          <tr>
            <td>
              {if empty($record.title)}
                {translate text='not_applicable'}
              {else}
                <a href="{$path}/Record/{$record.id|escape}">{$record.title|trim:'/:'|escape}</a>
              {/if}
            </td>
            <td>{$record.checkout|escape}</td>
            <td>{$record.duedate|escape}</td>
            <td>{$record.fine|escape}</td>
            <td>{$record.amount/100.00|safe_money_format|escape}</td>
            <td>{$record.balance/100.00|safe_money_format|escape}</td>
          </tr>
        {/foreach}
        </table>
      {/if}
    {else}
      {include file="MyResearch/catalog-login.tpl"}
    {/if}

  {include file="MyResearch/menu.tpl"}
