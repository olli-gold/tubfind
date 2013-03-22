<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>Synonyms Configuration</h1>
  {include file="Admin/savestatus.tpl"}

  <p>
    Synonyms are words that have the same meaning.
    Mappings work by either assignment or grouping.
    Assignment works just like a translation, for example:
  </p>
  <pre>colour => color</pre>
  
  <p>Grouping allows each term to have a match on any other term in the group, for example:</p>
  <pre>
GB,gib,gigabyte,gigabytes
MB,mib,megabyte,megabytes
Television, Televisions, TV, TVs
  </pre>

  <form method="post" action="{$url}/Admin/Config?file=synonyms.txt">
    <label class="displayBlock" for="conf_synonyms">{translate text="Synonyms"}:</label>
    <textarea id="conf_synonyms" name="synonyms" rows="20" cols="70">{$synonyms|escape}</textarea><br/>
    <input type="submit" name="submit" value="{translate text="Save"}"/>
  </form>
</div>

<div class="clear"></div>
