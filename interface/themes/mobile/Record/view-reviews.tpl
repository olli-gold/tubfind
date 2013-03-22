{foreach from=$reviews item=providerList key=provider}
  {foreach from=$providerList item=review}
    {if $review.Summary}
    <p>
      {if $review.Rating}<img src="{$path}/images/{$review.Rating}.gif" alt="{$review.Rating}/5 Stars">{/if}
      <b>{$review.Summary}</b>{if $review.Date},{/if} {$review.Date|date_format:"%B %e, %Y"}
    </p>
    {/if}
    {if $review.Source}
    <b>{translate text="Review by"} {$review.Source}</b>
    {/if}
    <p class="summary">{$review.Content}
      {if !$review.Content && $review.ReviewURL}<a target="new" href="{$review.ReviewURL}">{translate text="Read the full review online..."}</a>{/if}
    </p>
    {$review.Copyright}
    {if $provider == "amazon" || $provider == "amazoneditorial"}
    <div><a target="new" href="http://amazon.com/dp/{$isbn}">{translate text="Supplied by Amazon"}</a></div>
    {/if}
    <hr/>
  {/foreach}
{foreachelse}
{translate text="No reviews were found for this record"}.
{/foreach}