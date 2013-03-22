<div class="result recordId" id="record{$summId|escape}">
  {* hide until complete
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="id[]" value="{$summId|escape}" class="checkbox addToCartCheckbox"/>
   *}
  <div class="span-2">
    <img src="{$url}/interface/themes/blueprint-tub/images/dbis.jpg" class="alignleft" alt="DBIS"/>
  </div>
  <div class="span-9">
    <div class="resultItemLine1">
      {foreach from=$summURLs item=recordurl}{/foreach}
      <a href="{$recordurl|escape}" class="title">{if !$summTitle}{translate text='Title not available'}{else}{$summTitle|truncate:180:"..."|highlight:$lookfor}{/if}</a>
    </div>

    <div class="resultItemLine2">
      {if !empty($summPublisher)}
      {translate text='by'}
      {$summPublisher.0|escape}
      {/if}

      {if $dateSpan}{translate text='Published'} {$dateSpan.0|escape}{/if}
    </div>

    <div class="span-14 last">
        <strong>{translate text='Located'}:</strong> <span>{translate text='DBIS'}</span><br/>
        <a href="{$recordurl}" target="_blank">{$recordurl}</a>
    </div>

  </div>

  <div class="span-4 last">
    <a id="saveRecord{$summId|escape}" href="{$url}/Record/{$summId|escape:"url"}/Save" class="fav tool saveRecord" title="{translate text='Add to favorites'}">{translate text='Add to favorites'}</a>
    
    {* Display the lists that this record is saved to *}
    <div class="savedLists info hide" id="savedLists{$summId|escape}">
      <strong>{translate text="Saved in"}:</strong>
    </div>
  </div>
  
  <div class="clear"></div>
</div>

{if $summCOinS}<span class="Z3988" title="{$summCOinS|escape}"></span>{/if}
