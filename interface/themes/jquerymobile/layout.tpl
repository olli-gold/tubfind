<!DOCTYPE html> 
<html> 
  <head>
    <meta charset="utf-8"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1"/> 
    <title>{$site.title|escape}</title>

    {* Set global javascript variables *}
    <script type="text/javascript">
    //<![CDATA[
      var path = '{$path}';
    //]]>
    </script>

    {css filename="jquery.mobile-1.0rc2.min.css"}
    {js filename="jquery-1.6.4.min.js"}
    {js filename="common.js"}
    {js filename="jquery.mobile-1.0rc2.min.js"}
    {js filename="jquery.cookie.js"}
    {js filename="cart_cookie.js"}
    {js filename="cart.js"}
    {js filename="scripts.js"}    
    {css filename="styles.css"}
    {css filename="formats.css"}
  </head> 
  <body>
    {include file="$module/$pageTemplate"}   
    <div data-role="dialog" id="Language-dialog">
      <div data-role="header" data-theme="d" data-position="inline">
        <h1>{translate text="Language"}</h1>
      </div>
      <div data-role="content">
        {if is_array($allLangs) && count($allLangs) > 1}
        <form method="post" name="langForm" action="#" id="langForm" data-ajax="false">
          <div data-role="fieldcontain">
            <label for="langForm_mylang">{translate text="Language"}:</label>
            <select id="langForm_mylang" name="mylang">
              {foreach from=$allLangs key=langCode item=langName}
                <option value="{$langCode}"{if $userLang == $langCode} selected="selected"{/if}>{translate text=$langName}</option>
              {/foreach}
            </select>
            <input type="submit" value="{translate text='Set'}" />
          </div>
        </form>
        {/if}
      </div>
    </div>
  </body>
</html>
