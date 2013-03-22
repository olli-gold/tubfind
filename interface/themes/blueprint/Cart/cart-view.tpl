  <script type="text/javascript">
  vufindString.bulk_noitems_advice = "{translate text="bulk_noitems_advice"}";
  vufindString.confirmEmpty = "{translate text="bookbag_confirm_empty"}";
  vufindString.viewBookBag = "{translate text="View Book Bag"}";
  vufindString.addBookBag = "{translate text="Add to Book Bag"}";
  vufindString.removeBookBag = "{translate text="Remove from Book Bag"}";
  </script>
  
  {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
  {if $infoMsg}<div class="success">{$infoMsg|translate}</div>{/if}
  
  {if $showExport} <div class="success"><a class="save" target="_new" href="{$url}/Cart/Export?exportInit">{translate text="export_save"}</a></div>{/if}
  <form method="post" name="cartForm" action="{$url}/Cart/Home">
  {if !$bookBag->isEmpty()}
  <div class="bulkActionButtons">
    <input type="checkbox" class="selectAllCheckboxes floatleft" name="selectAll" id="cartCheckboxSelectAll"/> <label for="cartCheckboxSelectAll" class="floatleft">{translate text="select_page"}</label>
    <input type="submit" class="fav floatleft button" name="saveCart" value="{translate text='bookbag_save_selected'}" title="{translate text='bookbag_save'}"/>
    <input type="submit" class="mail floatleft button" name="email" value="{translate text='bookbag_email_selected'}" title="{translate text='bookbag_email'}"/>
    {if is_array($exportOptions) && count($exportOptions) > 0}
    <input type="submit" class="export floatleft button" name="export" value="{translate text='bookbag_export_selected'}" title="{translate text='bookbag_export'}"/>
    {/if}
    <input type="submit" class="print floatleft button" name="print" value="{translate text='bookbag_print_selected'}" title="{translate text='print_selected'}"/>
    <input type="submit" class="bookbagDelete floatleft button" name="delete" value="{translate text='bookbag_delete_selected'}" title="{translate text='bookbag_delete'}"/>
    <input type="submit" class="bookbagEmpty floatleft button" name="empty" value="{translate text='Empty Book Bag'}" title="{translate text='Empty Book Bag'}"/>
    <div class="clearer"></div>
  </div>
  {/if}
   
  {include file="Cart/cart.tpl"}
  </form>
