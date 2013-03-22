<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>
<div class="span-18 last">
  <h1>{translate text="Protected Words Configuration"}</h1>
  {include file="Admin/savestatus.tpl"}

  <p>
    The Protected Words are a list of words that will prevent VuFind from using word stemming on.
  </p>

  <form method="post" action="{$url}/Admin/Config?file=protwords.txt">
    <label class="displayBlock" for="conf_protwords">{translate text="Protected Words"}:</label>
    <textarea id="conf_protwords" name="protwords" rows="20" cols="20">{$protwords|escape}</textarea><br/>
    <input type="submit" name="submit" value="{translate text="Save"}"/>
  </form>
</div>

<div class="clear"></div>
