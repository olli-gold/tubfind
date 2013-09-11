<table class="datagrid extended">
     <colgroup>
        <col style="width: 60%;" />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
    </colgroup>
    <thead>
        <tr>
            <th>{translate text=title}</th>
            <th>{translate text=Year}</th>
            <th>{translate text=Tome}</th>
            <th>{translate text=Issue}</th>
            <th>{translate text=Pages}</th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$articleChildren item=mpChild name=loop}
    <tr class="{if ($smarty.foreach.loop.iteration % 2) == 0}odd{else}even{/if}">
        <td><a href="{$url}/Record/{$mpChild.id}">{$mpChild.title}</a></td>
        <td class="nowrap"><a href="{$url}/Record/{$mpChild.id}">{$mpChild.date}</a></td>
        <td class="nowrap"><a href="{$url}/Record/{$mpChild.id}">{$mpChild.volume}</a></td>
        <td class="nowrap"><a href="{$url}/Record/{$mpChild.id}">{$mpChild.issue}</a></td>
        <td class="nowrap"><a href="{$url}/Record/{$mpChild.id}">{$mpChild.pages}</a></td>
    </tr>
    {/foreach}
    </tbody>
</table>

<ul class="pager">
    <li class="next-parts">
       {translate text="multipart_next"}
    </li>
    <li class="setback-parts">
       {translate text="multipart_reset"}
    </li>
    <li class="all-parts">
       {translate text="multipart_all"}
    </li>
</ul>
