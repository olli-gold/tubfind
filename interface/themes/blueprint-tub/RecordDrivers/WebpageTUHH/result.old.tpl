<div id="record{$summId|escape}" class="result recordId">
  <div class="yui-u first"><img src="{$url}/interface/themes/blueprint-tub/images/website.jpg" class="alignleft" alt="Website"/>
    <div class="resultitem">
      <div class="resultItemLine1">
      {foreach from=$summURLs item=recordurl}
      {/foreach}
      {if $summTitleGer}
      <a href="{$recordurl|escape}?docinput[flavour]=1" class="title"><img src="{$url}/interface/themes/blueprint-tub/images/de.gif"  border="0" alt="Deutsch" title="Deutsch"/>{$summTitleGer|truncate:180:"..."|highlight:$lookfor}</a>
      {if $summTitleEng}
      <br/><a href="{$recordurl|escape}?docinput[flavour]=2" class="title"><img src="{$url}/interface/themes/blueprint-tub/images/uk.gif"  border="0" alt="English" title="English"/>{$summTitleEng|truncate:180:"..."|highlight:$lookfor}</a>
      {/if}
      {else}
      <a href="{$recordurl|escape}" class="title">{if !summTitle}{translate text='Title not available'}{else}
      {if is_array($summTitle)}
      {$summTitle.0|truncate:180:"..."|highlight:$lookfor}
      {else}
      {$summTitle|truncate:180:"..."|highlight:$lookfor}
      {/if}
      {/if}</a>
      {/if}
      </div>

      <div>
      {$summContent.0|truncate:180|highlight:$lookfor}
      </div>

      <div class="resultItemLine2">
      {if !empty($summAuthor)}
      {translate text='by'}
      <a href="{$url}/Author/Home?author={$summAuthor|escape:"url"}">{$summAuthor|highlight:$lookfor}</a>
      {/if}

      {if $summDate}{translate text='PublishedDate'} {$summDate.0|escape}{/if}
      </div>

      <div class="resultItemLine3">
      <b>{translate text='Located'}:</b> <span>Website der TUBHH</span><br/>
      <a href="{$recordurl}" target="_blank">{$recordurl}</a>

      </div>
      {foreach from=$summFormats item=format}
        <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
      {/foreach}
    </div>
  </div>

  <div class="yui-u span-4 last">
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
