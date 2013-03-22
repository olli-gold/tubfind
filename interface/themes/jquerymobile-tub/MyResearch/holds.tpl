<div data-role="page" id="MyResearch-holds">
  {include file="header.tpl"}
  <div data-role="content">
    {if $user->cat_username}
      <h3>{translate text='Your Holds and Recalls'}</h3>

      {if $holdResults.success}
        <p class="success">{translate text=$holdResults.status}</p>
      {/if}

      {if $errorMsg}
        <p class="error">{translate text=$errorMsg}</p>
      {/if}

      {if $cancelResults.count > 0}
        <p class="info">{$cancelResults.count|escape} {translate text="hold_cancel_success_items"}</p>
      {/if}

      {if $recordList}

        {if $cancelForm}
          <form name="cancelForm" action="{$url|escape}/MyResearch/Holds" method="post" id="cancelHold">
            <fieldset data-type="horizontal" data-role="controlgroup">
              <input type="submit" class="button holdCancel" name="cancelSelected" value="{translate text="hold_cancel_selected"}" onClick="return confirm('{translate text="confirm_hold_cancel_selected_text}')" />
              <input type="submit" class="button holdCancelAll" name="cancelAll" value="{translate text='hold_cancel_all'}" onClick="return confirm('{translate text="confirm_hold_cancel_all_text}')" />
             </fieldset>
        {/if}

        <ul class="results holds" data-role="listview">
        {foreach from=$recordList item=resource name="recordLoop"}
          <li>
            {if !empty($resource.id)}<a rel="external" href="{$path}/Record/{$resource.id|escape}">{/if}
            <div class="result">
              {* If $resource.id is set, we have the full Solr record loaded and should display a link... *}
              {if !empty($resource.id)}
                <h3>{$resource.title|trim:'/:'|escape}</h3>
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

              {if $resource.ils_details.volume}
                <p><strong>{translate text='Volume'}:</strong> {$resource.ils_details.volume|escape}</p>
              {/if}

              {if $resource.ils_details.publication_year}
                <p><strong>{translate text='Year of Publication'}:</strong> {$resource.ils_details.publication_year|escape}</p>
              {/if}

              {* Depending on the ILS driver, the "location" value may be a string or an ID; figure out the best
                 value to display... *}
              {assign var="pickupDisplay" value=""}
              {assign var="pickupTranslate" value="0"}
              {if isset($resource.ils_details.location)}
                {if $pickup}
                  {foreach from=$pickup item=library}
                    {if $library.locationID == $resource.ils_details.location}
                      {assign var="pickupDisplay" value=$library.locationDisplay}
                      {assign var="pickupTranslate" value="1"}
                    {/if}
                  {/foreach}
                {/if}
                {if empty($pickupDisplay)}
                  {assign var="pickupDisplay" value=$resource.ils_details.location}
                {/if}
              {/if}
              {if !empty($pickupDisplay)}
                <p>
                <strong>{translate text='pick_up_location'}:</strong>
                {if $pickupTranslate}{translate text=$pickupDisplay}{else}{$pickupDisplay|escape}{/if}
                </p>
              {/if}

              {foreach from=$cancelResults.items item=cancelResult key=itemId}
                {if $itemId == $resource.ils_details.item_id && $cancelResult.success == false}
                  <div class="error">{translate text=$cancelResult.status}{if $cancelResult.sysMessage} : {translate text=$cancelResult.sysMessage|escape}{/if}</div>
                {/if}
              {/foreach}

              {if $resource.ils_details.available == true}
                <div class="userMsg">{translate text="hold_available"}</div>
              {else}
                {if $resource.ils_details.position}
                  <p><strong>{translate text='hold_queue_position'}:</strong> {$resource.ils_details.position|escape}</p>
                {/if}
              {/if}

              <p><strong>{translate text='Created'}:</strong> {$resource.ils_details.create|escape} |
              <strong>{translate text='Expires'}:</strong> {$resource.ils_details.expire|escape}</p>
            </div>
            {if !empty($resource.id)}</a>{/if}
            {if $cancelForm && $resource.ils_details.cancel_details}
              <div data-role="fieldcontain">
                <fieldset data-type="horizontal" data-role="controlgroup">
                  <label for="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text="Select this record"}</label>
                  <input type="checkbox" name="cancelSelectedIDS[]" value="{$resource.ils_details.cancel_details|escape}" class="checkbox" id="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}" />
                  <input type="hidden" name="cancelAllIDS[]" value="{$resource.ils_details.cancel_details|escape}" />
                </fieldset>
              </div>
            {/if}
          </li>
        {/foreach}
        {if $cancelForm}
            <fieldset data-type="horizontal" data-role="controlgroup">
              <input type="submit" class="button holdCancel" name="cancelSelected" value="{translate text="hold_cancel_selected"}" onClick="return confirm('{translate text="confirm_hold_cancel_selected_text}')" />
              <input type="submit" class="button holdCancelAll" name="cancelAll" value="{translate text='hold_cancel_all'}" onClick="return confirm('{translate text="confirm_hold_cancel_all_text}')" />
           </fieldset>
          </form>
        {/if}
        </ul>
      {else}
        <p>{translate text='You do not have any holds or recalls placed'}.</p>
      {/if}
    {else}
      {include file="MyResearch/catalog-login.tpl"}
    {/if}
  </div>
  {include file="footer.tpl"}
</div>

