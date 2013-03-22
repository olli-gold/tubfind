<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="VuFind Configuration"}</h1>
  {include file="Admin/savestatus.tpl"}

  <form method="post" action="">
    <table class="citation">
      <tr>
        <th colspan="2">{translate text="Web Site Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="This section will need to be customized for your installation"}</td>
      </tr>
      <tr>
        <th>{translate text="Web Path"}: </th>
        <td><input type="text" name="webpath" value="{$config.Site.path}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Web URL"}: </th>
        <td><input type="text" name="weburl" value="{$config.Site.url}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Local Path"}: </th>
        <td><input type="text" name="localpath" value="{$config.Site.local}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Web Title"}: </th>
        <td><input type="text" name="title" value="{$config.Site.title}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Email Contact"}: </th>
        <td><input type="text" name="email" value="{$config.Site.email}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Web Language"}: </th>
        <td><input type="text" name="language" value="{$config.Site.language}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Locale"}: </th>
        <td><input type="text" name="locale" value="{$config.Site.locale}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Theme"}: </th>
        <td>
          <select name="theme">
          {foreach from=$themeList item=theme}
            <option value="{$theme}"{if $config.Site.theme == $theme} selected="selected"{/if}>{$theme}</option>
          {/foreach}
          </select>
        </td>
      </tr>
      <tr>
        <th colspan="2">{translate text="ILS Connection Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text='Please set the ILS that VuFind will interact with. Or leave "Sample" for testing purposes.'}</td>
      </tr>
      <tr>
        <th>{translate text="ILS"}: </th>
        <td>
          <select name="ils">
    <option value="Sample"{if $config.Catalog.driver == "Sample"} selected="selected"{/if}>Sample</option>
    <option value="Aleph"{if $config.Catalog.driver == "Aleph"} selected="selected"{/if}>Aleph</option>
    <option value="Evergreen"{if $config.Catalog.driver == "Evergreen"} selected="selected"{/if}>Evergreen</option>    
    <option value="Koha"{if $config.Catalog.driver == "Koha"} selected="selected"{/if}>Koha</option>    
    <option value="III"{if $config.Catalog.driver == "III"} selected="selected"{/if}>Innovative</option>
    <option value="Unicorn"{if $config.Catalog.driver == "Unicorn"} selected="selected"{/if}>Unicorn</option>
    <option value="Voyager"{if $config.Catalog.driver == "Voyager"} selected="selected"{/if}>Voyager</option>
          </select>
        </td>
      </tr>
      <tr>
        <th colspan="2">{translate text="Local Database Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="This section needs to be changed to match your installation path and database connection information"}</td>
      </tr>
      <tr>
        <th>{translate text="Username"}: </th>
        <td><input type="text" name="dbusername" value="{$config.Database.database}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Password"}: </th>
        <td><input type="text" name="dbpassword" value="{$config.Database.database}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Server"}: </th>
        <td><input type="text" name="dbhost" value="{$config.Database.database}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Database Name"}: </th>
        <td><input type="text" name="dbname" value="{$config.Database.database}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="Mail Server Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="This section should not need to be changed"}</td>
      </tr>
      <tr>
        <th>{translate text="Mail Server"}: </th>
        <td><input type="text" name="mailhost" value="{$config.Mail.host}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Mail Port"}: </th>
        <td><input type="text" name="mailport" value="{$config.Mail.port}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="Book Cover Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="Book Covers are Optional. You can select from Syndetics, Amazon or Google Books"}</td>
      </tr>
      <tr>
        <th>{translate text="Provider"}: </th>
        <td><input type="text" name="bookcover_provider" value="{$config.BookCovers.provider}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Account ID"}: </th>
        <td><input type="text" name="bokcover_id" value="{$config.BookCovers.id}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="Book Reviews Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="Book Reviews are Optional. You can select from Syndetics or Amazon"}</td>
      </tr>
      <tr>
        <th>{translate text="Provider"}: </th>
        <td><input type="text" name="bookreview_provider" value="{$config.BookReviews.provider}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="Account ID"}: </th>
        <td><input type="text" name="bookreview_id" value="{$config.BookReviews.id}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="LDAP Server Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="LDAP is optional.  With this disabled authentication will take place in the local database"}</td>
      </tr>
      <tr>
        <th>{translate text="LDAP Server"}: </th>
        <td><input type="text" name="ldaphost" value="{$config.LDAP.host}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="LDAP Port"}: </th>
        <td><input type="text" name="ldapport" value="{$config.LDAP.port}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="LDAP Base DN"}: </th>
        <td><input type="text" name="ldapbasedn" value="{$config.LDAP.basedn}" size="50"/></td>
      </tr>
      <tr>
        <th>{translate text="LDAP UID"}: </th>
        <td><input type="text" name="ldapuid" value="{$config.LDAP.uid}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="COinS Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="This section can be changed to create a COinS identifier"}</td>
      </tr>
      <tr>
        <th>{translate text="Identifier"}: </th>
        <td><input type="text" name="coinsID" value="{$config.COinS.identifier}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="OAI Server Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="This section can be changed to create an OAI identifier"}</td>
      </tr>
      <tr>
        <th>{translate text="Identifier"}: </th>
        <td><input type="text" name="oaiID" value="{$config.OAI.identifier}" size="50"/></td>
      </tr>
      <tr>
        <th colspan="2">{translate text="OpenURL Link Resolver Settings"}</th>
      </tr>
      <tr>
        <td colspan="2" class="notes">{translate text="OpenURL Link Resolver is Optional"}.</td>
      </tr>
      <tr>
        <th>{translate text="Link Resolver URL"}: </th>
        <td><input type="text" name="openurl" value="{$config.OpenURL.url}" size="50"/></td>
      </tr>
    </table>
    <input type="submit" name="submit" value="{translate text="Save"}"/>
  </form>
</div>

<div class="clear"></div>
