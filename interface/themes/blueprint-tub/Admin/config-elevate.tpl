<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="Elevated Terms Configuration"}</h1>
  {include file="Admin/savestatus.tpl"}

  <p>
    Elevated terms are terms that have a more important meaning.
    These words will make matching records match higher in the results.
  </p>

  <form method="post" action="">
    Term:<br><input type="text" name="term[]" value=""><br>
    Record IDs:<br><textarea name="idList[]" rows="5" cols="20">{$idList}</textarea><br>
    Term:<br><input type="text" name="term[]" value=""><br>
    Record IDs:<br><textarea name="idList[]" rows="5" cols="20">{$idList}</textarea><br>
    Term:<br><input type="text" name="term[]" value=""><br>
    Record IDs:<br><textarea name="idList[]" rows="5" cols="20">{$idList}</textarea><br>
    <input type="submit" name="submit" value="Save">
  </form>
</div>

<div class="clear"></div>
