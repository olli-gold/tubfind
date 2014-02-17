<div class="result recordId" id="record{$summId|escape}">
  {* hide until complete
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="id[]" value="{$summId|escape}" class="checkbox addToCartCheckbox"/>
   *}
  <div class="span-2">
    <img src="{$url}/interface/themes/blueprint-tub/images/tubdok-icon.gif" class="alignleft" alt="TUBdok"/>
  </div>
  <div class="span-9">
    <div class="resultItemLine1">
      <b class="listtitle">
      <a href="{$summDocUrl}" class="title">
      {if !$summTitle}
          {translate text='Title not available'}
      {else}
          {if is_array($summTitle)}
              {$summTitle.0|truncate:180:"..."|highlight:$lookfor}
          {else}
              {$summTitle|truncate:180:"..."|highlight:$lookfor}
          {/if}
      {/if}
      </a>
      </b>
    </div>

    <div>
        {$summContent.0|truncate:180|highlight:$lookfor}
    </div>

    <div class="resultItemLine2">
      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Search/Results?lookfor={$summAuthor|escape:"url"}&type=Author&localonly=1">{$summAuthor|escape}</a>
      {/if}

      {if $summDate}{translate text='Published'} 
          {if is_array($summDate)}
              {$summDate.0|escape}
          {else}
              {$summDate|escape}
          {/if}
      {/if}
    </div>

    <div class="span-14 last">
        {translate text='Source'}: <span>{translate text='TUBdok'}</span><br/>
        <strong>{translate text='Files'}:</strong><br/>
        {foreach from=$summFiles item=fileUrl key=fileName}
          <a href="{$fileUrl}">{$fileName|escape}</a><br/>
        {/foreach}

        <br/>
        {foreach from=$summFormats item=format}
            <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
        {/foreach}
    </div>

  </div>

  <div class="span-4 last">
    {if $user}
        <a id="saveRecord{$summId|escape}" href="{$url}/Record/{$summId|escape:"url"}/Save" class="fav tool saveRecord" title="{translate text='Add to favorites'}">{translate text='Add to favorites'}</a>
    {/if}
    {* Display the lists that this record is saved to *}
    <div class="savedLists info hide" id="savedLists{$summId|escape}">
      <strong>{translate text="Saved in"}:</strong>
    </div>
  </div>
  
  <div class="clear"></div>
</div>

{if $summCOinS}<span class="Z3988" title="{$summCOinS|escape}"></span>{/if}
