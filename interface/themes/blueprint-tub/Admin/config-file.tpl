<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{$pageTitle}</h1>
  
  {include file="Admin/savestatus.tpl"}
  
  <p>
    You are viewing the file at {$configPath}.
  </p>

  <form method="post" action="">
    <label class="displayBlock" for="conf_config_file">{$_GET.file}</label>
    <textarea id="conf_config_file" name="config_file" rows="20" cols="70" class="configEditor">{$configFile|escape}</textarea><br/>
    <input type="submit" name="submit" value="{translate text="Save"}"/>
  </form>
</div>

<div class="clear"></div>
