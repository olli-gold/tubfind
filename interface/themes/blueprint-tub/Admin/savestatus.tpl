{if $saved}
  <div class="warning">
    {if $bytesWritten}
      {translate text="File saved successfully"} -- {$bytesWritten} {translate text="bytes written"}.
    {else}
      {translate text="Problem saving file"} -- {translate text="check write permissions on server"}.
    {/if}
  </div>
{/if}
