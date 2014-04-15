{if $pcfields.id}
{assign var=id value=$pcfields.id}
{/if}
{assign var=isphdThesis value=false}
{assign var=$recordFormat value=$pcfields.format}
{if is_array($recordFormat)}
{foreach from=$recordFormat item=displayFormat name=loop}
RT {$displayFormat}
{/foreach}
{if in_array('dissertation', $recordFormat)}
{assign var=isphdThesis value=true}
{/if}
{else}
RT {$recordFormat}
{if $recordFormat == 'dissertation'}
{assign var=isphdThesis value=true}
{/if}
{/if}
IP {$id}
T1 {$pcfields.title}
{foreach from=$pcfields.title_short item=ts}
TS {$ts}
{/foreach}
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
A1 {$field}
{/foreach}
{/if}
{foreach from=$pcfields.language item=lang}
LA {$lang}
{/foreach}
{assign var=publisher value=$pcfields.publisher}
{if $pcfields.publisher}
{foreach from=$pcfields.publisher item=field name=loop}
{assign var=pub value=$field}
{assign var=pubArr value=':'|explode:$pub}
{if $pubArr.1}
PP {$pubArr.0}
PB {$pubArr.1}
{else}
{if $pubArr.0}
PB {$pubArr.0}
{/if}
{/if}
{/foreach}
{/if}
{if $pcfields.publishDate}
YR {$pcfields.publishDate.0}
{/if}
{if $pcfields.edition}
{foreach from=$pcfields.edition item=field name=loop}
ED {$field}
{/foreach}
{/if}
UL {$url}/Record/{$id|escape:"url"}
{if $pcfields.url}
{foreach from=$pcfields.url item=field name=loop}
UL {$field}
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
JF {if is_array($pcfields.jtitle)}{$pcfields.jtitle.0}{else}{$pcfields.jtitle}{/if}
{/if}
{if $pcfields.jvol}
VO {if is_array($pcfields.jvol)}{$pcfields.jvol.0}{else}{$pcfields.jvol}{/if}
{/if}
{if $pcfields.jissue}
IS {if is_array($pcfields.jissue)}{$pcfields.jissue.0}{else}{$pcfields.jissue}{/if}
{/if}
{if $pcfields.jspage}
SP {if is_array($pcfields.jspage)}{$pcfields.jspage.0}{else}{$pcfields.jspage}{/if}
{/if}
{if $pcfields.jepage}
OP {if is_array($pcfields.jepage)}{$pcfields.jepage.0}{else}{$pcfields.jepage}{/if}
{/if}
SL TU Hamburg-Harburg
