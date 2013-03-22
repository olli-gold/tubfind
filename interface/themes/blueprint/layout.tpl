<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$userLang}" xml:lang="en">

{* We should hide the top search bar and breadcrumbs in some contexts: *}
{if ($module=="Search" || $module=="Summon" || $module=="WorldCat" || $module=="Authority") && $pageTemplate=="home.tpl"}
    {assign var="showTopSearchBox" value=0}
    {assign var="showBreadcrumbs" value=0}
{else}
    {assign var="showTopSearchBox" value=1}
    {assign var="showBreadcrumbs" value=1}
{/if}

  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    {if $addHeader}{$addHeader}{/if}

    <title>{$pageTitle|truncate:64:"..."}</title>

    {if $module=='Record' && $hasRDF}
    <link rel="alternate" type="application/rdf+xml" title="RDF Representation" href="{$url}/Record/{$id|escape}/RDF"/>    
    {/if}
    <link rel="search" type="application/opensearchdescription+xml" title="Library Catalog Search" href="{$url}/Search/OpenSearch?method=describe" />
    
    {* Load Blueprint CSS framework *}
    {css media="screen, projection" filename="blueprint/screen.css"}
    {css media="print" filename="blueprint/print.css"}
    <!--[if lt IE 8]><link rel="stylesheet" href="{$url}/interface/themes/blueprint/css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
    
    {* Set global javascript variables *}
    <script type="text/javascript">
    //<![CDATA[
      var path = '{$url}';
    //]]>
    </script>

	{* Load jQuery framework and plugins *}
    {js filename="jquery-1.4.4.min.js"}
    {js filename="jquery.form.js"}
    {js filename="jquery.metadata.js"}
    {js filename="jquery.validate.min.js"}    
    
    {* Load jQuery UI *}
    {js filename="jquery-ui-1.8.7.custom/js/jquery-ui-1.8.7.custom.min.js"}
    <link rel="stylesheet" type="text/css" media="screen, projection" href="{$url}/interface/themes/blueprint/js/jquery-ui-1.8.7.custom/css/smoothness/jquery-ui-1.8.7.custom.css" />
        
    {* Load dialog/lightbox functions *}
    {js filename="lightbox.js"}

    {* Load common javascript functions *}
    {js filename="common.js"}

    {* Load VuFind specific stylesheets *}
    {css media="screen, projection" filename="styles.css"}
    {css media="print" filename="print.css"}
    <!--[if lt IE 8]><link rel="stylesheet" href="{$url}/interface/themes/blueprint/css/ie.css" type="text/css" media="screen, projection"><![endif]-->
  </head>

  <body>
    <div class="container">
	  <div class="header">
		{include file="header.tpl"}
	  </div>

	  {if $showTopSearchBox}
	  <div class="searchbox">
        {if $pageTemplate != 'advanced.tpl'}
          {if $module=="Summon" || $module=="WorldCat" || $module=="Authority"}
            {include file="`$module`/searchbox.tpl"}
          {else}
            {include file="Search/searchbox.tpl"}
          {/if}
        {/if}
	  </div>
	  {/if}

      {if $showBreadcrumbs}
      <div class="breadcrumbs">
        <div class="breadcrumbinner">
          <a href="{$url}">{translate text="Home"}</a> <span>&gt;</span>
          {include file="$module/breadcrumbs.tpl"}
        </div>
      </div>
      {/if}

	  <div class="main">
        {if $useSolr || $useWorldcat || $useSummon}
        <div id="toptab">
          <ul>
            {if $useSolr}
            <li{if $module != "WorldCat" && $module != "Summon"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}">{translate text="University Library"}</a></li>
            {/if}
            {if $useWorldcat}
            <li{if $module == "WorldCat"} class="active"{/if}><a href="{$url}/WorldCat/Search?lookfor={$lookfor|escape:"url"}">{translate text="Other Libraries"}</a></li>
            {/if}
            {if $useSummon}
            <li{if $module == "Summon"} class="active"{/if}><a href="{$url}/Summon/Search?lookfor={$lookfor|escape:"url"}">{translate text="Journal Articles"}</a></li>
            {/if}
          </ul>
        </div>        
        {/if}
        {include file="$module/$pageTemplate"}
	  </div>

	  <div class="footer">
		{include file="footer.tpl"}
	  </div>
    </div>
  </body>
</html>