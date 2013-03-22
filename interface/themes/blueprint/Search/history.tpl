<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
    {if !$noHistory}
      {if $saved}
        <h3>{translate text="history_saved_searches"}</h3>
        <table class="datagrid" width="100%">
          <tr>
            <th width="25%">{translate text="history_time"}</th>
            <th width="30%">{translate text="history_search"}</th>
            <th width="30%">{translate text="history_limits"}</th>
            <th width="10%">{translate text="history_results"}</th>
            <th width="5%">{translate text="history_delete"}</th>
          </tr>
          {foreach item=info from=$saved name=historyLoop}
          {if ($smarty.foreach.historyLoop.iteration % 2) == 0}
          <tr class="evenrow">
          {else}
          <tr class="oddrow">
          {/if}
            <td>{$info.time}</td>
            <td><a href="{$info.url|escape}">{if empty($info.description)}{translate text="history_empty_search"}{else}{$info.description|escape}{/if}</a></td>
            <td>{foreach from=$info.filters item=filters key=field}{foreach from=$filters item=filter}
              <strong>{translate text=$field|escape}</strong>: {$filter.display|escape}<br/>
            {/foreach}{/foreach}</td>
            <td>{$info.hits}</td>
            <td><a href="{$path}/MyResearch/SaveSearch?delete={$info.searchId|escape:"url"}&amp;mode=history" class="delete">{translate text="history_delete_link"}</a></td>
          </tr>
          {/foreach}
        </table>
      {/if}

      {if $links}
        <h3>{translate text="history_recent_searches"}</h3>
        <table class="datagrid" width="100%">
          <tr>
            <th width="25%">{translate text="history_time"}</th>
            <th width="30%">{translate text="history_search"}</th>
            <th width="30%">{translate text="history_limits"}</th>
            <th width="10%">{translate text="history_results"}</th>
            <th width="5%">{translate text="history_save"}</th>
          </tr>
          {foreach item=info from=$links name=historyLoop}
          {if ($smarty.foreach.historyLoop.iteration % 2) == 0}
          <tr class="evenrow">
          {else}
          <tr class="oddrow">
          {/if}
            <td>{$info.time}</td>
            <td><a href="{$info.url|escape}">{if empty($info.description)}{translate text="history_empty_search"}{else}{$info.description|escape}{/if}</a></td>
            <td>{foreach from=$info.filters item=filters key=field}{foreach from=$filters item=filter}
              <strong>{translate text=$field|escape}</strong>: {$filter.display|escape}<br/>
            {/foreach}{/foreach}</td>
            <td>{$info.hits}</td>
            <td><a href="{$path}/MyResearch/SaveSearch?save={$info.searchId|escape:"url"}&amp;mode=history" class="add">{translate text="history_save_link"}</a></td>
          </tr>
          {/foreach}
        </table>
        <a href="{$path}/Search/History?purge=true" class="delete">{translate text="history_purge"}</a>
      {/if}

    {else}
      <h3>{translate text="history_recent_searches"}</h3>
      {translate text="history_no_searches"}
    {/if}
</div>

<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>