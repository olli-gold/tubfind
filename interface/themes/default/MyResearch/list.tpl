{js filename="ajax_common.js"}
{js filename="search.js"}
{js filename="myresearch.js"}

<div id="bd">
  <div id="yui-main" class="content">
    <div class="yui-b first contentbox">

      <b class="btop"><b></b></b>

      {* Internal Grid *}
      <div class="yui-ge">
        <div class="yui-u first">
          {if $listEditAllowed && $list && $list->id}
          <form action="{$url}/Cart/Home" id="favForm" name="addForm" method="POST">
            <input type="hidden" name="listID" value="{$list->id|escape}" />
            <input type="hidden" name="listName" value="{$list->title|escape}" />
            <input type="hidden" value="Favorites" name="origin">
            <div class="listDetails">
              <div class="listControls">
                <div class="toolbar">
                  <ul>
                    <li><input type="submit" class="button edit" name="editList" value="{translate text="edit_list"}" /></li>
                    <li><input type="submit" class="button delete" name="deleteList" onClick="confirmOperation({literal}{{/literal}listID: '{$list->id|escape}', origin: 'Favorites', deleteList: 'deleteList'{literal}}{/literal}, 'Cart', 'Home', '', '', '{translate text="delete_list"}', 'MyResearch', 'Favorites', ''); return false;" value="{translate text="delete_list"}" /></li>
                  </ul>
                </div>
              </div>
              <h3 class="list">{$list->title|escape:"html"}</h3>
              <br class="clearer" />
              {if $list->description}<p>{$list->description|escape}</p>{/if}
            </div>
          </form>
          {else}
            <h3 class="fav">{translate text='Your Favorites'}</h3>
          {/if}

          {if $errorMsg || $infoMsg}
          <div class="messages">
            {if $errorMsg}<p class="error">{$errorMsg|translate}</p>{/if}
            {if $infoMsg}<p class="userMsg">{$infoMsg|translate}{if $showExport} <a class="save" target="_new" href="{$showExport|escape}">{translate text="export_save"}</a>{/if}</p>{/if}
          </div>
          {/if}

          {if $resourceList}
          <div class="yui-gd resulthead">

            <div class="yui-u first">
            {if $recordCount}
              {translate text="Showing"}
              <b>{$recordStart}</b> - <b>{$recordEnd}</b>
              {translate text='of'} <b>{$recordCount}</b>
            {/if}
            </div>

            <div class="yui-u toggle">
              <form action="{$path}/Search/SortResults" method="post">
              <label for="sort">{translate text='Sort'}</label>
              <select name="sort" id="sort" onChange="document.location.href = this.options[this.selectedIndex].value;">
              {foreach from=$sortList item=sortData key=sortLabel}
                <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected{/if}>{translate text=$sortData.desc}</option>
              {/foreach}
              </select>
              <noscript><input type="submit" name="sortResults" value="{translate text="Set"}" /></noscript>
              </form>
            </div>

          </div>

          <form method="post" id="bulkActionForm" name="bulkActionForm" action="{$url}/Cart/Home">
          <input type="hidden" name="origin" value="Favorites" />
          <input type="hidden" name="followup" value="true" />
          <input type="hidden" name="followupModule" value="MyResearch" />
          <input type="hidden" name="followupAction" value="Favorites" />
          {if $list && $list->id}
            <input type="hidden" name="listID" value="{$list->id|escape}" />
            <input type="hidden" name="listName" value="{$list->title|escape}" />
          {/if}

          <div class="toolbar">
            <ul>
              <li><div class="control"><input id="selectAll" type="checkbox" class="checkbox" name="selectAll" onClick="toggleCheck(this, 'bulkActionForm', 'ui_checkboxes')" /> <label for="selectAll">{translate text="select_page"}</label></div></li>
              <li><input type="submit" class="button mail" name="email" onClick="processIds('bulkActionForm', 'ui_checkboxes', 'makeArray', 'Cart', 'Email', '', '', '{translate text="email_selected_favorites"}', 'MyResearch', 'Favorites', ''); return false;" value="{translate text='email_selected'}" /></li>
              {if $listEditAllowed}<li><input type="submit" class="button delete" name="delete" onClick="processIds('bulkActionForm', 'ui_checkboxes', 'makeArray', 'MyResearch', 'Delete', '{if $list}{$list->id|escape}{/if}', '', '{translate text="delete_selected_favorites"}', 'MyResearch', 'Favorites', ''); return false;" value="{translate text='delete_selected'}"></li>{/if}
              {if is_array($exportOptions) && count($exportOptions) > 0}
              <li><input type="submit" class="button export" name="export" onClick="processIds('bulkActionForm', 'ui_checkboxes', 'makeArray', 'Cart', 'Home', '', '', '{translate text="export_selected_favorites"}', 'MyResearch', 'Favorites', '', {literal}{{/literal}'export': '1'{literal}}{/literal}); return false;" value="{translate text='export_selected'}"></li>
              {/if}
           </ul>
           <br class="clearer" />
          </div>

          <ul>
          {foreach from=$resourceList item=resource name="recordLoop"}
            <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
              {* This is raw HTML -- do not escape it: *}
              {$resource}
            </li>
          {/foreach}
          </ul>
          </form>
          {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
          {else}
          {translate text='You do not have any saved resources'}
          {/if}

        </div>

        <div class="yui-u">
          <div class="listTags">
            {if $listList}
            <h3 class="list">{translate text='Your Lists'}</h3>
            <ul class="bulleted">
              {foreach from=$listList item=listItem}
              <li>
                {if $list && $listItem->id == $list->id}
                  <strong>{$listItem->title|escape:"html"}</strong>
                {else}
                  <a href="{$url}/MyResearch/MyList/{$listItem->id}">{$listItem->title|escape:"html"}</a>
                {/if}
                ({$listItem->cnt})
              </li>
              {/foreach}
            </ul>
            {/if}

            {if $tagList}
            <h3 class="tag">{if $list}{$list->title|escape:"html"} {translate text='Tags'}{else}{translate text='Your Tags'}{/if}</h3>

            {if $tags}
            <ul>
            {foreach from=$tags item=tag}
              <li>{translate text='Tag'}: {$tag|escape:"html"}
                <a href="{$url}/MyResearch/{if $list}MyList/{$list->id}{else}Favorites{/if}?{foreach from=$tags item=mytag}{if $tag != $mytag}tag[]={$mytag|escape:"url"}&amp;{/if}{/foreach}">X</a>
            </li>
            {/foreach}
            </ul>
            {/if}

            <ul class="bulleted">
            {foreach from=$tagList item=tag}
              <li>
                <a href="{$url}/MyResearch/{if $list}MyList/{$list->id}{else}Favorites{/if}?tag[]={$tag->tag|escape:"url"}{foreach from=$tags item=mytag}&amp;tag[]={$mytag|escape:"url"}{/foreach}">{$tag->tag|escape:"html"}</a> ({$tag->cnt})
              </li>
            {/foreach}
            </ul>
            {/if}
          </div>
        </div>
      </div>
      {* End of Internal Grid *}

      <b class="bbot"><b></b></b>

    </div>
    {* End of first Body *}
  </div>

  {include file="MyResearch/menu.tpl"}

</div>
