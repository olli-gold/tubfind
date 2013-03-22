{if $errorMsg}<div class="error">{$errorMsg|translate}</div>{/if}
{if $infoMsg}<div class="userMsg">{$infoMsg|translate}</div>{/if}

<div id="popupMessages"></div>
<div id="popupDetails">
<form method="post" action="{$url}{$formTargetPath|escape}" name="popupForm"
      onSubmit='sendSMS(&quot;{$id|escape}&quot;, this.elements[&quot;to&quot;].value, 
                this.elements[&quot;provider&quot;][this.elements[&quot;provider&quot;].selectedIndex].value,
                &quot;{$module|escape}&quot;,
                {* Pass translated strings to Javascript -- ugly but necessary: *}
                {literal}{{/literal}sending: &quot;{translate text='sms_sending'}&quot;, 
                 success: &quot;{translate text='sms_success'}&quot;,
                 failure: &quot;{translate text='sms_failure'}&quot;{literal}}{/literal}
                ); return false;'>
  <table>
  <tr>
    <td><label for="number">{translate text="Number"}:</label></td>
    <td>
      <input type="text" name="to" id="number" value="{translate text="sms_phone_number"}" 
        onfocus="if (this.value=='{translate text="sms_phone_number"}') this.value=''" 
        onblur="if (this.value=='') this.value='{translate text="sms_phone_number"}'">
    </td>
  </tr>
  <tr>
    <td><label for="provider">{translate text="Provider"}:</label></td>
    <td>
      <select name="provider" id="provider">
        <option selected="selected" value="">{translate text="Select your carrier"}</option>
        {foreach from=$carriers key=val item=details}
        <option value="{$val}">{$details.name|escape}</option>
        {/foreach}
      </select>
    </td>
  </tr>
  <tr>
    <td></td>
    <td><input type="submit" name="submit" value="{translate text="Send"}"></td>
  </tr>
  </table>
</form>
</div>