<div class="span-18">
  {if $user->cat_username}
    <h3>{translate text='Your Checked Out Items'}</h3>
    {if $blocks}
      {foreach from=$blocks item=block}
        <p class="info">{translate text=$block}</p>
      {/foreach}
    {/if}

    {if $transList}

      {if $renewForm}
      <form name="renewals" action="{$url}/MyResearch/CheckedOut" method="post" id="renewals">
        <div class="toolbar">
          <ul>
            <li><input type="submit" class="button renew" name="renewSelected" value="{translate text="renew_selected"}" /></li>
            <li><input type="submit" class="button renewAll" name="renewAll" value="{translate text='renew_all'}" /></li>
          </ul>
        </div>
        <br />
      {/if}

      {if $errorMsg}
        <p class="error">{translate text=$errorMsg}</p>
      {/if}

    <ul class="recordSet">
    {foreach from=$transList item=resource name="recordLoop"}
      <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
      {if $renewForm}
        {if $resource.ils_details.renewable && isset($resource.ils_details.renew_details)}
            <label for="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
            <input type="checkbox" name="renewSelectedIDS[]" value="{$resource.ils_details.renew_details}" class="checkbox" style="margin-left: 0" id="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}" />
            <input type="hidden" name="renewAllIDS[]" value="{$resource.ils_details.renew_details}" />
        {/if}
      {/if}
        <div id="record{$resource.id|escape}">
          <div class="span-2">
            {if $resource.isbn}
              <img src="{$path}/bookcover.php?isn={$resource.isbn|@formatISBN}&amp;size=small" class="summcover" alt="{translate text='Cover Image'}"/>
            {else}
              <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
            {/if}
          </div>
          <div class="span-10">
            {* If $resource.id is set, we have the full Solr record loaded and should display a link... *}
            {if !empty($resource.title)}
              {if is_array($resource.title)}
                <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.title.0|escape}</a>
              {else}
                <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.title|escape}</a>
              {/if}
            {elseif !empty($resource.journal)}
              <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.journal|escape}</a>
            {* If the record is not available in Solr, perhaps the ILS driver sent us a title we can show... *}
            {elseif !empty($resource.ils_details.title)}
              {$resource.ils_details.title|escape}
            {* Last resort -- indicate that no title could be found. *}
            {else}
              {translate text='Title not available'}
            {/if}
            <br/>
            {if $resource.author}
              {translate text='by'}: <a href="{$url}/Search/Results?lookfor={$resource.author|escape:"url"}&type=Author&localonly=1">{$resource.author|escape}</a><br/>
            {/if}
            {if $resource.tags}
              {translate text='Your Tags'}:
              {foreach from=$resource.tags item=tag name=tagLoop}
                <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape}</a>{if !$smarty.foreach.tagLoop.last},{/if}
              {/foreach}
              <br/>
            {/if}
            {if $resource.notes}
              {translate text='Notes'}: {$resource.notes|escape}<br/>
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
              <strong>{translate text='Volume'}:</strong> {$resource.ils_details.volume|escape}
              <br />
            {/if}

            {if $resource.ils_details.publication_year}
              <strong>{translate text='Year of Publication'}:</strong> {$resource.ils_details.publication_year|escape}
              <br />
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
              {if $resource.ils_details.renewals > 0}
                | <b>{translate text='Renewals'}:</b> {translate text=$resource.ils_details.renewals|escape}
              {elseif $resource.renewals > 0}
                | <b>{translate text='Renewals'}:</b> {translate text=$resource.renewals|escape}
              {/if}
              {if $resource.reservations == 0}
                {* Ist das Buch eine Fernleihe? *}
                {if substr($resource.ils_details.vb,0,6) == '830$99'}
                    | {translate text='Title from interlibrary loan'}
                {else}
                    | <a href="{$url}/MyResearch/Renew?VB={$resource.ils_details.vb|escape}">{translate text='Renew'}</a>
                {/if}
              {else}
                | <b>{translate text='Reservations'}:</b> {translate text=$resource.reservations|escape}
                {/if}
              {/if}

            {if $showStatus == "show" && $resource.ils_details.message}
              <div class="info">{translate text=$resource.ils_details.message}</div>
            {/if}
            {if $resource.ils_details.renewable && $resource.ils_details.renew_link}
              <a href="{$resource.ils_details.renew_link|escape}">{translate text='renew_item'}</a>
            {/if}
          </div>
          <div class="clear"></div>
        </div>
      </li>
    {/foreach}
    </ul>
      {if $renewForm}
        </form>
      {/if}
    {else}
      {translate text='You do not have any items checked out'}.
    {/if}
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>

<div class="span-5 last">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>