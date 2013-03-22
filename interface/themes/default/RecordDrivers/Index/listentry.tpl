<div class="yui-ge">
  <input type="hidden" name="idsAll[]" value="{$listId|escape}" />
  <div class="yui-u first">
    <label for="listID{$listId|escape}" class="hiddenLabel">{translate text="Select"} {$listTitle|escape}</label>
    <input id="listID{$listId|escape}" type="checkbox" name="ids[]" value="{$listId|escape}" class="ui_checkboxes" />
    {if $listThumb}
      <img src="{$listThumb|escape}" class="alignleft" alt="{translate text='Cover Image'}"/>
    {else}
      <img src="{$path}/bookcover.php" class="alignleft" alt="{translate text='No Cover Image'}"/>
    {/if}

    <div class="resultitem">
      <a href="{$url}/Record/{$listId|escape:"url"}" class="title">{$listTitle|escape}</a><br>
      {if $listAuthor}
        {translate text='by'}: <a href="{$url}/Author/Home?author={$listAuthor|escape:"url"}">{$listAuthor|escape}</a><br>
      {/if}
      {if $listTags}
        {translate text='Your Tags'}:
        {foreach from=$listTags item=tag name=tagLoop}
          <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape:"html"}</a>{if !$smarty.foreach.tagLoop.last},{/if}
        {/foreach}
        <br>
      {/if}
      {if $listNotes}
        {translate text='Notes'}: 
        {foreach from=$listNotes item=note}
          {$note|escape:"html"}<br>
        {/foreach}
      {/if}

      {foreach from=$listFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
    </div>
  </div>

  {if $listEditAllowed}
    <div class="yui-u">
      <a href="{$url}/MyResearch/Edit?id={$listId|escape:"url"}{if !is_null($listSelected)}&amp;list_id={$listSelected|escape:"url"}{/if}" class="edit tool">{translate text='Edit'}</a>
      {* Use a different delete URL if we're removing from a specific list or the overall favorites: *}
      <a
      {if is_null($listSelected)}
        href="{$url}/MyResearch/Favorites?delete={$listId|escape:"url"}"
      {else}
        href="{$url}/MyResearch/MyList/{$listSelected|escape:"url"}?delete={$listId|escape:"url"}"
      {/if}
      class="delete tool" onClick="return confirm('{translate text='confirm_delete'}');">{translate text='Delete'}</a>
    </div>
  {/if}
</div>
