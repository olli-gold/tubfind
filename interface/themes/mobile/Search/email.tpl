<div align="left">
  {if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
  {if $infoMsg}<div class="userMsg">{$infoMsg|translate}</div>{/if}

  <div id="popupMessages"></div>
  <div id="popupDetails"> 
  <form action="{$url}/Search/Email" method="post" onSubmit='SendURLEmail(this.elements[&quot;to&quot;].value, 
    this.elements[&quot;from&quot;].value, this.elements[&quot;message&quot;].value,
    {* Pass translated strings to Javascript -- ugly but necessary: *}
    {literal}{{/literal}sending: &quot;{translate text='email_sending'}&quot;, 
     success: &quot;{translate text='email_success'}&quot;,
     failure: &quot;{translate text='email_failure'}&quot;{literal}}{/literal}
    ); return false;'>
    <input type="hidden" name="url" value="{$searchURL|escape:"html"}">
    <strong><label for="to">{translate text='To'}:</label></strong><br>
    <input type="text" name="to" size="40" id="to"><br>
    <strong><label for="from">{translate text='From'}:</label></strong><br>
    <input type="text" name="from" size="40" id="from"><br>
    <strong><label for="message">{translate text='Message'}:</label></strong><br>
    <textarea name="message" rows="3" cols="40" id="message"></textarea><br>
    <input type="submit" name="submit" value="{translate text='Send'}">
  </form>
  </div>
</div>