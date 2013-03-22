{if $tocTemplate}
  <strong>{translate text='Table of Contents'}: </strong>
  {include file=$tocTemplate}
{else}
  {translate text="Table of Contents unavailable"}.
{/if}
