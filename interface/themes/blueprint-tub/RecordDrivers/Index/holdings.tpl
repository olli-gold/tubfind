{foreach from=$holdings item=holding key=location}
<h3>{translate text=$location}</h3>
<table cellpadding="2" cellspacing="0" border="0" class="citation" summary="{translate text='Holdings details from'} {translate text=$location}">
  {if $holding.0.summary}
  <tr>
    <th>{translate text="Volume Holdings"}: </th>
    <td>
      {foreach from=$holding.0.summary item=summary}
          {$summary|escape}<br>
      {/foreach}
    </td>
  </tr>
  {/if}
  {if $holding.0.marc_notes}
  <tr>
    <th>{translate text="Notes"}: </th>
    <td>
      {foreach from=$holding.0.marc_notes item=data}
      {$data|escape}<br>
      {/foreach}
    </td>
  </tr>
  {/if}

  {assign  var="showLoc" value="true"}
{*
  {if is_array($recordFormat)}
    {foreach from=$recordFormat item=displayFormat name=loop}
        {if $displayFormat!="Serial" && $displayFormat!="Electronic" && $displayFormat!="eBook"}
          {assign var="showLoc" value="true"}
        {/if}
    {/foreach}
  {else}
    {if $recordFormat!="Serial" && $recordFormat!="Electronic" && $recordFormat!="eBook"}
        {assign var="showLoc" value="true"}
    {/if}
  {/if}
*}

  {if $showLoc == "true"}
  {foreach from=$holding item=row}
    {assign var="thisIsAnURL" value="false"}
    {if $row.callnumber != $lastCallnumber}
        {assign var="lastCallnumber" value=$row.callnumber}
        <tr>
            <th>{translate text="Call Number"}: </th>
            <td>{$row.callnumber|escape}</td>
        </tr>
    {/if}
    {if $row.barcode == "1"}
        <tr><th>Copy {$row.number}</th>
    {else}
        <tr>
        {if $row.barcode != ""}
            <th>
                {assign var="numberShown" value="false"}
                {foreach from=$volumes item=volume key=signature}
                    {if $signature == $row.callnumber || $signature == $row.barcode}
                        {$volume}
                        {assign var="numberShown" value="true"}
                    {/if}
                {/foreach}
                {if $location == "Internet" || substr($row.barcode, 0, 5) == "http:"}
                    <a href="{$row.barcode}">{$row.barcode}</a>
                    {assign var="thisIsAnURL" value="true"}
                {else}
                    {if $numberShown != true}
                        {$row.barcode}
                    {/if}
                {/if}
            </th>
        {else}
            <th></th>
        {/if}
    {/if}
            <td>
                {if $row.reserve == "Y"}
                    {translate text="On Reserve - Ask at Circulation Desk"}
                {else}
                    {if $location != "Internet"}
                        {if $row.callnumber != "Einzelsign."}
                            {if $row.availability > 0 || $thisIsAnURL == "true"}
                                <span class="available">{translate text="Available"}</span> |
                                {if $thisIsAnURL == "true"}
                                    {translate text="Available online"}
                                {else}
                                    {if $row.loan_availability == "0"}
                                        <strong>{translate text="Only for presence use"}</strong>
                                    {/if}
                                    {if $location != "Magazin"}
                                        {* Take holding from reading room *}
                                        {translate text="Please pick up this holding from its position in the reading room"}
                                    {else}
                                        {* order holdings from closed stack *}
                                        {if $row.recallhref}
                                            <a href="{$row.recallhref}" target="_blank">{translate text="Place a Hold"}</a>
                                        {/if}
                                    {/if}
                                {/if}
                                {* TODO: reserve holding via vufind *}
                                {* <a href="{$url}/Record/{$id|escape:"url"}/Hold">{translate text="Place a Hold"}</a> *}
                            {else}
                                {if $row.availability == 0}
                                    <span class="checkedout">{translate text=$row.status|escape}{$row.duedate}</span>
                                    {if $row.duedate}
                                        {translate text="Due"}: {translate text=$row.duedate|escape}
                                        {if $row.recallhref}
                                            | <a href="{$row.recallhref}" target="_blank">{translate text="Recall This"}</a>
                                        {/if}
                                        {* TODO: reserve holding via vufind.
                                        <a href="{$url}/Record/{$id|escape:"url"}/Hold">{translate text="Recall This"}</a> *}
                                    {else}
                                        {translate text="Not for loan"}
                                    {/if}
                                {/if}
                            {/if}
                        {/if}
                    {/if}
                    {if $row.notes}
                        <br/>{$row.notes.remark.$language}
                    {/if}
                {/if}
            </td>
        </tr>
  {/foreach}
  {/if}
</table>
{/foreach}
{if (!empty($holdingURLs) || $holdingsOpenURL) && ($location != "Internet" || empty($row.barcode))}
  <h3>{translate text="Internet"}</h3>
  {if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}
  {/if}
  {if !empty($holdingURLs)}
    {foreach from=$holdingURLs item=desc key=currentUrl name=loop}
      <a href="{if $proxy}{$proxy}/login?url={$currentUrl|escape:"url"}{else}{$currentUrl|escape}{/if}">{$desc|escape}</a><br/>
    {/foreach}
  {/if}
{/if}


{if $history}
<h3>{translate text="Most Recent Received Issues"}</h3>
<ul>
  {foreach from=$history item=row}
  <li>{$row.issue|escape}</li>
  {/foreach}
</ul>
{/if}

{if $showAssociated == "1"}
    {include file="RecordDrivers/Index/associated_records.tpl" gbvsubrecords=$gbvsubrecords}
{/if}