<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="Record Management"}</h1>

  <h2>{translate text="Single Record Operations"}</h2>
  {if $status}<div class="warning">{$status}</div>{/if}
  <form action="{$url}/Admin/Records" method="get" name="recordEdit">
    <input type="hidden" name="util" value=""/>
    <label class="displayBlock">{translate text="Record Id"}</label>
    <input type="text" name="id" size="50"/>
    <input class="button" type="submit" name="submit" value="View" onclick="document.forms['recordEdit'].elements['util'].value='viewRecord';"/>
    <input class="button" type="submit" name="submit" value="Delete" onclick="if (!confirm('Are you sure?')) return false; else document.forms['recordEdit'].elements['util'].value='deleteRecord';"/>
  </form>
  
  <h2>{translate text="Utilities"}</h2>
  <dl>
    <dt><a href="{$url}/Admin/Records?util=deleteSuppressed">{translate text="Delete Suppressed Records"}</a></dt>
    <dd>{translate text="This process will delete any suppressed records from the VuFind Index"}.</dd>

    {* not implemented yet:
    <dt>{translate text="Process Authority Records"}</dt>
    <dd>{translate text="This process will update all records with the authority records to ensure that the authority data is included in the search index"}</dd>
    *}
  </dl>
</div>

<div class="clear"></div>
