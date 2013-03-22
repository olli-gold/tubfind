<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$userLang}" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <title>{translate text="MyResearch Help"}</title>
    {css media="screen" filename="help.css"}
  </head>
  <body>
    {if $warning}
      <p class="warning">
        {translate text='Sorry, but the help you requested is unavailable in your language.'}
      </p>
    {/if}
    {include file="$pageTemplate"}
  </body>
</html>
