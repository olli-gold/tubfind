{if is_array($recordFormat)}
    {foreach from=$recordFormat item=displayFormat name=loop}
        {if $displayFormat=="Electronic" || $displayFormat=="eBook" || $displayFormat=="Elektronische Aufsätze"}
            {assign var="interlibraryLoan" value="0"}
        {/if}
        {if $displayFormat=="Journal" || $displayFormat=="Serial"}
            {assign var="showAssociated" value="1"}
        {/if}
    {/foreach}
{else}
    {if $recordFormat=="Electronic" || $recordFormat=="eBook" || $recordFormat=="Elektronische Aufsätze"}
        {assign var="interlibraryLoan" value="0"}
    {/if}
    {if $recordFormat=="Journal" || $recordFormat=="Serial"}
        {assign var="showAssociated" value="1"}
    {/if}
{/if}

{assign var="nothingShown" value="0"}

{if $nlurls}
    {assign var="nothingShown" value="1"}
    <p>
    {*translate text="Available via German National license."*}
    {foreach from=$nlurls key=recordurl item=urldesc}
        <br/>{translate text="NL"}: <a href="{$recordurl}">{$urldesc}</a>
    {/foreach}
    </p>
{else}

{assign var="thisIsAnURL" value="false"}

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
                        <span class="available">{translate text="Available"}</span> | 
                        {if $row.loan_availability == "0"}
                            <strong>{translate text="Only for presence use"}</strong>
                        {/if}
                        {if $location != "Magazin" }
                            {* Take holding from reading room *}
                            {translate text="Please pick up this holding from its position in the reading room"}
                        {else}
                            {* order holdings from closed stack if there is a reservation link *}
                            {if $row.recallhref}
                                <a href="{$row.recallhref}" target="_blank">{translate text="Place a Hold"}</a>
                            {/if}
                        {/if}
                        {* TODO: reserve holding via vufind *}
                        {* <a href="{$url}/Record/{$id|escape:"url"}/Hold">{translate text="Place a Hold"}</a> *}
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
                            {/if}
                            {* TODO: reserve holding via vufind.
                            <a href="{$url}/Record/{$id|escape:"url"}/Hold">{translate text="Recall This"}</a> *}
                        {else}
                            {if $interlibraryLoan=="1"}
                                <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$id}" target="_blank">{translate text="interlibrary loan"}</a></span>
                            {else}
                                {if $isMultipartChildren == 0}
                                    {translate text="Not for loan"}
                                {/if}
                                {if $nothingShown == "0" && $isMultipartChildren == 1}
                                    {assign var="nothingShown" value="1"}
                                    <a href="{$url}/Record/{$id|escape:"url"}/Multipart#tabnav">{translate text='See Tomes'}</a>
                                {/if}
                            {/if}
                        {/if}
                    {elseif $row.availability == -1}
                        {if $interlibraryLoan=="1"}
                            <span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN={$id}" target="_blank">{translate text="interlibrary loan"}</a></span>
                        {else}
                            {if $isMultipartChildren == 0}
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

{if $history}
    <h3>{translate text="Most Recent Received Issues"}</h3>
    <ul>
        {foreach from=$history item=row}
            <li>{$row.issue|escape}</li>
        {/foreach}
    </ul>
{/if}

{if !empty($holdingURLs) && $thisIsAnURL == "false"}
  <h3>{translate text="Internet"}</h3>
  {if !empty($holdingURLs)}
    {foreach from=$holdingURLs item=desc key=hurl name=loop}
      {if $desc != "C"}
          <a href="{if $proxy}{$proxy}/login?url={$hurl|escape:"url"}{else}{$hurl|escape}{/if}">{$desc|escape}</a><br/>
      {/if}
    {/foreach}
  {/if}
{/if}

{/if}

{if $coreArticleHRef}
    {$coreArticleHRef.inref} 
    {if $coreArticleHRef.hrefId}
        <a href="{$url}/Record/{$coreArticleHRef.hrefId|upper}">{$coreArticleHRef.jref}</a>
    {else}
        {$coreArticleHRef.jref}
    {/if}
     {$coreArticleHRef.aref}<br/>
{/if}
{foreach from=$articleVol.docs item=artvol}
    {translate text="This article is printed in volume"}: <a href="{$url}/Record/{$artvol.id}">{$artvol.series2.0}</a><br/>
{/foreach}

{if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}
{/if}

{* Loop for subholdings (important in journal context) *}
{*
{if $gbvsubrecords && ($showAssociated == "1" || $showAssociated == "2")}
    <h3>{translate text="Associated volumes"}</h3>
    <ul>
    {foreach from=$gbvsubrecords item=item}
        {assign var=length value=$item.spelling|@strlen}
        {if $item.id!=$id}
            <li>
                {if $item.record_url}
                    <a href="{$item.record_url}">
                {else}
                    <a href="{$url}/Record/{$item.id}">
                {/if}
                {if $item.contents.0}
                    {$item.contents.0}
                {else}
                    {if $item.title_full}
                        {$item.title_full.0}
                    {else}
                        {$item.spelling|substr:0:$length-17|escape}
                    {/if}
                {/if}
                {if $item.publishDate.0}
                    {$item.publishDate.0}
                {/if}
                </a>
            </li>
        {/if}
    {/foreach}
    </ul>
{/if}
*}