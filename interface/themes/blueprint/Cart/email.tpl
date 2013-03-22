{if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
{if $infoMsg}<div class="info">{$infoMsg|translate}</div>{/if}
  
<form action="{$url}/Cart/Home?email" method="post" name="bulkEmail">

  {foreach from=$emailList item=emailItem}
  <strong>{translate text='Title'}:</strong>
  {$emailItem.title|escape}<br />
  {/foreach}
  <br />
  <label class="displayBlock" for="email_to">{translate text='To'}:</label>
  <input id="email_to" type="text" name="to" size="40" class="mainFocus {jquery_validation required='This field is required' email='Email address is invalid'}"/>
  <label class="displayBlock" for="email_from">{translate text='From'}:</label>
  <input id="email_from" type="text" name="from" size="40" class="{jquery_validation required='This field is required' email='Email address is invalid'}"/>
  <label class="displayBlock" for="email_message">{translate text='Message'}:</label>
  <textarea id="email_message" name="message" rows="3" cols="40"></textarea>
  <br />
  <input class="button" type="submit" name="submit" value="{translate text='Send'}"/>
  {foreach from=$emailIDS item=emailID}
    <input type="hidden" name="ids[]" value="{$emailID|escape}"/>
  {/foreach}
  {if $followupModule}
    <input type="hidden" name="followup" value="1"/>
    <input type="hidden" name="followupModule" value="{$followupModule|escape}"/>
  {/if}
  {if $followupAction}
    <input type="hidden" name="followupAction" value="{$followupAction|escape}"/>
  {/if}
  {if $listID}
    <input type="hidden" name="listID" value="{$listID|escape}"/>
  {/if}
</form>

