{if $smarty.post.mylang}
{assign var="jsFileName" value="check_item_statuses_"|cat:$smarty.post.mylang|cat:".js"}
{elseif $smarty.cookies.language}
{assign var="jsFileName" value="check_item_statuses_"|cat:$smarty.cookies.language|cat:".js"}
{else}
{assign var="jsFileName" value="check_item_statuses_de.js"}
{/if}
{js filename=$jsFileName}
{js filename="check_save_statuses.js"}
{if $showContext}
{js filename="search_hierarchyTree.js}
{/if}
{js filename="openurl.js"}
{if $showPreviews}
{js filename="preview.js"}
{/if}

{if $bookBag}
<form method="post" name="bulkActionForm" action="{$url}/Cart/Home">
  <div class="bulkActionButtons">
    <input type="checkbox" class="selectAllCheckboxes floatleft" name="selectAll" id="addFormCheckboxSelectAll"/> <label class="floatleft" for="addFormCheckboxSelectAll">{translate text="select_page"}</label>
    <span class="floatleft">|</span>
    <span class="floatleft"><strong>{translate text="with_selected"}: </strong></span>
    <a href="#" id="updateCart" class="bookbagAdd offscreen">{translate text='Add to Book Bag'}</a> 
    <noscript>
    <input type="submit" class="button bookbagAdd" name="add" value="{translate text='Add to Book Bag'}"/>
    </noscript>
    <div class="clear"></div>
  </div>
{/if}

  <ul class="recordSet">
  {foreach from=$recordSet item=record name="recordLoop"}
    <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
      <span class="recordNumber">{$recordStart+$smarty.foreach.recordLoop.iteration-1}</span>
      {* This is raw HTML -- do not escape it: *}
      {$record}
    </li>
  {/foreach}
  </ul>
  
{if $bookBag}  
  <div class="bulkActionButtons">
    <input type="checkbox" class="selectAllCheckboxes floatleft" name="selectAll" id="addFormCheckboxSelectAllBottom"/> <label class="floatleft" for="addFormCheckboxSelectAllBottom">{translate text="select_page"}</label>
    <span class="floatleft">|</span>
    <span class="floatleft"><strong>{translate text="with_selected"}: </strong></span>
    <a href="#" id="updateCartBottom" class="bookbagAdd offscreen">{translate text='Add to Book Bag'}</a> 
    <noscript>
    <input type="submit" class="button bookbagAdd" name="add" value="{translate text='Add to Book Bag'}"/>
    </noscript>
    <div class="clear"></div>
  </div>
</form>
{/if}
