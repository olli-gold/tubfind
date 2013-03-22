<div data-role="page" id="MyResearch-checkedout">
  {include file="header.tpl"}
  <div data-role="content">
    {if $user->cat_username}
      <h3>{translate text='Your Checked Out Items'}</h3>
      {if $errorMsg}
        <p class="error">{translate text=$errorMsg}</p>
      {/if}
      {if $transList}
        {if $renewForm}
          <form name="renewals" action="{$url}/MyResearch/CheckedOut" method="post" id="renewals">
           <fieldset data-type="horizontal" data-role="controlgroup">
            <input type="submit" class="button renew" name="renewSelected" value="{translate text="renew_selected"}" />
            <input type="submit" class="button renewAll" name="renewAll" value="{translate text='renew_all'}" />
           </fieldset>
        {/if}
        <ul class="results checkedout-list" data-role="listview">
        {foreach from=$transList item=resource name="recordLoop"}
          <li>
            {if !empty($resource.id)}<a rel="external" href="{$path}/Record/{$resource.id|escape}">{/if}
            <div class="result">
            {* If $resource.id is set, we have the full Solr record loaded and should display a link... *}
            {if !empty($resource.id)}
              <h3>
              {if is_array($resource.title)}
                  {$resource.title.0|trim:'/:'|escape}
              {else}
                  {$resource.title|trim:'/:'|escape}
              {/if}
              </h3>
            {* If the record is not available in Solr, perhaps the ILS driver sent us a title we can show... *}
            {elseif !empty($resource.ils_details.title)}
              <h3>{$resource.ils_details.title|trim:'/:'|escape}</h3>
            {* Last resort -- indicate that no title could be found. *}
            {else}
              <h3>{translate text='Title not available'}</h3>
            {/if}
            {if !empty($resource.author)}
              <p>{translate text='by'} {$resource.author|escape}</p>
            {/if}
            {if !empty($resource.format)}
            <p>
              {foreach from=$resource.format item=format}
                <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
              {/foreach}
            </p>
            {/if}

            {assign var="showStatus" value="show"}
            {if $renewResult[$resource.ils_details.item_id]}
              {if $renewResult[$resource.ils_details.item_id].success}
                {assign var="showStatus" value="hide"}
                <strong>{translate text='Due Date'}: {$renewResult[$resource.ils_details.item_id].new_date} {if $renewResult[$resource.ils_details.item_id].new_time}{$renewResult[$resource.ils_details.item_id].new_time|escape}{/if}</strong>
                <div class="success">{translate text='renew_success'}</div>
              {else}
                <strong>{translate text='Due Date'}: {$resource.ils_details.duedate|escape} {if $resource.ils_details.dueTime} {$resource.ils_details.dueTime|escape}{/if}</strong>
                <div class="error">{translate text='renew_fail'}{if $renewResult[$resource.ils_details.item_id].sysMessage}: {$renewResult[$resource.ils_details.item_id].sysMessage|escape}{/if}</div>
              {/if}
            {else}
              <strong>{translate text='Due Date'}: {$resource.ils_details.duedate|escape} {if $resource.ils_details.dueTime} {$resource.ils_details.dueTime|escape}{/if}</strong>
              {if $showStatus == "show"}
                {if $resource.ils_details.dueStatus == "overdue"}
                  <div class="error">{translate text="renew_item_overdue"}</div>
                {elseif $resource.ils_details.dueStatus == "due"}
                  <div class="notice">{translate text="renew_item_due"}</div>
                {/if}
              {/if}
            {/if}
            {if $resource.ils_details.renewals > 0}
                | <b>{translate text='Renewals'}:</b> {translate text=$resource.ils_details.renewals|escape}
            {elseif $resource.renewals > 0}
                | <b>{translate text='Renewals'}:</b> {translate text=$resource.renewals|escape}
            {/if}
            {if $resource.reservations != 0}
                | <b>{translate text='Reservations'}:</b> {translate text=$resource.reservations|escape}
            {/if}

            {if $showStatus == "show" && $resource.ils_details.message}
              <div class="info">{translate text=$resource.ils_details.message}</div>
            {/if}

            </div>
            {if !empty($resource.id)}</a>{/if}

            {if $resource.reservations == 0}
                <div class="info">
                {* Ist das Buch eine Fernleihe? *}
                {if substr($resource.ils_details.vb,0,6) == '830$99'}
                    {translate text='Title from interlibrary loan'}
                {else}
                    <a href="{$url}/MyResearch/Renew?VB={$resource.ils_details.vb|escape}">{translate text='Renew'}</a>
                {/if}
                </div>
            {/if}
            {if $renewForm}
              {if $resource.ils_details.renewable && isset($resource.ils_details.renew_details)}
                <div data-role="fieldcontain">
                  <fieldset data-type="horizontal" data-role="controlgroup">
                    <label for="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text="Select this record"}</label>
                    <input type="checkbox" name="renewSelectedIDS[]" value="{$resource.ils_details.renew_details}" class="checkbox" id="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}" />
                    <input type="hidden" name="renewAllIDS[]" value="{$resource.ils_details.renew_details}" />
                  </fieldset>
                </div>
              {/if}
            {/if}
          </li>
        {/foreach}
        </ul>
        {if $renewForm}
            <fieldset data-type="horizontal" data-role="controlgroup">
              <input type="submit" class="button renew" name="renewSelected" value="{translate text="renew_selected"}" />
              <input type="submit" class="button renewAll" name="renewAll" value="{translate text='renew_all'}" />
           </fieldset>
          </form>
        {/if}
      {else}
        <p>{translate text='You do not have any items checked out'}.</p>
      {/if}
    {else}
      {include file="MyResearch/catalog-login.tpl"}
    {/if}
  </div>
  {include file="footer.tpl"}
</div>
