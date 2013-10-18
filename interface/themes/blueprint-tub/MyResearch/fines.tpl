<div class="span-18">
  {if $user->cat_username}
    <h3>{translate text='Your Fines'}</h3>
    {if ($rawFinesData)}
        <table width="100%" cellspacing="1" cellpadding="4">
            <tr>
                <th>{translate text="Title"}</th>
                <th>{translate text="Checked Out"}</th>
                <th>{translate text="Due Date"}</th>
                <th>{translate text="Fine"}</th>
                <th>{translate text="Fee"}</th>
                <th>{translate text="Balance"}</th>
            </tr>
            {foreach from=$rawFinesData item=fineValue}
                <tr>
                    <td>{$fineValue.title}</td>
                    <td>{$fineValue.checkout}</td>
                    <td>{$fineValue.duedate}</td>
                    <td>{$fineValue.fine}</td>
                    <td>{$fineValue.amount}</td>
                    <td>{$fineValue.balance}</td>
                </tr>
            {/foreach}
        </table>
    {else}
        {translate text="You do not have any fines"}
    {/if}
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>
<div class="span-5 last">
  {include file="MyResearch/menu.tpl"}
</div>
<div class="clear"></div>