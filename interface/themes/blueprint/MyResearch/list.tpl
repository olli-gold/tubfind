{js filename="bulk_actions.js"}
{if $bookBag}
<script type="text/javascript">
vufindString.bulk_noitems_advice = "{translate text="bulk_noitems_advice"}";
vufindString.confirmEmpty = "{translate text="bookbag_confirm_empty"}";
vufindString.viewBookBag = "{translate text="View Book Bag"}";
vufindString.addBookBag = "{translate text="Add to Book Bag"}";
vufindString.removeBookBag = "{translate text="Remove from Book Bag"}";
vufindString.itemsAddBag = "{translate text="items_added_to_bookbag"}";
vufindString.itemsInBag = "{translate text="items_already_in_bookbag"}";
vufindString.bookbagMax = "{$bookBag->getMaxSize()}";
vufindString.bookbagFull = "{translate text="bookbag_full_msg"}";
vufindString.bookbagStatusFull = "{translate text="bookbag_full"}";
</script>
{/if}

<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  {if $list}
    <div class="toolbar floatright">
      <form method="post" name="addForm" action="{$url}/Cart/Home">
        <input type="hidden" name="listID" value="{$list->id|escape}" />
        <input type="hidden" name="listName" value="{$list->title|escape}" />
        {if $listEditAllowed}
          <input type="hidden" value="Favorites" name="origin">
          <input type="submit" class="edit button" name="editList" value="{translate text="edit_list"}" />
          <input type="submit" class="delete deleteList button" id="deleteList{$list->id|escape}" title="{translate text="delete_list"}" name="deleteList" value="{translate text="delete_list"}" />
        {/if}
      </form>
    </div>
    <h3 class="list">{$list->title|escape:"html"}</h3>
    {if $list->description}<p class="listDescription">{$list->description|escape}</p>{/if}
  {else}
    <h3 class="fav">{translate text="Your Favorites"}</h3>
  {/if}

  {if $errorMsg || $infoMsg}
  <div class="messages">
    {if $errorMsg}<p class="error">{$errorMsg|translate}</p>{/if}
    {if $infoMsg}<p class="info">{$infoMsg|translate}{if $showExport} <a class="save" target="_new" href="{$showExport|escape}">{translate text="export_save"}</a>{/if}</p>{/if}
  </div>
  {/if}
  {if $resourceList}
    <div class="resulthead">
      <div class="floatleft">
      {if $recordCount}
        {translate text="Showing"}
        <strong>{$recordStart}</strong> - <strong>{$recordEnd}</strong>
        {translate text='of'} <strong>{$recordCount}</strong>
      {/if}
      </div>
      <div class="floatright">
        <form action="{$path}/Search/SortResults" method="post">
          <label for="sort_options_1">{translate text='Sort'}</label>
          <select id="sort_options_1" name="sort" class="jumpMenu">
          {foreach from=$sortList item=sortData key=sortLabel}
            <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected="selected"{/if}>{translate text=$sortData.desc}</option>
          {/foreach}
          </select>
          <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
        </form>
      </div>
      <div class="clear"></div>
    </div>
    <form method="post" name="bulkActionForm" action="{$url}/Cart/Home">
      <input type="hidden" name="origin" value="Favorites" />
      <input type="hidden" name="followup" value="true" />
      <input type="hidden" name="followupModule" value="MyResearch" />
      <input type="hidden" name="followupAction" value="Favorites" />
    {if $list && $list->id}
      <input type="hidden" name="listID" value="{$list->id|escape}" />
      <input type="hidden" name="listName" value="{$list->title|escape}" />
    {/if}
    <div class="bulkActionButtons">
      <input type="checkbox" class="selectAllCheckboxes floatleft" name="selectAll" id="addFormCheckboxSelectAll"/> <label class="floatleft" for="addFormCheckboxSelectAll">{translate text="select_page"}</label>
      <span class="floatleft">|</span>
      <span class="floatleft"><strong>{translate text="with_selected"}: </strong></span>
      {if $bookBag}
      <a id="updateCart" class="bookbagAdd offscreen" href="">{translate text='Add to Book Bag'}</a>
      <noscript>
        <input type="submit"  class="button floatleft bookbagAdd" name="add" value="{translate text='Add to Book Bag'}"/>
      </noscript>
      {/if}
      <input type="submit" class="mail floatleft button" name="email" value="{translate text='Email'}" title="{translate text='email_selected'}"/>
      {if $listEditAllowed}<input id="delete_list_items_{if $list}{$list->id|escape}{/if}" type="submit" class="delete floatleft button" name="delete" value="{translate text='Delete'}" title="{translate text='delete_selected'}"/>{/if}
      {if is_array($exportOptions) && count($exportOptions) > 0}
      <input type="submit" class="export floatleft button" name="export" value="{translate text='Export'}" title="{translate text='export_selected'}"/>
      {/if}
      <input type="submit" class="print floatleft button" name="print" value="{translate text='Print'}" title="{translate text='print_selected'}"/>
      <div class="clear"></div>
    </div> 
    <ul class="recordSet">
    {foreach from=$resourceList item=resource name="recordLoop"}
      <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
        <span class="recordNumber">{$recordStart+$smarty.foreach.recordLoop.iteration-1}</span>
        {* This is raw HTML -- do not escape it: *}
        {$resource}
      </li>
    {/foreach}
    </ul>
    <div class="bulkActionButtons">
      <input type="checkbox" class="selectAllCheckboxes floatleft" name="selectAll" id="addFormCheckboxSelectAllBottom"/> <label class="floatleft" for="addFormCheckboxSelectAllBottom">{translate text="select_page"}</label>
      <span class="floatleft">|</span>
      <span class="floatleft"><strong>{translate text="with_selected"}: </strong></span>
      {if $bookBag}
      <a id="updateCartBottom" class="bookbagAdd offscreen" href="">{translate text='Add to Book Bag'}</a>
      <noscript>
        <input type="submit"  class="button floatleft bookbagAdd" name="add" value="{translate text='Add to Book Bag'}"/>
      </noscript>
      {/if}
      <input type="submit" class="mail floatleft button" name="email" value="{translate text='Email'}" title="{translate text='email_selected'}"/>
      {if $listEditAllowed}<input id="bottom_delete_list_items_{if $list}{$list->id|escape}{/if}" type="submit" class="delete floatleft button" name="delete" value="{translate text='Delete'}" title="{translate text='delete_selected'}"/>{/if}
      {if is_array($exportOptions) && count($exportOptions) > 0}
      <input type="submit" class="export floatleft button" name="export" value="{translate text='Export'}" title="{translate text='export_selected'}"/>
      {/if}
      <input type="submit" class="print floatleft button" name="print" value="{translate text='Print'}" title="{translate text='print_selected'}"/>
      <div class="clear"></div>
    </div> 
    </form>
    {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
  {else}
    <p>{translate text='You do not have any saved resources'}</p>
  {/if}
</div>
  
<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">  
  {include file="MyResearch/menu.tpl"}
  
  {if $listList}
    <div class="sidegroup">
      <h4 class="list">{translate text='Your Lists'}</h4>
      <ul>
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
    </div>
  {/if}

  {if $tagList}
    <div class="sidegroup">
      <h4 class="tag">{if $list}{$list->title|escape:"html"} {translate text='Tags'}{else}{translate text='Your Tags'}{/if}</h4>
      {if $tags}
      <ul>
      {foreach from=$tags item=tag}
        <li>{translate text='Tag'}: {$tag|escape:"html"}
          <a href="{$url}/MyResearch/{if $list}MyList/{$list->id}{else}Favorites{/if}?{foreach from=$tags item=mytag}{if $tag != $mytag}tag[]={$mytag|escape:"url"}&amp;{/if}{/foreach}">X</a>
        </li>
      {/foreach}
      </ul>
      {/if}
          
      <ul>
      {foreach from=$tagList item=tag}
        <li><a href="{$url}/MyResearch/{if $list}MyList/{$list->id}{else}Favorites{/if}?tag[]={$tag->tag|escape:"url"}{foreach from=$tags item=mytag}&amp;tag[]={$mytag|escape:"url"}{/foreach}">{$tag->tag|escape:"html"}</a> ({$tag->cnt})</li>
      {/foreach}
      </ul>
    </div>
  {/if}
</div>

<div class="clear"></div>
