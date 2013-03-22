{if $citationCount < 1}
  {translate text="No citations are available for this record"}.
{else}
  {if $apa}
    <strong>{translate text="APA Citation"}</strong>
    <p class="citationText">
        {include file=$apa}
    </p>
  {/if}
  {if $mla}
    <strong>{translate text="MLA Citation"}</strong>
    <p class="citationText">
      {include file=$mla}
    </p>
  {/if}
  <div class="note">{translate text="Warning: These citations may not always be 100% accurate"}.</div>
{/if}