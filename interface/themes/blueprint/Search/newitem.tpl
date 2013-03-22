<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  <h3>{translate text='Find New Items'}</h3>
  <form method="get" action="{$url}/Search/NewItem" class="search">
    <div class="span-5">
      <fieldset>
        <legend>{translate text='Range'}:</legend>
        {foreach from=$ranges item="range" key="key"}
        <input id="newitem_range_{$key}" type="radio" name="range" value="{$range|escape}"{if $key == 0} checked="checked"{/if}/>
        <label for="newitem_range_{$key}">
        {if $range == 1}
          {translate text='Yesterday'}
        {else}
          {translate text='Past'} {$range|escape} {translate text='Days'}
        {/if}
        </label>
        <br/>
        {/foreach}
      </fieldset>
    </div>
    {if is_array($fundList) && !empty($fundList)}
    <div class="span-5">
      <label class="displayBlock" for="newitem_department">{translate text='Department'}:</label>
      <select id="newitem_department" name="department" size="10">
      {foreach from=$fundList item="fund" key="fundId"}
        <option value="{$fundId|escape}">{$fund|escape}</option>
      {/foreach}
      </select>
    </div>
    {/if}
    <div class="clear"></div>
    <input type="submit" name="submit" value="{translate text='Find'}"/>
  </form>
  {* not currently supported: <p><a href="{$url}/Search/NewItem/RSS" class="feed">{translate text='New Item Feed'}</a></p> *}
</div>
<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
</div>
<div class="clear"></div>
      