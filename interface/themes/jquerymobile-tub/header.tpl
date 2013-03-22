<div data-role="header" data-theme="b">
  <h1>{$pageTitle|trim:':/'}</h1>
    
  {* display the search button everywhere except /Search/Home *}
  {if !($module == 'Search' && $pageTemplate == 'home.tpl') }
    <a rel="external" href="{$path}/Search/Home" data-icon="search"  class="ui-btn-right">
    {translate text="Search"}
    </a>
  {/if}
  
  {* if a module has header-navbar.tpl, then use it *}
  {assign var=header_navbar value="$module/header-navbar.tpl"|template_full_path}
  {if !empty($header_navbar)}
    {include file=$header_navbar}
  {/if}
</div>
