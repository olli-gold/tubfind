{if $message}<div class="error">{$message|translate}</div>{/if}

<form method="post" action="{$url}{$formTargetPath|escape}" name="smsRecord">
  <input type="hidden" name="id" value="{$id|escape}" />
  <input type="hidden" name="type" value="{$module|escape}" />
  <label class="span-2" for="sms_to">{translate text="Number"}:</label>
  <input id="sms_to" type="text" name="to" value="{translate text="sms_phone_number"}" 
        onfocus="if (this.value=='{translate text="sms_phone_number"}') this.value=''" 
        onblur="if (this.value=='') this.value='{translate text="sms_phone_number"}'"
        class="{jquery_validation required='This field is required' phoneUS='Invalid phone number.'}"/>
  <br/>
  <label class="span-2" for="sms_provider">{translate text="Provider"}:</label>
  <select id="sms_provider" name="provider" class="{jquery_validation required='This field is required'}">
    <option selected="selected" value="">{translate text="Select your carrier"}</option>
    {foreach from=$carriers key=val item=details}
      <option value="{$val}">{$details.name|escape}</option>
    {/foreach}
  </select>
  <br/>
  <input class="button" type="submit" name="submit" value="{translate text="Send"}"/>
</form>
