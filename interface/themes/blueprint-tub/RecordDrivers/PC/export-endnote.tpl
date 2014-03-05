{assign var=isphdThesis value=false}
{assign var=$recordFormat value=$pcfields.format}
{if is_array($recordFormat)}
{if in_array('dissertation', $recordFormat)}
{assign var=isphdThesis value=true}
{/if}
{else}
{if $recordFormat == 'dissertation'}
{assign var=isphdThesis value=true}
{/if}
{/if}
{assign var=yearIsSet value=false}
{foreach from=$recordFormat item=format}
%0 {$format}
{/foreach}
{assign var=authors value=$pcfields.author}
{if $authors}
{foreach from=$authors item=field name=loop}
%A {$field}
{/foreach}
{/if}
{assign var=publisher value=$pcfields.publisher}
{if $pcfields.publisher}
{foreach from=$pcfields.publisher item=field name=loop}
{assign var=pub value=$field}
{assign var=pubArr value=':'|explode:$pub}
{if $pubArr.1}
%C {$pubArr.0}
%I {$pubArr.1}
{else}
{if $pubArr.0}
%I {$pubArr.0}
{/if}
{/if}
{/foreach}
{/if}
{if $pcfields.publishDate}
%D {$pcfields.publishDate.0}
{/if}
{if $pcfields.edition}
{foreach from=$pcfields.edition item=field name=loop}
%E {$field}
{/foreach}
{/if}
{foreach from=$pcfields.language item=lang}
%G {$lang}
{/foreach}
{assign var=marcField440 value=$pcfields.series}
{* Display subject section if at least one subject exists. *}
{if $marcField440}
{foreach from=$marcField440 item=field name=loop}
%B {$field}
{/foreach}
{/if}
{if $pcfields.isbn}
{foreach from=$pcfields.isbn item=field name=loop}
%@ {$field}
{/foreach}
{/if}
{if $pcfields.issn}
{foreach from=$pcfields.issn item=field name=loop}
%@ {$field}
{/foreach}
{/if}
%T {$pcfields.title}
{if $pcfields.jtitle}
%J {$pcfields.jtitle}
{/if}
{if $pcfields.jvol}
%V {$pcfields.jvol}
{/if}
{if $pcfields.jissue}
%N {$pcfields.jissue}
{/if}
{if $pcfields.jspage}
%P {$pcfields.jspage}{if $pcfields.jepage}-{$pcfields.jepage}{/if}},
{/if}
{if $pcfields.url}
{foreach from=$pcfields.url item=field name=loop}
%U {$field}
{/foreach}
{/if}
{if $pcfields.doi}
%R {$pcfields.doi.0}
{/if}
{if $pcfields.description}
%X {$pcfields.description.0}
{/if}
{if $pcfields.topic}
{foreach from=$pcfields.topic item=field name=loop}
%K {$field}
{/foreach}
{/if}
%~ TUBfind - Katalog der TU Hamburg-Harburg
{*
{assign var=marcField value=$marc->getFields('250')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
%7 {$field|getvalue:'a'}
{/foreach}
{/if}
*}
{*
{if $pcfields.id}
{assign var=id value=$pcfields.id}
{/if}
*}
