<ul class="commentList" id="commentList{$id|escape}">
{* Pull in comments from a separate file -- this separation allows the same template
   to be used for refreshing this list via AJAX. *}
{include file="Record/view-comments-list.tpl"}
</ul>

<form name="commentRecord" id="commentRecord" action="{$url}/Record/{$id|escape:"url"}/UserComments" method="post">
  <input type="hidden" name="id" value="{$id|escape}"/>
  <textarea id="comment" name="comment" rows="4" cols="50" class="{jquery_validation required='This field is required'}"></textarea>
  <br/><br/>
  <input type="submit" value="{translate text="Add your comment"}"/>
</form>
