TY {$displayFormat}
{assign var=marcField value=$marc->getField('245')}
T1 {$marcField|getvalue:'a'}{if $marcField|getvalue:'b'} {$marcField|getvalue:'b'|replace:'/':''}{/if}

{* Load the three possible subject fields -- 440 is deprecated but
   still exists in many catalogs. *}
{assign var=marcField440 value=$marc->getFields('440')}
{assign var=marcField490 value=$marc->getFields('490')}
{assign var=marcField830 value=$marc->getFields('830')}
{* Check for 490's with indicator 1 == 0; these should be displayed
   since they will have no corresponding 830 field.  Other 490s would
   most likely be redundant and can be ignored. *}
{assign var=visible490 value=0}
{if $marcField490}
{foreach from=$marcField490 item=field}
{if $field->getIndicator(1) == 0}
{assign var=visible490 value=1}
{/if}
{/foreach}
{/if}
{* Display subject section if at least one subject exists. *}
{if $marcField440 || $visible490 || $marcField830}
{if $marcField440}
{foreach from=$marcField440 item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{if $visible490}
{foreach from=$marcField490 item=field name=loop}
{if $field->getIndicator(1) == 0}
T2 {$field|getvalue:'a'}
{/if}
{/foreach}
{/if}
{if $marcField830}
{foreach from=$marcField830 item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{/if}
{assign var=marcField value=$marc->getField('100')}
{if $marcField}
A1 {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('110')}
{if $marcField}
A2 {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getFields('700')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
A2 {$field|getvalue:'a'}
{/foreach}
{/if}
{foreach from=$recordLanguage item=lang}
LA {$lang}
{/foreach}
{assign var=marcField value=$marc->getFields('260')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
CY {$field|getvalue:'a'|replace:':':''} 
PB {$field|getvalue:'b'|replace:',':''} 
PY {$field|getvalue:'c'|replace:'.':''}
{/foreach}
{/if}
{assign var=marcField value=$marc->getFields('250')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
ED {$field|getvalue:'a'}
{/foreach}
{/if}
UL {$url}/Record/{$id|escape:"url"}
{assign var=marcField value=$marc->getField('520')}
{if $marcField}
AB {$marcField|getvalue:'a'} {$marcField|getvalue:'b'}
{/if}
{assign var=marcField value=$marc->getField('500')}
{if $marcField}
NO {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('020')}
{if $marcField}
SN {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getFields('650')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
KW {foreach from=$field->getSubfields() item=subfield name=subloop}{if !$smarty.foreach.subloop.first} : {/if}{assign var=subfield value=$subfield->getData()}{$subfield}{/foreach}{if $smarty.foreach.loop.last} 
{/if}
{/foreach}{/if}
{assign var=marcField value=$marc->getField('773')}
{if $marcField}
{if $marcField|getvalue:'t'}
T2 {$marcField|getvalue:'t'}
{/if}
{if $marcField|getvalue:'x'}
SN {$marcField|getvalue:'x'}
{/if}
{/if}
{assign var=marcField value=$marc->getFields('856')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
L1 {$field|getvalue:'u'}
{/foreach}
{/if}
{assign var=marcField value=$marc->getField('952')}
{if $marcField}
{if $marcField|getvalue:'d'}
VL {$marcField|getvalue:'d'}
{/if}
{if $marcField|getvalue:'j'}
PY {$marcField|getvalue:'j'}
{/if}
{if $marcField|getvalue:'e'}
IS {$marcField|getvalue:'e'}
{/if}
{if $marcField|getvalue:'h'}
{assign var=hField value=$marcField|getvalue:'h'}
{assign var=pages value='-'|explode:$hField}
SP {$pages.0}
EP {$pages.1}
{/if}
{/if}
ID GBV-{$id}
TS TUBfind - Katalog der TU Hamburg-Harburg