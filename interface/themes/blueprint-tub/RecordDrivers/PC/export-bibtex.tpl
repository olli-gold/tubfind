{assign var=bracket value='{'}
{assign var=isphdThesis value=false}
{assign var=format value=$pcfields.format}
{if is_array($format)}
{if in_array('dissertation', $format)}
{assign var=isphdThesis value=true}
@phdthesis{$bracket}
{else}
{if in_array('Article', $format)}
@article{$bracket}
{else}
{if in_array('book_chapter', $recordFormat)}
@inbook{$bracket}
{else}
{if in_array('Book', $recordFormat)}
@book{$bracket}
{else}
@misc{$bracket}
{/if}
{/if}
{/if}
{/if}
{else}
{if $format == 'Article' || $format == 'Book'}
@{$recordFormat}{$bracket}
{else}
{if $format == 'dissertation'}
{assign var=isphdThesis value=true}
@phdthesis{$bracket}
{else}
{if $format == 'book_chapter'}
@inbook{$bracket}
{else}
@misc{$bracket}
{/if}
{/if}
{/if}
{/if}
{if $pcfields.id}
{$pcfields.id},
{assign var=id value=$pcfields.id}
{/if}
title = {$bracket}{$pcfields.title}},
{assign var=marcField440 value=$pcfields.series}
{* Display subject section if at least one subject exists. *}
{if $marcField440}
{foreach from=$marcField440 item=field name=loop}
series = {$bracket}{$field}},
{/foreach}
{/if}
{assign var=authors value=$pcfields.author}
{if $authors}
{foreach from=$authors item=field name=loop}
author = {$bracket}{$field}},
{/foreach}
{/if}
{assign var=publisher value=$pcfields.publisher}
{if $pcfields.publisher}
{foreach from=$pcfields.publisher item=field name=loop}
{assign var=pub value=$field}
{assign var=pubArr value=':'|explode:$pub}
{if $pubArr.1}
address = {$bracket}{$pubArr.0}},
publisher = {$bracket}{$pubArr.1}},
{else}
{if $pubArr.0}
publisher = {$bracket}{$pubArr.0}},
{/if}
{/if}
{/foreach}
{/if}
{if $pcfields.publishDate}
year = {$bracket}{$pcfields.publishDate.0}},
{/if}
{if $pcfields.edition}
{foreach from=$pcfields.edition item=field name=loop}
edition = {$bracket}{$field}},
{/foreach}
{/if}
{if $pcfields.isbn}
{foreach from=$pcfields.isbn item=field name=loop}
isbn = {$bracket}{$field}},
{/foreach}
{/if}
{if $pcfields.issn}
{foreach from=$pcfields.issn item=field name=loop}
issn = {$bracket}{$field}},
{/foreach}
{/if}
{if $isphdThesis == true && $pcfields.source}
school = {$bracket}{$pcfields.source.0}},
{/if}
{*
{assign var=marcField value=$marc->getField('300')}
{if $marcField}
pages = {$bracket}{$marcField|getvalue:'a'}},
{/if}
{assign var=marcField value=$marc->getField('500')}
{if $marcField}
note = {$bracket}{$marcField|getvalue:'a'}},
{/if}
*}
{if $pcfields.url}
url = {$pcfields.url.0},
{/if}
{if $pcfields.jtitle}
journal = {$bracket}{if is_array($pcfields.jtitle)}{$pcfields.jtitle.0}{else}{$pcfields.jtitle}{/if}},
{/if}
{if $pcfields.jvol}
volume = {$bracket}{if is_array($pcfields.jvol)}{$pcfields.jvol.0}{else}{$pcfields.jvol}{/if}},
{/if}
{if $pcfields.jissue}
number = {$bracket}{if is_array($pcfields.jissue)}{$pcfields.jissue.0}{else}{$pcfields.jissue}{/if}},
{/if}
{if $pcfields.jspage}
pages = {$bracket}{if is_array($pcfields.jspage)}{$pcfields.jspage.0}{else}{$pcfields.jspage}{/if}{if $pcfields.jepage}-{if is_array($pcfields.jepage)}{$pcfields.jepage.0}{else}{$pcfields.jepage}{/if}{/if}},
{/if}
crossref = {$url}/Record/{$id|escape:"url"}
}