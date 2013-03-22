<ul class="recordSet">
  {foreach from=$recordSet item=record name="recordLoop"}
    <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
      <div class="listentry">
        <div class="resultItemLine1">
          <a href="{$url}/Authority/Record?id={$record.id|escape:"url"}" class="title">{if $record.heading}{$record.heading|escape}{else}{translate text='Heading unavailable.'}{/if}</a>
        </div>

        <div class="resultItemLine2">
        {if $record.see_also}
          {translate text="See also"}:<br/>
          {foreach from=$record.see_also item=current}
            <a href="Search?lookfor=%22{$current|escape:"url"}%22&amp;type=MainHeading">{$current|escape}</a><br/>
          {/foreach}
        {/if}
        </div>

        <div class="resultItemLine3">
        {if $record.use_for}
          {translate text="Use for"}:<br/>
          {foreach from=$record.use_for item=current}
            {$current|escape}<br/>
           {/foreach}
          {/if}
        </div>
      </div>
    </li>
  {/foreach}
</ul>
