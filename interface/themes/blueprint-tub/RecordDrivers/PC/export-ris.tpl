{if $pcfields.id}
{assign var=id value=$pcfields.id}
{/if}
{assign var=isphdThesis value=false}
{assign var=$recordFormat value=$pcfields.format}
{if is_array($recordFormat)}
{foreach from=$recordFormat item=displayFormat name=loop}
TY {$displayFormat}
{/foreach}
{if in_array('dissertation', $recordFormat)}
{assign var=isphdThesis value=true}
{/if}
{else}
TY {$recordFormat}
{if $recordFormat == 'dissertation'}
{assign var=isphdThesis value=true}
{/if}
{/if}
ID {$id}
T1 {$pcfields.title}
{assign var=marcField440 value=$pcfields.series}
{* Display subject section if at least one subject exists. *}
{if $marcField440}
{foreach from=$marcField440 item=field name=loop}
T2 {$field}
{/foreach}
{/if}
{assign var=authors value=$pcfields.author}
{if $authors}
{foreach from=$authors item=field name=loop}
AU {$field}
{/foreach}
{/if}
{*
{foreach from=$pcfields.language item=lang}
LA {$lang}
{/foreach}
*}
{assign var=publisher value=$pcfields.publisher}
{if $pcfields.publisher}
{foreach from=$pcfields.publisher item=field name=loop}
{assign var=pub value=$field}
{assign var=pubArr value=':'|explode:$pub}
{if $pubArr.1}
CY {$pubArr.0}
PB {$pubArr.1}
{else}
{if $pubArr.0}
PB {$pubArr.0}
{/if}
{/if}
{/foreach}
{/if}
{if $pcfields.publishDate}
PY {$pcfields.publishDate.0}
{/if}
{if $pcfields.edition}
{*
{foreach from=$pcfields.edition item=field name=loop}
ED {$field}
{/foreach}
*}
{/if}
L1 {$url}/Record/{$id|escape:"url"}
{if $pcfields.url}
{foreach from=$pcfields.url item=field name=loop}
L1 {$field}
{/foreach}
{/if}
{if $pcfields.doi}
DO {$pcfields.doi.0}
{/if}
{if $pcfields.description}
AB {$pcfields.description.0}
{/if}
{*
{assign var=marcField value=$marc->getField('300')}
{if $marcField}
OP {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('500')}
{if $marcField}
NO {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('099')}
{if $marcField}
CN {$marcField|getvalue:'a'}
{else}
{assign var=marcField value=$marc->getField('050')}
{if $marcField}
CN {foreach from=$marcField->getSubfields() item=subfield name=subloop}{$subfield->getData()}{/foreach}
{/if}
{/if}
*}
{if $pcfields.isbn}
{foreach from=$pcfields.isbn item=field name=loop}
SN {$field}
{/foreach}
{/if}
{if $pcfields.issn}
{foreach from=$pcfields.issn item=field name=loop}
SN {$field}
{/foreach}
{/if}
{*
{assign var=marcField value=$marc->getFields('650')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
K1 {foreach from=$field->getSubfields() item=subfield name=subloop}{if !$smarty.foreach.subloop.first} : {/if}{assign var=subfield value=$subfield->getData()}{$subfield}{/foreach}
{/foreach}{/if}
*}
{if $pcfields.jtitle}
T2 {$pcfields.jtitle}
{/if}
{if $pcfields.jvol}
VL {$pcfields.jvol}
{/if}
{if $pcfields.jissue}
IS {$pcfields.jissue}
{/if}
{if $pcfields.jspage}
SP {$pcfields.jspage}
{/if}
{if $pcfields.jepage}
EP {$pcfields.jepage}
{/if}
{if $pcfields.topic}
{foreach from=$pcfields.topic item=field name=loop}
KW {$field}
{/foreach}
{/if}
TS TUBfind - Katalog der TU Hamburg-Harburg
