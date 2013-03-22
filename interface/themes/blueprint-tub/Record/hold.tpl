<form action="{$url}/Record/{$id|escape:"url"}/Hold" method="post" onsubmit="PlaceHold({$id|escape}, this); return false;">
  <label class="displayBlock" for="hold_username">{translate text='Username'}:</label>
  <input id="hold_username" type="text" name="username" size="40"/>
  <label class="displayBlock" for="hold_password">{translate text='Password'}:</label>
  <input id="hold_password" type="password" name="password" size="40"/>
  <input class="button" type="submit" name="submit" value="{translate text='Submit'}"/>
</form>