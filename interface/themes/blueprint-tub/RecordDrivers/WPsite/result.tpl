<div class="result recordId" id="record{$summId|escape}">
  {* hide until complete
  <label for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
  <input id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="id[]" value="{$summId|escape}" class="checkbox addToCartCheckbox"/>
   *}
  <div class="span-2">
    <img src="{$url}/interface/themes/blueprint-tub/images/website.jpg" class="alignleft" alt="Website"/>
  </div>
  <div class="span-9">
    <div class="resultItemLine1">
      {if $summTitleGer}
        <a href="{$urlDE|escape}" class="title">
        <img src="{$url}/interface/themes/blueprint-tub/images/de.gif" border="0" alt="Deutsch" title="Deutsch"/>
        {if is_array($summTitleGer)}
            {$summTitleGer.0|truncate:180:"..."|highlight:$lookfor}
        {else}
            {$summTitleGer|truncate:180:"..."|highlight:$lookfor}
        {/if}
        </a>
        {if $summTitleEng}
            <br/><a href="{$urlEN|escape}" class="title">
            <img src="{$url}/interface/themes/blueprint-tub/images/uk.gif" border="0" alt="English" title="English"/>
            {if is_array($summTitleEng)}
                {$summTitleEng.0|truncate:180:"..."|highlight:$lookfor}
            {else}
                {$summTitleEng|truncate:180:"..."|highlight:$lookfor}
            {/if}
            </a>
        {/if}
      {else}
        <a href="{$summURLs.0|escape}" class="title">{if !summTitle}{translate text='Title not available'}{else}
        {if is_array($summTitle)}
            {$summTitle.0|truncate:180:"..."|highlight:$lookfor}
        {else}
            {$summTitle|truncate:180:"..."|highlight:$lookfor}
        {/if}
      {/if}
      </a>
      {/if}
    </div>
{*
    <div>
        {$summContent.0|truncate:180|highlight:$lookfor}
    </div>

    <div class="resultItemLine2">
      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Search/Results?lookfor={$summAuthor|escape:"url"}&type=Author&localonly=1">{$summAuthor|escape}</a>
      {/if}

      {if $summDate}{translate text='Published'} {$summDate.0|escape}{/if}
    </div>
*}
    <div class="span-14 last">
        {translate text='Source'}: <span>{translate text='Website TUBHH'}</span><br/>
    </div>

  </div>

  <div class="span-4 last">
    {if $user}
        <div id="saveRecordBox{$summId|escape}">
            <a id="saveRecord{$summId|escape}" href="{$url}/Record/{$summId|escape:"url"}/Save" class="fav tool saveRecord" title="{translate text='Add to favorites'}">{translate text='Add to favorites'}</a>
        </div>
    {/if}
    {* Display the lists that this record is saved to *}
    <div class="savedLists info hide" id="savedLists{$summId|escape}">
      <strong>{translate text="Saved in"}:</strong>
    </div>
    {if $bookBag}
      <div class="bookbagLightbox info hide" id="inbookbag{$summId|escape}">
        <strong>{translate text="in Book Bag"}</strong>
      </div>
      <a id="keepRecord{$summId|escape}" href="#" class="recordCart bookbagAdd offscreen" title="{translate text='Add to Book Bag'}" keepId="{$summId|escape}">{translate text='Add to Book Bag'}</a>
    {/if}
  </div>
  
  <div class="clear"></div>
</div>

{if $summCOinS}<span class="Z3988" title="{$summCOinS|escape}"></span>{/if}
