<form action="{$url}/Record/{$id|escape}/AddTag" method="post" name="tagRecord">
  <input type="hidden" name="submit" value="1" />
  <input type="hidden" name="id" value="{$id|escape}" />
  <label for="addtag_tag">{translate text="Tags"}:</label>
  <input id="addtag_tag" type="text" name="tag" value="" size="40"  class="mainFocus {jquery_validation required='This field is required'}"/>
  <p>{translate text="add_tag_note"}</p>
  <input type="submit" value="{translate text='Save'}"/>
</form>