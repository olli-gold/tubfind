<div class="span-5">
  {include file="Admin/menu.tpl"}
</div>

<div class="span-18 last">
  <h1>{translate text="VuFind Administration"}</h1>

  <h2>{translate text="Bibliographic Index"}</h2>
  <table class="citation">
  <tr>
    <th>{translate text="Record Count"}: </th>
    <td>{$data.biblio.index.numDocs._content}</td>
  </tr>
  <tr>
    <th>{translate text="Optimized"}: </th>
    <td>
      {if $data.biblio.index.optimized._content == "false"}
        <span class="error">{$data.biblio.index.optimized._content}</span>
      {else}
        <span>{$data.biblio.index.optimized._content}</span>
      {/if}
    </td>
  </tr>
  <tr>
    <th>{translate text="Start Time"}: </th>
    <td>{$data.biblio.startTime._content|date_format:"%b %d, %Y %l:%M:%S%p"}</td>
  </tr>
  <tr>
    <th>{translate text="Last Modified"}: </th>
    <td>{$data.biblio.index.lastModified._content|date_format:"%b %d, %Y %l:%M:%S%p"}</td>
  </tr>
  <tr>
    <th>{translate text="Uptime"}: </th>
    <td>{$data.biblio.uptime._content|printms}</td>
  </tr>
  </table>
          
  <h2>{translate text="Authority Index"}</h2>
  <table class="citation">
  <tr>
    <th>{translate text="Record Count"}: </th>
    <td>{$data.authority.index.numDocs._content}</td>
  </tr>
  <tr>
    <th>{translate text="Optimized"}: </th>
    <td>
      {if $data.authority.index.optimized._content == "false"}
        <span class="error">{$data.authority.index.optimized._content}</span>
      {else}
        <span>{$data.authority.index.optimized._content}</span>
      {/if}
    </td>
  </tr>
  <tr>
    <th>{translate text="Start Time"}: </th>
    <td>{$data.authority.startTime._content|date_format:"%b %d, %Y %l:%M:%S%p"}</td>
  </tr>
  <tr>
    <th>{translate text="Last Modified"}: </th>
    <td>{$data.authority.index.lastModified._content|date_format:"%b %d, %Y %l:%M:%S%p"}</td>
  </tr>
  <tr>
    <th>{translate text="Uptime"}: </th>
    <td>{$data.authority.uptime._content|printms}</td>
  </tr>
  </table>

  <h2>{translate text="Usage Statistics Index"}</h2>
  <table class="citation">
  <tr>
    <th>{translate text="Record Count"}: </th>
    <td>{$data.stats.index.numDocs._content}</td>
  </tr>
  <tr>
    <th>{translate text="Optimized"}: </th>
    <td>
      {if $data.stats.index.optimized._content == "false"}
        <span class="error">{$data.stats.index.optimized._content}</span>
      {else}
        <span>{$data.stats.index.optimized._content}</span>
      {/if}
    </td>
  </tr>
  <tr>
    <th>{translate text="Start Time"}: </th>
      <td>{$data.stats.startTime._content|date_format:"%b %d, %Y %l:%M:%S%p"}</td>
    </tr>
    <tr>
      <th>{translate text="Last Modified"}: </th>
      <td>{$data.stats.index.lastModified._content|date_format:"%b %d, %Y %l:%M:%S%p"}</td>
    </tr>
    <tr>
      <th>{translate text="Uptime"}: </th>
      <td>{$data.stats.uptime._content|printms}</td>
    </tr>
    </table>
</div>

<div class="clear"></div>

      