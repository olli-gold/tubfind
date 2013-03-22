<form method="get" action="{$url}/Search/NewItem" name="searchForm" class="search">
  <span class="graytitle">{translate text='Find New Items'}</span>
  <ul class="pageitem">
    <li>{translate text='Range'}:</li>
    <li>
      {foreach from=$ranges item="range" key="key"}
        <input type="radio" name="range" value="{$range|escape}"{if $key == 0} checked="checked"{/if}/>
        {if $range == 1}
          {translate text='Yesterday'}
        {else}
          {translate text='Past'} {$range|escape} {translate text='Days'}
        {/if}
        <br/>
      {/foreach}
      <br/>
    </li>
    {if is_array($fundList) && !empty($fundList)}
    <li class="menu">
      <select name="department">
        <option value="">{translate text='Department'}:</option>
        {foreach from=$fundList item="fund" key="fundId"}
          <option value="{$fundId|escape}">{$fund|escape}</option>
        {/foreach}
      </select>
    </li>
    {/if}
    <li class="form">
      <input type="submit" name="submit" value="{translate text='Find'}"/>
    </li>
  </ul>
</form>
