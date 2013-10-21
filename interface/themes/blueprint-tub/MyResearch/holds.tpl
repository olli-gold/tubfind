<div class="span-18">
  {if $user->cat_username}
    <h3>{translate text='Your Holds and Recalls'}</h3>

    {if $cancelForm}
      <form name="cancelForm" action="{$url|escape}/MyResearch/Holds" method="post" id="cancelHold">
        <div class="toolbar">
          <ul>
            <li><input type="submit" class="button holdCancel" name="cancelSelected" value="{translate text="hold_cancel_selected"}" onClick="return confirm('{translate text="confirm_hold_cancel_selected_text}')" /></li>
            <li><input type="submit" class="button holdCancelAll" name="cancelAll" value="{translate text='hold_cancel_all'}" onClick="return confirm('{translate text="confirm_hold_cancel_all_text}')" /></li>
          </ul>
        </div>
      <div class="clearer"></div>
    {/if}

    {if $holdResults.success}
      <div class="holdsMessage"><p class="success">{translate text=$holdResults.status}</p></div>
    {/if}

    {if $errorMsg}
       <div class="holdsMessage"><p class="error">{translate text=$errorMsg}</p></div>
    {/if}

    {if $cancelResults.count > 0}
      <div class="holdsMessage"><p class="info">{$cancelResults.count|escape} {translate text="hold_cancel_success_items"}</p></div>
    {/if}

    {if is_array($recordList)}
    <ul class="recordSet">
    {foreach from=$recordList item=resource name="recordLoop"}
      <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
        {if $cancelForm && $resource.ils_details.cancel_details}
          <input type="hidden" name="cancelAllIDS[]" value="{$resource.ils_details.cancel_details|escape}" />
          <input type="checkbox" name="cancelSelectedIDS[]" value="{$resource.ils_details.cancel_details|escape}" class="checkbox" style="margin-left:0;" />
        {/if}
        <div id="record{$resource.id|escape}">
          <div class="span-2">
            {if $resource.isbn.0}
              <img src="{$path}/bookcover.php?isn={$resource.isbn.0|@formatISBN}&amp;size=small" class="summcover" alt="{translate text='Cover Image'}"/>
            {else}
              <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
            {/if}
          </div>
          <div class="span-10">
            {* If $resource.id is set, we have the full Solr record loaded and should display a link... *}
            {if !empty($resource.title.0)}
              <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.title.0|escape}</a>
            {* If the record is not available in Solr, perhaps the ILS driver sent us a title we can show... *}
            {elseif !empty($resource.ils_details.title)}
              <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.ils_details.title|escape}</a>
            {elseif !empty($resource.journal)}
              <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.journal|escape}</a>
            {* Last resort -- indicate that no title could be found. *}
            {else}
              {translate text='Title not available'}
            {/if}
            <br/>
            {if $resource.author}
              {translate text='by'}: <a href="{$url}/Search/Results?lookfor={$resource.author|escape:"url"}&type=Author&localonly=1">{$resource.author|escape}</a><br/>
            {/if}
            {if $resource.tags}
              <strong>{translate text='Your Tags'}:</strong>
              {foreach from=$resource.tags item=tag name=tagLoop}
                <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape}</a>{if !$smarty.foreach.tagLoop.last},{/if}
              {/foreach}
              <br/>
            {/if}
            {if $resource.notes}
              <strong>{translate text='Notes'}:</strong> {$resource.notes|escape}<br/>
            {/if}

            {if is_array($resource.format)}
              {foreach from=$resource.format item=format}
                <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
              {/foreach}
              <br/>
            {elseif isset($resource.format)}
              <span class="iconlabel {$resource.format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$resource.format}</span>
              <br/>
            {/if}

            {if $resource.ils_details.volume}
              <strong>{translate text='Volume'}:</strong> {$resource.ils_details.volume|escape}<br />
            {/if}

            {if $resource.ils_details.publication_year}
              <strong>{translate text='Year of Publication'}:</strong> {$resource.ils_details.publication_year|escape}<br />
            {/if}

            {if $pickup}
              <strong>{translate text='pick_up_location'}:</strong>
              {foreach from=$pickup item=library}
                {if $library.locationID == $resource.ils_details.location}
                  {translate text=$library.locationDisplay}
                {/if}
              {/foreach}
              <br />
            {/if}
            {if $resource.ils_details.create}
            <strong>{translate text='Created'}:</strong> {translate text=$resource.ils_details.create|escape}
            {/if}
            {if $resource.ils_details.expire}
             |
            <strong>{translate text='Expires'}:</strong> {$resource.ils_details.expire|escape}
            {/if}
            <br />

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
            {if $resource.ils_details.cancel_link}
              <p><a href="{$resource.ils_details.cancel_link|escape}">{translate text='hold_cancel'}</a></p>
            {/if}

          </div>
          <div class="clear"></div>
        </div>
      </li>
    {/foreach}
    </ul>
    {if $cancelForm}
    </form>
    {/if}
    {else}
      {translate text='You do not have any holds or recalls placed'}.
    {/if}
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>

<div class="span-5 last">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>
