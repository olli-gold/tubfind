<div id="bd">
  <div id="yui-main" class="content">
    <div class="contentbox" style="margin-right: 15px;">

      <div class="yui-g">
        <div class="yui-g first">
          <div class="yui-u first">
            <div class="browseNav" style="margin: 0px;">
            {include file="Browse/top_list.tpl" currentAction="Topic"}
            </div>
          </div>
          <div class="yui-u" id="browse2">
            <div class="browseNav" style="margin: 0px;">
            <ul class="browse" id="list2">
              <li><a href="{$url}/Browse/Topic" onClick="highlightBrowseLink(this); LoadAlphabet('topic_facet', 'list3', 'topic_facet'); return false">{translate text="By Alphabetical"}</a></li>
              {if $genreEnabled}<li><a href="{$url}/Browse/Topic" onClick="highlightBrowseLink(this); LoadSubject('genre_facet', 'list3', 'topic_facet'); return false">{translate text="By Genre"}</a></li>{/if}
              {if $regionEnabled}<li><a href="{$url}/Browse/Topic" onClick="highlightBrowseLink(this); LoadSubject('geographic_facet', 'list3', 'topic_facet'); return false">{translate text="By Region"}</a></li>{/if}
              {if $eraEnabled}<li><a href="{$url}/Browse/Topic" onClick="highlightBrowseLink(this); LoadSubject('era_facet', 'list3', 'topic_facet'); return false">{translate text="By Era"}</a></li>{/if}
            </ul>
            </div>
          </div>
        </div>
        <div class="yui-g">
          <div class="yui-u first" id="browse3">
            <div class="browseNav" style="margin: 0px;">
            <ul class="browse" id="list3">
            </ul>
            </div>
          </div>
          <div class="yui-u" id="browse4">
            <div class="browseNav" style="margin: 0px;">
            <ul class="browse" id="list4">
            </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>