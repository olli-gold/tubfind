{assign var=records value=$bookBag->getRecordDetails()}
{if !empty($records)}
  <ul class="cartContent">
  {foreach from=$records item=record}
    {* assuming we're dealing with VuFind records *}
    <li>
      <label for="checkbox_{$record.id|regex_replace:'/[^a-z0-9]/':''|escape}" class="offscreen">{translate text="Select this record"}</label>
      <input id="checkbox_{$record.id|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$record.id|escape}" class="checkbox"/>
      <input type="hidden" name="idsAll[]" value="{$record.id|escape}" />
      <a title="{translate text='View Record'}" href="{$url}/Record/{$record.id|escape}">{$record.title|escape}</a>
    </li>
  {/foreach}
  </ul>
{else}
  <p>{translate text='bookbag_is_empty'}.</p>
{/if}
