<h2>{translate text="User Account"}</h2>

{if $message}<div class="error">{$message|translate}</div>{/if}

<form method="post" action="{$url}/MyResearch/Account" name="accountForm" id="accountForm">
  <label class="span-3" for="account_firstname">{translate text="First Name"}:</label>
  <input id="account_firstname" type="text" name="firstname" value="{$formVars.firstname|escape}" size="30" 
    class="mainFocus {jquery_validation required='This field is required'}"/><br class="clear"/>
  <label class="span-3" for="account_lastname">{translate text="Last Name"}:</label>
  <input id="account_lastname" type="text" name="lastname" value="{$formVars.lastname|escape}" size="30"
    class="{jquery_validation required='This field is required'}"/><br class="clear"/>
  <label class="span-3" for="account_email">{translate text="Email Address"}:</label>
  <input id="account_email" type="text" name="email" value="{$formVars.email|escape}" size="30"
    class="{jquery_validation required='This field is required' email='Email address is invalid'}"/><br class="clear"/>
  <label class="span-3" for="account_username">{translate text="Desired Username"}:</label>
  <input id="account_username" type="text" name="username" value="{$formVars.username|escape}" size="30"
    class="{jquery_validation required='This field is required'}"/><br class="clear"/>
  <label class="span-3" for="account_password">{translate text="Password"}:</label>
  <input id="account_password" type="password" name="password" size="15"
    class="{jquery_validation required='This field is required'}"/><br class="clear"/>
  <label class="span-3" for="account_password2">{translate text="Password Again"}:</label>
  <input id="account_password2" type="password" name="password2" size="15"
    class="{jquery_validation required='This field is required' equalTo='Passwords do not match' equalToField='#account_password'}"/><br class="clear"/>
  <input class="push-3 button" type="submit" name="submit" value="{translate text="Submit"}"/>
  <div class="clear"></div>
</form>
<script>
  {literal}
  $(document).ready(function() {
    $('#accountForm').validate();
  });
  {/literal}
</script>