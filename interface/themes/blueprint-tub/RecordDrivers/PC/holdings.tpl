{assign var=summId value=$id|escape}
{if !empty($holdingURLs) || $holdingsOpenURL}
  <h3>{translate text="Internet"}</h3>
  {if $doi}<a href="http://dx.doi.org/{$doi}" target="_new">http://dx.doi.org/{$doi}</a>{/if}

  {if !empty($pcURLs) && empty($doi)}
    {foreach from=$pcURLs item=pcurl}
      <a href="{$pcurl|escape}" class="fulltext" target="new">{$pcurl|escape}</a>
    {/foreach}
  {/if}

  <br/>

  {*
  {if !empty($holdingURLs)}
    {foreach from=$holdingURLs item=desc key=url name=loop}
      {if $desc != "C"}
          <a href="{if $proxy}{$proxy}/login?url={$url|escape:"url"}{else}{$url|escape}{/if}">{$desc|escape}</a><br/>
      {/if}
    {/foreach}
  {/if}
  *}
  {if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}
  {/if}
{/if}
{if $sfxmenu && $sfxbutton && empty($pcURLs) && empty($doi)}
  <span class="hidden" id="sfxmenu{$id|escape}"><a href="{$sfxmenu}"><img src="{$sfxbutton}" alt="SFX" /></a></span>
{/if}
{assign var="nothingShown" value="0"}
{foreach from=$gbvholdings item=holding key=location}
    <h3>
    {if $holding.0.locationhref}
        {if $holding.0.locationhref == "\n"}
            {assign var="nothingShown" value="1"}
            <a href="{$url}/Record/{$id|escape:"url"}/Multipart#tabnav">
        {else}
            <a href="{$holding.0.locationhref}">
        {/if}
            {translate text=$location}
        </a>
    {else}
        {if $location == "s. zugehörige Publikationen"}
            {assign var="nothingShown" value="1"}
            <a href="{$url}/Record/{$id|escape:"url"}/Multipart#tabnav">
        {/if}
        {translate text=$location}
        {if $location == "s. zugehörige Publikationen"}
            </a>
        {/if}
    {/if}
    </h3>
    <table cellpadding="2" cellspacing="0" border="0" class="citation" summary="{translate text='Holdings details from'} {translate text=$location}">
      {*
      {if $holding.0.summary}
        <tr>
            <th>{translate text="Notes"}</th>
            <td>
                {foreach from=$holding.0.summary item=summary}
                    {$summary|escape}<br>
                {/foreach}
            </td>
        </tr>
      {/if}
      *}
      {*
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
      *}
      {if $holding.0.limit}
        <tr>
            <th>{translate text="Limit"}: </th>
            <td>
                {foreach from=$holding.0.limit item=data}
                    {$data}<br>
                {/foreach}
            </td>
        </tr>
      {/if}
      {if $holding.0.callnumber && $holding.0.callnumber != "-"}
        <tr>
          <th>{translate text="Call Number"}</th>
          <td class="callnumberResult">{$holding.0.callnumber|escape}</td>
        </tr>
      {/if}
      {foreach from=$holding item=row}
        {assign var="remark" value="0"}
        {if $row.callnumber != $holding.0.callnumber}
            <tr>
                <th>{translate text="Call Number"}</th>
                <td class="callnumberResult">{$row.callnumber|escape}</td>
            </tr>
        {/if}
        {if $row.marc_notes.0}
            <tr>
                <th>{translate text="Volume Holdings"}</th>
                <td>{$row.marc_notes.0}</td>
            </tr>
        {/if}
        {if $row.summary}
            <tr>
                <th>{translate text="Notes"}</th>
                <td>
                {foreach from=$row.summary item=summary}
                    {$summary|escape}<br>
                {/foreach}
                </td>
            </tr>
        {/if}
        {if $row.barcode != "-1"}
        {if $row.barcode == "1" && count($volumes) == 0}
            <tr>
                <th>{translate text="Copy"} {$row.number}</th>
        {elseif $row.availability != -1}
            <tr>
                <th>
                    {assign var="remarkShown" value="false"}
                    {foreach from=$volumes item=volume key=signature}
                        {if ($signature == $row.callnumber || $signature == $row.barcode) && $volume.volume != "0" && $row.barcode != "1"}
                            {if $volume.remark}
                                {translate text="Volume Holdings"}
                                </th>
                                <td>
                                {$volume.remark}
                                </td>
                                </tr>
                                <tr>
                                <th>
                                {*assign var="remarkShown" value="true"*}
                            {/if}
                            {$volume.volume}
                            {assign var="numberShown" value="true"}
                        {/if}
                    {/foreach}
                    {if ($location == "Internet" || substr($row.barcode, 0, 5) == "http:") && $row.barcode != "1"}
                        <a href="{$row.barcode}">{$row.barcode}</a>
                        {assign var="thisIsAnURL" value="true"}
                    {else}
                    {*
                        {if $numberShown != true && $row.barcode != "1"}
                            {translate text=$row.barcode}
                        {/if}
                    *}
                        {translate text="Copy"} {$row.number}
                    {/if}
                </th>
        {/if}
        {*<tr>*}
                <td>
        {if $row.reserve == "Y"}
            {translate text="On Reserve - Ask at Circulation Desk"}
        {else}
            {if $location != "Internet"}
                {if $row.callnumber != "Einzelsign."}
                    {if $row.availability > 0}
                        <span class="available">{translate text="Available"}</span> |.
                        {if $row.loan_availability == "0"}
                            <strong>{translate text="Only for presence use"}</strong>
                        {/if}
                        {if !$row.recallhref}
                        {* if $location != "Magazin" *}
                            {* Take holding from reading room *}
                            {translate text="Please pick up this holding from its position in the reading room"}
                        {else}
                            {* order holdings from closed stack if there is a reservation link *}
                            {*if $row.recallhref*}
                                <a href="{$row.recallhref}" target="_blank">{translate text="Place a Hold"}</a>
                                {* reserve holding via vufind *}
                                <form method="POST" action="{$url}/Record/{$id|escape:"url"}/Hold">
                                    <input type="hidden" name="hashKey" value="2c69dc8fa7eded228820509bd80a3bb2" />
                                    {if $row.barcode != "1"}
                                        <input type="hidden" name="item_id" value="http://uri.gbv.de/document/opac-de-830:bar:830${$row.barcode|replace:'-':''}" />
                                    {else}
                                        <input type="hidden" name="item_id" value="http://uri.gbv.de/document/opac-de-830:bar:830${$row.callnumber|replace:'-':''}" />
                                    {/if}
                                    <input type="submit" name="placeHold" value="{translate text="Place a VuFind-Hold"}" />
                                </form>
                            {*/if*}
                        {/if}
                    {elseif $row.availability == 0}
                        <span class="checkedout">{translate text=$row.status|escape}</span>
                        {if $row.queue}
                            {translate text="Reservations"}: {$row.queue} |
                        {/if}
                        {if $row.duedate}
                            {if $row.duedate != "unknown"}
                                {translate text="Due"}: {translate text=$row.duedate|escape} |
                            {/if}
                            {if $row.recallhref}
                                <a href="{$row.recallhref}" target="_blank">{translate text="Recall This"}</a>
                                {* reserve holding via vufind *}
                                <form method="POST" action="{$url}/Record/{$id|escape:"url"}/Hold">
                                    <input type="hidden" name="hashKey" value="2c69dc8fa7eded228820509bd80a3bb2" />
                                    {if $row.barcode != "1"}
                                        <input type="hidden" name="item_id" value="http://uri.gbv.de/document/opac-de-830:bar:830${$row.barcode|replace:'-':''}" />
                                    {else}
                                        <input type="hidden" name="item_id" value="http://uri.gbv.de/document/opac-de-830:bar:830${$row.callnumber|replace:'-':''}" />
                                    {/if}
                                    <input type="submit" name="placeHold" value="{translate text="Recall this via VuFind"}" />
                                </form>
                            {/if}
                        {else}
                            {if $interlibraryLoan=="1"}
                                <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$id}" target="_blank">{translate text="interlibrary loan"}</a></span>
                                {if $showAcqProp=="1"}
                                    <span> | <a href="{translate text="Erwerbungsvorschlag_Url"}{$holdingsOpenURL|escape}&gvk_ppn={$id}" target="_blank">{translate text="Erwerbungsvorschlag"}</a></span>
                                {/if}
                            {else}
                                {if $isMultipartChildren == 0 && $showAvail == 1 && $showArticleAvail == 1}
                                    {translate text="Not for loan"}
                                {/if}
                                {if $nothingShown == "0" && $isMultipartChildren == 1}
                                    {assign var="nothingShown" value="1"}
                                    <a href="{$url}/Record/{$gbvppn|escape:"url"}/Multipart?shard[]=GBV Central&shard[]=wwwtub&shard[]=tubdok">{translate text='See Tomes'}</a>
                                {/if}
                            {/if}
                        {/if}
                    {elseif $row.availability == -1}
                        {if $interlibraryLoan=="1"}
                            <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$id}" target="_blank">{translate text="interlibrary loan"}</a></span>
                            {if $showAcqProp=="1"}
                                <span> | <a href="{translate text="Erwerbungsvorschlag_Url"}{$holdingsOpenURL|escape}&gvk_ppn={$id}" target="_blank">{translate text="Erwerbungsvorschlag"}</a></span>
                            {/if}
                        {else}
                            {if $isMultipartChildren == 0 && $showAvail == 1 && $showArticleAvail == 1}
                                {translate text="Not for loan"}
                            {/if}
                            {if $nothingShown == "0" && $isMultipartChildren == 1}
                                {assign var="nothingShown" value="1"}
                                <a href="{$url}/Record/{$id|escape:"url"}/Multipart#tabnav">{translate text='See Tomes'}</a>
                            {/if}
                        {/if}
                    {/if}
                {/if}
            {else}
                <a href="{$row.locationhref}">
                {if $linkNames[$row.locationhref]}
                    {$linkNames[$row.locationhref]}: {$row.locationhref|truncate:60:"..."|escape}
                {else}
                    {$row.locationhref}
                {/if}
                </a>
                {if (substr($row.locationhref, 0, 5) == "http:")}
                    {assign var="thisIsAnURL" value="true"}
                {/if}
            {/if}
            {*if $row.notes && !$remarkShown*}
            {if $row.notes}
                <br/>{$row.notes.remark.$language}
            {/if}
        {/if}
        {/if}
        {if $nothingShown == "0" && $isMultipartChildren == 1}
            {assign var="nothingShown" value="1"}
            <a href="{$url}/Record/{$id|escape:"url"}/Multipart#tabnav">{translate text='See Tomes'}</a>
        {/if}
                </td>
            </tr>
    {/foreach}
</table>
{/foreach}
