{if $user->cat_username}
  <ul class="pageitem">
  {if $transList}
    {foreach from=$transList item=resource name="recordLoop"}
      <li class="menu">
        {* TODO: implement resource icons in mobile template: <img src="images/{$resource.format|lower|regex_replace:"/[^a-z0-9]/":""}.png"> *}
        {* If $resource.id is set, we have the full Solr record loaded and should display a link... *}
        {if !empty($resource.id)}
          <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.title|escape}</a>
        {* If the record is not available in Solr, perhaps the ILS driver sent us a title we can show... *}
        {elseif !empty($resource.ils_details.title)}
          {$resource.ils_details.title|escape}
        {* Last resort -- indicate that no title could be found. *}
        {else}
          {translate text='Title not available'}
        {/if}
      </li>
      <li class="textbox">
        <b>{translate text='Due'}: {$resource.ils_details.duedate|escape}</b>
      </li>
    {/foreach}
  {else}
    <li class="textbox">{translate text='You do not have any items checked out'}.</li>
  {/if}
  </ul>
{else}
  {include file="MyResearch/catalog-login.tpl"}
{/if}

{include file="MyResearch/menu.tpl"}
