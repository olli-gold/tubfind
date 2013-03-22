<div data-role="page" id="MyResearch-fines">
  {include file="header.tpl"}
  <div data-role="content">
    {if $user->cat_username}
      <h3>{translate text='Your Fines'}</h3>
      {if !empty($rawFinesData)}
        <ul class="results fines" data-role="listview">
        {foreach from=$rawFinesData item=record name="recordLoop"}
          <li>
            <a rel="external" href="{$path}/Record/{$record.id|escape}">
              <div class="result">
                <h3>
                {if empty($record.title)}
                  {translate text='Title not available'}
                {else}
                  {$record.title|trim:'/:'|escape}
                {/if}
                </h3>
                <span class="ui-li-aside">{$record.balance/100|safe_money_format|escape}</span>
                <p><strong>{translate text='Due Date'}</strong>: {$record.duedate|escape}</p>
                <p><strong>{translate text='Checked Out'}</strong>: {$record.checkout|escape}</p> 
                <p><strong>{translate text='Fine'}</strong>: {$record.fine|escape}</p>
                <p><strong>{translate text='Fee'}</strong>: {$record.amount/100|safe_money_format|escape}</p>
              </div>
            </a>
          </li>
        {/foreach}
        </ul>
      {else}
        <p>{translate text='You do not have any fines'}.</p>
      {/if}
    {else}
      {include file="MyResearch/catalog-login.tpl"}
    {/if}
  </div>
  {include file="footer.tpl"}
</div>
