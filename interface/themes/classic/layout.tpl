<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="{$userLang}">
{* These variables can be used to hide the logo and/or search box in some contexts: *}
{assign var="showTopSearchBox" value=1}
{assign var="showTopLogo" value=1}
  <head>
    <title>{$pageTitle|truncate:64:"..."}</title>
    {if $addHeader}{$addHeader}{/if}
    <link rel="search" type="application/opensearchdescription+xml" title="Library Catalog Search" href="{$url}/Search/OpenSearch?method=describe">
    {css media="screen" filename="styles.css"}
    {css media="print" filename="print.css"}
    {css media="handheld" filename="print.css"}
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <script language="JavaScript" type="text/javascript">
      path = '{$url}';
    </script>

    {js filename="yui/yahoo-dom-event.js"}
    {js filename="yui/connection-min.js"}
    {js filename="yui/datasource-min.js"}
    {js filename="yui/autocomplete-min.js"}
    {js filename="yui/dragdrop-min.js"}
    {js filename="scripts.js"}
    {js filename="rc4.js"}
    {js filename="ajax.yui.js"}
  </head>

  <body>

    {* LightBox *}
    <div id="lightboxLoading" style="display: none;">{translate text="Loading"}...</div>
    <div id="lightboxError" style="display: none;">{translate text="lightbox_error"}</div>
    <div id="lightbox" onClick="hideLightbox(); return false;"></div>
    <div id="popupbox" class="popupBox"></div>
    {* End LightBox *}

    {* Change id for page width, class for menu layout. *}
    <div id="doc2" class="yui-t{if $sidebarOnLeft}3{else}5{/if}">

      <div id="hd">
        {* Your header. Could be an include. *}
        {if $showTopLogo}
          <a href="{$url}"><img src="{$path}/images/vufind.jpg" alt="vufinder"></a>
        {/if}
      </div>

      {* Search box. This should really be coming from the include. *}
      <div class="searchheader">
        <div class="searchcontent">
          <div class="alignright">
            <div id="logoutOptions"{if !$user} style="display: none;"{/if}>
              <a href="{$path}/MyResearch/Home">{translate text="Your Account"}</a> |
              <a href="{$path}/MyResearch/Logout">{translate text="Log Out"}</a>
            </div>
            <div id="loginOptions"{if $user} style="display: none;"{/if}>
              {if $authMethod == 'Shibboleth'}
                <a href="{$sessionInitiator}">{translate text="Institutional Login"}</a>
              {else}
                <a href="{$path}/MyResearch/Home">{translate text="Login"}</a>
              {/if}
            </div>
            {if is_array($allLangs) && count($allLangs) > 1}
              <form method="post" name="langForm" action="">
                <div class="hiddenLabel"><label for="mylang">{translate text="Language"}:</label></div>
                <select id="mylang" name="mylang" onChange="document.langForm.submit();">
                  {foreach from=$allLangs key=langCode item=langName}
                    <option value="{$langCode}"{if $userLang == $langCode} selected{/if}>{translate text=$langName}</option>
                  {/foreach}
                </select>
                <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
              </form>
            {/if}
          </div>

        {if $showTopSearchBox}
          {if $pageTemplate != 'advanced.tpl'}
            {if $module=="Summon" || $module=="WorldCat" || $module=="Authority"}
              {include file="`$module`/searchbox.tpl"}
            {else}
              {include file="Search/searchbox.tpl"}
            {/if}
          {/if}
        {/if}

        </div>
      </div>

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
      <div style="clear: left;"></div>
      {/if}

      {include file="$module/$pageTemplate"}

      <div id="ft">
      {include file="footer.tpl"}
      </div>
    </div>
  </body>
</html>
