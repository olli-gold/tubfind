{if !empty($holdingURLs) || $holdingsOpenURL}
  <h3>{translate text="Internet"}</h3>
  {if $holdingsOpenURL}
    {include file="Search/openurl.tpl" openUrl=$holdingsOpenURL}
  {* else *}
  {* if !empty($holdingURLs) *}
    {* foreach from=$holdingURLs item=desc key=url name=loop *}
      <!--<a href="{if $proxy}{$proxy}/login?url={$url|escape:"url"}{else}{$url|escape}{/if}">{$desc|escape}</a><br/>-->
    {* /foreach *}
  {* /if *}
  {/if}
{/if}


{foreach from=$gbvholdings item=holding key=location}
<h3>{translate text=$location}</h3>
<table cellpadding="2" cellspacing="0" border="0" class="citation" summary="{translate text='Holdings details from'} {translate text=$location}">
  {if $holding.0.callnumber}
    <tr>
        <th>{translate text="Call Number"}: </th>
            <td>{$holding.0.callnumber|escape}</td>
              </tr>
                {/if}
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
                                                        {if $holding.0.notes}
                                                          <tr>
                                                              <th>{translate text="Notes"}: </th>
                                                                  <td>
                                                                        {foreach from=$holding.0.notes item=data}
                                                                              {$data|escape}<br>
                                                                                    {/foreach}
                                                                                        </td>
                                                                                          </tr>
                                                                                            {/if}
                                                                                              {foreach from=$holding item=row}
                                                                                                  {if $row.callnumber != $holding.0.callnumber}
                                                                                                        <tr>
                                                                                                                <th>{translate text="Call Number"}: </th>
                                                                                                                        <td>{$row.callnumber|escape}</td>
                                                                                                                              </tr>
                                                                                                                                  {/if}
                                                                                                                                      {if $row.barcode == "1"}
                                                                                                                                              <tr><th>Copy {$row.number}</th>
                                                                                                                                                  {else}
                                                                                                                                                          <tr><th>
                                                                                                                                                                  {foreach from=$volumes item=volume key=signature}
                                                                                                                                                                              {if $signature == $row.callnumber}
                                                                                                                                                                                              {$volume}
                                                                                                                                                                                                          {/if}
                                                                                                                                                                                                                  {/foreach}
                                                                                                                                                                                                                          {$row.barcode}
                                                                                                                                                                                                                                  </th>
                                                                                                                                                                                                                                      {/if}
    <td>
          {if $row.reserve == "Y"}
                {translate text="On Reserve - Ask at Circulation Desk"}
                      {else}
                              {if $location != "Internet"}
                                      {if $row.callnumber != "Einzelsign."}
                                              {if $row.availability}
                                                          <span class="available">{translate text="Available"}</span> | 
                                                                      {if $location != "Magazin" }
                                                                                      {* Take holding from reading room *}
                                                                                                      {translate text="Please pick up this holding from its position in the reading room"}
                                                                                                                  {else}
                                                                                                                                  {* order holdings from closed stack *}
                                                                                                                                                  <a href="{$row.recallhref}" target="_blank">{translate text="Place a Hold"}</a>
                                                                                                                                                              {/if}
                                                                                                                                                                          {* TODO: reserve holding via vufind *}
                                                                                                                                                                                      {* <a href="{$url}/Record/{$id|escape:"url"}/Hold">{translate text="Place a Hold"}</a> *}
                                                                                                                                                                                              {else}
                                                                                                                                                                                                          <span class="checkedout">{$row.status|escape}{$row.duedate}</span>
                                                                                                                                                                                                                      {if $row.duedate}
                                                                                                                                                                                                                                      {translate text="Due"}: {translate text=$row.duedate|escape} |
                                                                                                                                                                                                                                                      <a href="{$row.recallhref}" target="_blank">{translate text="Recall This"}</a>
                                                                                                                                                                                                                                                                      {* TODO: reserve holding via vufind.
                                                                                                                                                                                                                                                                                      <a href="{$url}/Record/{$id|escape:"url"}/Hold">{translate text="Recall This"}</a> *}
                                                                                                                                                                                                                                                                                                  {else}
                                                                                                                                                                                                                                                                                                                  {translate text="Not for loan"}
                                                                                                                                                                                                                                                                                                                              {/if}
                                                                                                                                                                                                                                                                                                                                      {/if}
                                                                                                                                                                                                                                                                                                                                              {/if}
                                                                                                                                                                                                                                                                                                                                                      {/if}
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
                                                                                                                                                                                                                                                                                                                                                                          
                                                                                                                                                                                                                                                                                                                                                                          {* Loop for subholdings (important in journal context) *}
{if $gbvsubrecords}
    <h3>{translate text="Associated volumes"}</h3>
        {foreach from=$gbvsubrecords item=item}
                {assign var=length value=$item.spelling|@strlen}
                        {if $item.id!=$id}
                                    <h3>
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
                                                                                                                                                                                                                                                                                                        </a></h3>
                                                                                                                                                                                                                                                                                                                {/if}
                                                                                                                                                                                                                                                                                                                    {/foreach}
                                                                                                                                                                                                                                                                                                                    {/if}
