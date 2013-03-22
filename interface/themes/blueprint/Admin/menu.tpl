<ul id="list1">
  <li{if $action == "Home"} class="active"{/if}><a href="Home">{translate text="Home"}</a></li>
  <li{if $action == "Statistics"} class="active"{/if}><a href="Statistics">{translate text="Statistics"}</a></li>
  <li{if $action == "Config"} class="active"{/if}><a href="Config">{translate text="Configuration"}</a>
{if $action == "Config"}
<ul>
  <li><a href="Config?file=config.ini">General Configuration</a></li>
  <li><a href="Config?file=searchspecs.yaml">Search Specifications</a></li>
  <li><a href="Config?file=searches.ini">Search Settings</a></li>
  <li><a href="Config?file=facets.ini">Facet Settings</a></li>
  <li><a href="Config?file=stopwords.txt">Stop Words</a></li>
  <li><a href="Config?file=synonyms.txt">Synonyms</a></li>
  <li><a href="Config?file=protwords.txt">Protected Words</a></li>
  <li><a href="Config?file=elevate.xml">Elevated Words</a></li>
</ul>
{/if}
  </li>
  <li{if $action == "Records"} class="active"{/if}><a href="Records">Record Management</a>
{if $action == "Records"}
<ul>
  {* not implemented yet <li><a href="">{translate text="Add Records"}</a></li> *}
  <li><a href="{$url}/Admin/Records?util=deleteSuppressed">{translate text="Delete Suppressed"}</a></li>
  {* not implemented yet <li><a href="">{translate text="Update Authority"}</a></li> *}
</ul>
{/if}
  </li>
  <li{if $action == "Maintenance"} class="active"{/if}><a href="Maintenance">{translate text="System Maintenance"}</a></li>
</ul>
