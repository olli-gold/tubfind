<table class="datagrid extended">
     <colgroup>
        <col style="width: 10%;" />
        <col style="width: 60%;" />
        <col style="width: 30%;" />
    </colgroup>
    <thead>
        <tr>
            <th>{translate text=Tome}</th>
            <th>{translate text=title}</th>
            <th>{translate text=Year}</th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$multipartChildren item=mpChild name=loop}
    <tr class="{if ($smarty.foreach.loop.iteration % 2) == 0}odd{else}even{/if}">
        <td>
            <a href="{$url}/Record/{$mpChild.id}">
                <!-- {foreach key=a from=$mpChild.parts item=number}{if $a != 0},{/if} {$number}{/foreach} -->
                {$mpChild.part}
            </a>
        </td>
        <td><a href="{$url}/Record/{$mpChild.id}">{$mpChild.title}</a></td>
        <td class="nowrap"><a href="{$url}/Record/{$mpChild.id}">{$mpChild.date}</a></td>
    </tr>
    {/foreach}
    </tbody>
</table>