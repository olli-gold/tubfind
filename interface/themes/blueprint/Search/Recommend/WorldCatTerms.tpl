{if !empty($WorldCatTerms)}
<div class="authorbox">
  <h3>{translate text='Subject Recommendations'}</h3>
    {foreach from=$WorldCatTerms item=section name=sectionLoop key=type}
      <div class="span-5{if $smarty.foreach.sectionLoop.last} last{/if}">
        <dl>
          <dt>{translate text="wcterms_`$type`"}</dt>
          {foreach from=$section item=subj name=narrowLoop}
            {if $smarty.foreach.narrowLoop.iteration == 4}
              <dd id="moreWCTerms{$type}"><a href="#" onclick="moreFacets('WCTerms{$type}'); return false;">{translate text='more'} ...</a></dd>
              </dl>
              <dl class="offscreen" id="narrowGroupHidden_WCTerms{$type}">
            {/if}
            <dd>&bull; <a href="{$url}/Search/Results?lookfor=%22{$subj|escape:"url"}%22&amp;type=Subject">{$subj|escape}</a></dd>
          {/foreach}
          {if $smarty.foreach.narrowLoop.total > 3}<dd><a href="#" onclick="lessFacets('WCTerms{$type}'); return false;">{translate text='less'} ...</a></dd>{/if}
        </dl>
      </div>
    {/foreach}
    <div class="clear"></div>
</div>
{/if}
