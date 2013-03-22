<div id="record{$summId|escape}" class="yui-ge">
  <div class="yui-u first">
  {if $summISBN}
    <img src="{$path}/bookcover.php?src=GBVCentral&isn={$summISBN|escape:"url"}&amp;size=small" class="alignleft" alt="{translate text='Cover Image'}"/>
  {else}
    <img src="{$path}/bookcover.php?src=GBVCentral" class="alignleft" alt="{translate text='No Cover Image'}"/>
  {/if}
    <div class="resultitem">
      <div class="resultItemLine1">
      <a href="{$url}/Record/{$summId|escape:"url"}" class="title">{if !$summTitle}{translate text='Title not available'}{else}{$summTitle|truncate:180:"..."|highlight:$lookfor}{/if}</a>
      </div>

      <div class="resultItemLine2">
      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Author/Home?author={$summAuthor|escape:"url"}">{$summAuthor|highlight:$lookfor}</a>
      {/if}

      {if $summDate}{translate text='Published'} {$summDate.0|escape}{/if}
      {if $summArticleRef}
          <br/>
          {$summArticleRef}
      {/if}
      </div>

      <div class="span-6 last">
        {if !$summArticleRef}
        {if $summAjaxStatus}
            <strong>{translate text='Call Number'}:</strong> <span class="ajax_availability hide" id="callnumber{$summId|escape}">{translate text='Loading'}...</span><br/>
            <strong>{translate text='Located'}:</strong> <span class="ajax_availability hide" id="location{$summId|escape}">{translate text='Loading'}...</span>
        {elseif !empty($summCallNo)}
            <strong>{translate text='Call Number'}:</strong> {$summCallNo|escape}
        {/if}
        {/if}

        {if $summOpenUrl || !empty($summURLs)}
            {if $summOpenUrl}
              <br>
              {include file="Search/openurl.tpl" openUrl=$summOpenUrl}
            {/if}
            {foreach from=$summURLs key=recordurl item=urldesc}
                <br><a href="{$recordurl|escape}" class="fulltext" target="new">{$urldesc|escape}</a>
            {/foreach}
        {/if}
        {assign  var="showAvail" value="false"}
        {*foreach from=$summFormats item=format*}
            {*if $format!="Serial" && $format!="Journal" && $format!="Electronic" && $format!="eBook" && $format!="Aufsätze"*}
                {*assign var="showAvail" value="true"*}
            {*/if*}
        {*/foreach*}
        {if $showAvail=="true"}
            <div class="status" id="status{$summId|escape}">
                <span class="unknown" style="font-size: 8pt;">{translate text='Loading'}...</span>
            </div>
        {/if}
      </div>
      {foreach from=$summFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
    </div>
  </div>

  <div class="yui-u">
    <div id="saveLink{$summId|escape}">
      <a href="{$url}/Record/{$summId|escape:"url"}/Save" onClick="getLightbox('Record', 'Save', '{$summId|escape}', '', '{translate text='Add to favorites'}', 'Record', 'Save', '{$summId|escape}'); return false;" class="fav tool">{translate text='Add to favorites'}</a>
      <ul id="lists{$summId|escape}"></ul>
      <script language="JavaScript" type="text/javascript">
        getSaveStatuses('{$summId|escape:"javascript"}');
      </script>
    </div>
  </div>
</div>

<div style="clear:left;">&nbsp;</div>

{if $summOpenUrl}<span class="Z3988" title="{$summOpenUrl|escape}"></span>{/if}

<script type="text/javascript">
  getStatuses('{$summId|escape:"javascript"}');
</script>
