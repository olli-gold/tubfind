<div class="listentry recordId" id="record{$listId|escape}">
    <label for="checkbox_{$listId|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
    <input id="checkbox_{$listId|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$listId|escape}" class="checkbox"/>
    <input type="hidden" name="idsAll[]" value="{$listId|escape}" />
    <div class="span-2">
    {if $listThumb}
      <img src="{$listThumb|escape}" class="summcover" alt="{translate text='Cover Image'}"/>
    {else}
      <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
    {/if}
    </div>
    <div class="span-10">
      <a href="{$url}/Record/{$listId|escape:"url"}" class="title">
      {if is_array($listTitle)}
          {$listTitle.0|escape}
      {else}
          {$listTitle|escape}
      {/if}
      </a><br/>
      {if $listAuthor}
        {translate text='by'}: <a href="{$url}/Search/Results?lookfor={$listAuthor|escape:"url"}&type=Author&localonly=1">{$listAuthor|escape}</a><br/>
      {/if}
      {if $listTags}
        <strong>{translate text='Your Tags'}:</strong>
        {foreach from=$listTags item=tag name=tagLoop}
          <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape:"html"}</a>{if !$smarty.foreach.tagLoop.last},{/if}
        {/foreach}
        <br/>
      {/if}
      {if $listNotes}
        <strong>{translate text='Notes'}:</strong>
        {if count($listNotes) > 1}<br/>{/if}
        {foreach from=$listNotes item=note}
          {$note|escape:"html"}<br/>
        {/foreach}
      {/if}

      {foreach from=$listFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
    </div>

  {if $listEditAllowed}
    <div class="floatright">
      <a href="{$url}/MyResearch/Edit?id={$listId|escape:"url"}{if !is_null($listSelected)}&amp;list_id={$listSelected|escape:"url"}{/if}" class="edit tool">{translate text='Edit'}</a>
      {* Use a different delete URL if we're removing from a specific list or the overall favorites: *}
      <a
      {if is_null($listSelected)}
        href="{$url}/MyResearch/Favorites?delete={$listId|escape:"url"}"
      {else}
        href="{$url}/MyResearch/MyList/{$listSelected|escape:"url"}?delete={$listId|escape:"url"}"
      {/if}
      class="delete tool" onclick="return confirm('{translate text='confirm_delete'}');">{translate text='Delete'}</a>
    </div>
  {/if}

  <div class="clear"></div>
</div>