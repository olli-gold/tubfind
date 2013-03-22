{if $multipartChildren}
<table class="datagrid extended">
	<tr>
        <th>{translate text=Tome}</th>
        <th>{translate text=Year}</th>
        <th>{translate text=title}</th>
    </tr>
    {foreach from=$multipartChildren item=mpChild name=loop}
    <tr class="{if ($smarty.foreach.loop.iteration % 2) == 0}odd{else}even{/if}">
        <td class="nowrap">
            <a href="{$url}/Record/{$mpChild.id}">
                {foreach key=a from=$mpChild.parts item=number}{if $a != 0},{/if} {$number}{/foreach}
            </a>
        </td>
        <td class="nowrap"><a href="{$url}/Record/{$mpChild.id}">{$mpChild.date}</a></td>
        <td><a href="{$url}/Record/{$mpChild.id}">{$mpChild.title}</a></td>
	</tr>
    {/foreach}
</table>
{/if}

