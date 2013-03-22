{js filename="check_item_statuses.js"}
{js filename="check_save_statuses.js"}
{js filename="openurl.js"}
{if $showPreviews}
{js filename="preview.js"}
{/if}

{if $bookBag}
<script>
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
