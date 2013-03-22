{capture name=pagelinks}
  <div class="alphaBrowsePageLinks">
    {if isset ($prevpage)}
      <div class="alphaBrowsePrevLink"><a href="{$path}/AlphaBrowse/Results?source={$source|escape:"url"}&amp;from={$from|escape:"url"}&amp;page={$prevpage|escape:"url"}">&laquo; Prev</a></div>
    {/if}

    {if isset ($nextpage)}
      <div class="alphaBrowseNextLink"><a href="{$path}/AlphaBrowse/Results?source={$source|escape:"url"}&amp;from={$from|escape:"url"}&amp;page={$nextpage|escape:"url"}">Next &raquo;</a></div>
    {/if}
    <div class="clear"></div>
  </div>
{/capture}

<div class="span-18{if $sidebarOnLeft} push-5 last{/if}">
  <div class="resulthead">
    <form method="get" action="{$path}/AlphaBrowse/Results" name="alphaBrowseForm" id="alphaBrowseForm">
      <label for="alphaBrowseForm_source">{translate text='Browse Alphabetically'}</label>
      <select id="alphaBrowseForm_source" name="source">
        {foreach from=$alphaBrowseTypes key=key item=item}
          <option value="{$key|escape}" {if $source == $key}selected="selected"{/if}>{translate text=$item}</option>
        {/foreach}
      </select>
      <label for="alphaBrowseForm_from">{translate text='starting from'}</label>
      <input type="text" name="from" id="alphaBrowseForm_from" value="{$from|escape}"/>
      <input type="submit" value="{translate text='Browse'}"/>
    </form>
  </div>

  {if $result}
    <div class="alphaBrowseResult">
    {$smarty.capture.pagelinks}

    <div class="alphaBrowseHeader">{translate text="alphabrowse_matches"}</div>
      {foreach from=$result.Browse.items item=item name=recordLoop}
      <div class="alphaBrowseEntry {if ($smarty.foreach.recordLoop.iteration % 2) == 0}alt {/if}">
      <div class="alphaBrowseHeading">
        {if $item.count > 0}
        {capture name="searchLink"}
          {* linking using bib ids is generally more reliable than
           doing searches for headings, but headings give shorter
           queries and don't look as strange. *}
          {if $item.count < 5}
          {$path}/Search/Results?type=ids&amp;lookfor={foreach from=$item.ids item=id}{$id}+{/foreach}
          {else}
          {$path}/Search/Results?type={$source|capitalize|escape:"url"}Browse&amp;lookfor={$item.heading|escape:"url"}
          {/if}
        {/capture}
        <a href="{$smarty.capture.searchLink|trim}">{$item.heading|escape:"html"}</a>
        {else}
        {$item.heading|escape:"html"}
        {/if}
      </div>
      <div class="alphaBrowseCount">{if $item.count > 0}{$item.count}{/if}</div>
      <div class="clear"></div>

      {if $item.useInstead|@count > 0}
        <div class="alphaBrowseRelatedHeading">
        <div class="title">{translate text="Use instead"}:</div>
        <ul>
          {foreach from=$item.useInstead item=heading}
          <li><a href="{$path}/AlphaBrowse/Results?source={$source|escape:"url"}&amp;from={$heading|escape:"url"}">{$heading|escape:"html"}</a></li>
          {/foreach}
        </ul>
        </div>
      {/if}

      {if $item.seeAlso|@count > 0}
        <div class="alphaBrowseRelatedHeading">
        <div class="title">{translate text="See also"}:</div>
        <ul>
          {foreach from=$item.seeAlso item=heading}
          <li><a href="{$path}/AlphaBrowse/Results?source={$source|escape:"url"}&amp;from={$heading|escape:"url"}">{$heading|escape:"html"}</a></li>
          {/foreach}
        </ul>
        </div>
      {/if}

      {if $item.note}
        <div class="alphaBrowseRelatedHeading">
        <div class="title">{translate text="Note"}:</div>
        <ul>
          <li>{$item.note|escape:"html"}</li>
        </ul>
        </div>
      {/if}

      </div>
      {/foreach}

    {$smarty.capture.pagelinks}
    </div>
  {/if}
</div>

<div class="span-5 {if $sidebarOnLeft}pull-18 sidebarOnLeft{else}last{/if}">
</div>

<div class="clear"></div>
