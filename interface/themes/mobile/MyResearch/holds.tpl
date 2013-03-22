{if $user->cat_username}
  <ul class="pageitem">
  {if is_array($recordList)}
  {foreach from=$recordList item=record name="recordLoop"}
    <li class="menu">
      {* TODO: implement resource icons in mobile template: <img src="images/{$record.format|lower|regex_replace:"/[^a-z0-9]/":""}.png"> *}
      {* If $record.id is set, we have the full Solr record loaded and should display a link... *}
      {if !empty($record.id)}
        <a href="{$url}/Record/{$record.id|escape:"url"}" class="title">{$record.title|escape}</a>
      {* If the record is not available in Solr, perhaps the ILS driver sent us a title we can show... *}
      {elseif !empty($record.ils_details.title)}
        {$record.ils_details.title|escape}
      {* Last resort -- indicate that no title could be found. *}
      {else}
        {translate text='Title not available'}
      {/if}
    </li>
    <li class="textbox">
      <b>{translate text='Created'}:</b> {$record.ils_details.create|escape}<br/>
      <b>{translate text='Expires'}:</b> {$record.ils_details.expire|escape}
    </li>
  {/foreach}
  {else}
  <li class="textbox">{translate text='You do not have any holds or recalls placed'}.</li>
  {/if}
  </ul>
{else}
  {include file="MyResearch/catalog-login.tpl"}
{/if}

{include file="MyResearch/menu.tpl"}
