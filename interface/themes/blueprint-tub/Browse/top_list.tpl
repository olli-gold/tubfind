{js filename="browse.js"}
<ul class="browse" id="list1">
{foreach from=$browseOptions item=currentOption}
  <li {if $currentOption.action == $currentAction} class="active"{/if}>
    <a href="{$url}/Browse/{$currentOption.action}">{translate text=$currentOption.description}</a>
  </li>
{/foreach}
</ul>
