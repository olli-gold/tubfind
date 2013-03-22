<form method="GET" action="{$url}/Search/Results" name="searchForm" class="search">
  <ul class="pageitem">
    <li class="form"><input type="hidden" name="type[]" value="title"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_title"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="subject"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_subject"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="author"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_author"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="publisher"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_publisher"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="series"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_series"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="callnumber"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_callnumber"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="isn"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_isn"}"></li>
    <li class="form"><input type="hidden" name="type[]" value="toc"><input type="text" name="lookfor[]" placeholder="{translate text="adv_search_toc"}"></li>
  </ul>
  <ul class="pageitem">
    <li class="form"><input type="submit" name="submit" value="{translate text="Find"}"></li>
  </ul>
</form>
