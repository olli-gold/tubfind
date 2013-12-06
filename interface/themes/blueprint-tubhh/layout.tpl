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

    <title>{translate text="TUHH Bibliothek"}: {$pageTitle|truncate:64:"..."}</title>

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

    <link rel="stylesheet" type="text/css" media="screen, projection" href="{$url}/interface/themes/blueprint/js/jquery-ui-1.8.7.custom/css/smoothness/jquery-ui-1.8.7.custom.css" />

    {* Load VuFind specific stylesheets *}
    {css media="screen, projection" filename="styles.css"}
    {css media="screen" filename="print.css"}
    <!--[if lt IE 8]><link rel="stylesheet" href="{$url}/interface/themes/blueprint/css/ie.css" type="text/css" media="screen, projection"><![endif]-->

	{* Load jQuery framework and plugins *}
    {js filename="jquery-1.4.4.min.js"}
    {js filename="jquery.form.js"}
    {js filename="jquery.metadata.js"}
    {js filename="jquery.validate.min.js"}    
    
    {* Load jQuery UI *}
    {js filename="jquery-ui-1.8.7.custom/js/jquery-ui-1.8.7.custom.min.js"}

    {* Load dialog/lightbox functions *}
    {js filename="lightbox.js"}

    {* Load common javascript functions *}
    {js filename="common.js"}
    {js filename="dbr.js"}


    {js filename="jquery.cookie.js"}

    {if $bookBag}
      <script type="text/javascript">
      var vufindString = Array();
      vufindString.bulk_noitems_advice = "{translate text="bulk_noitems_advice"}";
      vufindString.confirmEmpty = "{translate text="bookbag_confirm_empty"}";
      vufindString.viewBookBag = "{translate text="View Book Bag"}";
      vufindString.addBookBag = "{translate text="Add to Book Bag"}";
      vufindString.removeBookBag = "{translate text="Remove from Book Bag"}";
      vufindString.itemsAddBag = "{translate text="items_added_to_bookbag"}";
      vufindString.itemsInBag = "{translate text="items_already_in_bookbag"}";
      vufindString.bookbagMax = "{$bookBag->getMaxSize()}";
      vufindString.bookbagFull = "{translate text="bookbag_full_msg"}";
      vufindString.bookbagStatusFull = "{translate text="bookbag_full"}";
      </script>

      {js filename="cart.js"}
      {assign var=bookBagItems value=$bookBag->getItems()}
    {/if}

  </head>

  <body>
    <div class="container">
	  <div class="header">
		{include file="header.tpl"}
	  </div>
          <div id="access">
              {include file="topnav.tpl"}
          </div>
          <div>&nbsp;</div>

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
          <div style="float:right;width:auto;margin-right:20px;margin-top:-100px;">
            {include file="pwmenu.tpl"}
          </div>
          <div class="clear"></div>
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
        {if $showtabs || $useSolr || $useWorldcat || $useSummon}
        {if $gbvmessage}<span style="color:red">{$gbvmessage}</span>{/if}
        <div id="toptab">
          <ul>
            {if $showtabs}
<!--
            <li{if $tab == "gbv" || $tab == ""} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=GBV Central&tab=gbv&localonly=1">{translate text="GBV Discovery"}</a></li>
            <li{if $tab == "localonly"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=localbiblio&tab=localonly">{translate text="Lokaler Index"}</a></li>
            <li{if $tab == "wwwtub"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=wwwtub&tab=wwwtub">{translate text="TUBHH Webseiten"}</a></li>
            <li{if $tab == "tubdok"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=TUBdok&tab=tubdok">{translate text="TUBdok"}</a></li>
            <li{if $tab == "all"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=GBV Central&shard[]=TUBdok&shard[]=wwwtub&tab=all&localonly=1">{translate text="Alles mit GBV Discovery"}</a></li>
            <li{if $tab == "nogbvall"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=localbiblio&shard[]=TUBdok&shard[]=wwwtub&tab=nogbvall">{translate text="Alles ohne GBV Discovery"}</a></li>
            <li{if $tab == "tuhh"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=TUHH Test&tab=tuhh">{translate text="Test: TUHH Webseiten"}</a></li>
-->
            <li{if $tab == "all"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=GBV Central&shard[]=TUBdok&shard[]=wwwtub&tab=all">{translate text="Books and more"}</a></li>
            <li{if $tab == "primo"} class="active"{/if}><a href="{$url}/Search/Results?lookfor={$lookfor|escape:"url"}&type=AllFields&view=list&shard[]=Primo Central&tab=primo">{translate text="Articles and more"}</a></li>
            {/if}
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
        <div class="clear" style="height:0px;">&nbsp;</div>
        {/if}
        {include file="$module/$pageTemplate"}
	  </div>

	  <div class="footer">
		{include file="footer-tub.tpl"}
	  </div>
    </div>
        
  </body>
</html>
