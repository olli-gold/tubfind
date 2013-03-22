<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="Stop Words Configuration"}</h1>
  {include file="Admin/savestatus.tpl"}
  
  <p>
    The Stop Words are a list of words that VuFind will ignore when a user searches for the term.
    Each word should be on a new line.
  </p>

  <form method="post" action="{$url}/Admin/Config?file=stopwords.txt">
    <label class="displayBlock" for="conf_stopwords">{translate text="Stopwords"}:</label>
    <textarea id="conf_stopwords" name="stopwords" rows="20" cols="20">{$stopwords|escape}</textarea><br/>
    <input type="submit" name="submit" value="{translate text="Save"}"/>
  </form>
</div>

<div class="clear"></div>
