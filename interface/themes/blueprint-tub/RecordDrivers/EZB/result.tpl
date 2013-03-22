<div class="result recordId" id="record{$summId|escape}">
  {* hide until complete
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="id[]" value="{$summId|escape}" class="checkbox addToCartCheckbox"/>
   *}
  <div class="span-2">
    <img src="{$url}/interface/themes/blueprint-tub/images/ezb.jpg" class="alignleft" alt="EZB"/>
  </div>
  <div class="span-9">
    <div class="resultItemLine1">
      <a href="{$url}/Record/{$summId|escape:"url"}" class="title">{if !$summTitle}{translate text='Title not available'}{else}{$summTitle|truncate:180:"..."|highlight:$lookfor}{/if}</a>
    </div>

    <div class="resultItemLine2">
      {if !empty($summPublisher)}
      {translate text='by'}
      {$summPublisher.0|escape}
      {/if}

      {if $summDate}{translate text='Published'} {$summDate.0|escape}{/if}
    </div>

    <div class="span-14 last">
        <strong>{translate text='Located'}:</strong> <span>{translate text='EZB'}</span><br/>
        {foreach from=$summURLs item=recordurl}
        <a href="{$recordurl}" target="_blank">{$recordurl}</a><br/>
        {/foreach}
    </div>

    <div class="span-14 last>
         <strong>{translate text='Licensed'}: </strong>
            {if is_array($ezb_licenses)}
                {foreach from=$ezb_licenses item=ezb_license name=loop}
                    {$ezb_license}
                {/foreach}
            {else}
                <a href="{$ezb_license_links}">{$ezb_licenses}</a>
            {/if}
    </div>

    <div class="span-14 last">
        <strong>{translate text='Availability'}:</strong> <span>
        {if $ezb_availability=='1'}
            <img src="{$url}/images/bullet_green.png" /> {translate text='free'}
        {elseif $ezb_availability=='2'}
            <img src="{$url}/images/bullet_orange.png" /> {translate text='available'}
        {elseif $ezb_availability=='6'}
            <img src="{$url}/images/bullet_orange.png" /><img src="{$url}/images/bullet_red.png" /> {translate text='available in parts'}
        {else}
            <img src="{$url}/images/bullet_red.png" /> {translate text='unknown'}
        {/if}
        </span><br/>
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
