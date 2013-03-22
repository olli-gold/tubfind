<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$userLang}" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>

    <title>{translate text='An error has occurred'}</title>

    <link rel="search" type="application/opensearchdescription+xml" title="Library Catalog Search" href="{$url}/Search/OpenSearch?method=describe" />
    
    {* Load Blueprint CSS framework *}
    {css media="screen, projection" filename="blueprint/screen.css"}
    {css media="print" filename="blueprint/print.css"}
    <!--[if lt IE 8]><link rel="stylesheet" href="blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
    
    {* Load VuFind specific stylesheets *}
    {css media="screen" filename="styles.css"}
    {css media="screen" filename="print.css"}
    
    {* Set global javascript variables *}
    <script type="text/javascript">
    //<![CDATA[
      var path = '{$url}';
    //]]>
    </script>

	{* Load jQuery framework *}
    {js filename="jquery-1.4.4.min.js"}
    
    {* Load common javascript functions *}
    {js filename="common.js"}

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
        <div class="error fatalError">
          <h1>{translate text="An error has occurred"}</h1>
          <p class="errorMsg">{$error->getMessage()}</p>
          {if $debug}
            <p class="errorStmt">{$error->getDebugInfo()}</p>
          {/if}
          <p>
            {translate text="Please contact the Library Reference Department for assistance"}
            <br/>
            <a href="mailto:{$supportEmail}">{$supportEmail}</a>
          </p>
          {if $debug}
          <div class="debug">
            <h2>{translate text="Debug Information"}</h2>
            {assign var=errorCode value=$error->getCode()}
            {if $errorCode}
            <p class="errorMsg">{translate text="Code"}: {$errorCode}</p>
            {/if}
            <p>{translate text="Backtrace"}:</p>
            <code>
            {foreach from=$error->backtrace item=trace}
              [{$trace.line}] {$trace.file}<br/>
            {/foreach}
            </code>
          </div>
          {/if}
        </div>
	  </div>

	  <div class="footer">
          {include file="footer.tpl"}
	  </div>
    </div>
  </body>
</html>