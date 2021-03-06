

<div style="float:left;">
<ul class="linklist">
    {if $lookfor != ''}
        <li><a href="https://katalog.b.tu-harburg.de/DB=1/{translate text='opclang'}/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM={$lookfor}" target="_blank">{translate text='classic_catalog'}</a></li>
    {else}
        {if $id}
            <li><a href="https://katalog.b.tu-harburg.de/DB=1/{translate text='opclang'}/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM=ppn+{$id}" target="_blank">{translate text='classic_catalog'}</a></li>
        {else}
            <li><a href="https://katalog.b.tu-harburg.de/DB=1/{translate text='opclang'}/" target="_blank">{translate text='classic_catalog'}</a></li>
        {/if}
    {/if}

    <li><a href="{$path}/Cart/Home">{translate text="Book Bag"} <span id="cartSize"></span></a></li>

    {if !$user}
        {if $authMethod == 'Shibboleth'}
            | <a href="{$sessionInitiator}">{translate text="Institutional Login"}</a>
        {else}
            <li><a href="{$path}/MyResearch/Home">{translate text="Library Account"}</a></li>
        {/if}
    {else}
        <li><a  href="{$path}/MyResearch/Home">{translate text="Library Account"}</a> </li>
        <li><a  href="{$path}/MyResearch/Logout">{translate text="Log Out"}</a> </li>
    {/if}
</ul>
</div>

{*
<div style="float:right;">
    <ul class="linklist">
	<!-- OPC Login <li>{translate text="opc_login"}</li>-->
        <!--<li><a  href="{$path}/MyResearch/Home">{translate text="Library Account"}</a></li>
	<li>{translate text="pw_change"}</li>
	<li>{translate text="pw_forgot"}</li>-->
    </ul>
</div>
*}

