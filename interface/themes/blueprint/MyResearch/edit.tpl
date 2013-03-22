<div class="record">
  <h1>{$record.title|escape:"html"}</h1>

  <form method="post" name="editForm" action="">
  {if empty($savedData)}
    <p>
      {if isset($listFilter)}
        {translate text='The record you selected is not part of the selected list.'}
      {else}
        {translate text='The record you selected is not part of any of your lists.'}
      {/if}
    </p>
  {else}
    {foreach from=$savedData item="current"}
      <strong>{translate text='List'}: {$current.listTitle|escape:"html"}</strong>
      <input type="hidden" name="lists[]" value="{$current.listId}"/>
      <label class="displayBlock" for="edit_tags{$current.listId}">{translate text='Tags'}:</label>
      <input id="edit_tags{$current.listId}" type="text" name="tags{$current.listId}" value="{$current.tags|escape:"html"}" size="50"/>
      <label class="displayBlock" for="edit_notes{$current.listId}">{translate text='Notes'}:</label>
      <textarea id="edit_notes{$current.listId}" class="displayBlock" name="notes{$current.listId}" rows="3" cols="50">{$current.notes|escape:"html"}</textarea>
      <br/>
    {/foreach}
    <input class="button" type="submit" name="submit" value="{translate text='Save'}"/>
  {/if}
  </form>
</div>
