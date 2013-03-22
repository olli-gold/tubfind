<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$userLang}" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>

    <title>{translate text="VuFind Administration"} - {$pageTitle}</title>

    {* Load Blueprint CSS framework *}
    {css media="screen, projection" filename="blueprint/screen.css"}
    {css media="print" filename="blueprint/print.css"}
    <!--[if lt IE 8]><link rel="stylesheet" href="blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->

    {* Load VuFind specific stylesheets *}
    {css media="screen" filename="styles.css"}
    {css media="print" filename="print.css"}
  </head>

  <body>
    <div class="container">
      <div class="header">
        {include file="header.tpl"}
      </div>

      {if $showBreadcrumbs}
      <div class="breadcrumbs">
        <div class="breadcrumbinner">
          <a href="{$url}">{translate text="Home"}</a> <span>&gt;</span>
          {include file="$module/breadcrumbs.tpl"}
        </div>
      </div>
      {/if}

      <div class="main">
        {include file="$module/$pageTemplate"}
      </div>

      <div class="footer">
        {include file="footer.tpl"}
      </div>
    </div>
  </body>
</html>