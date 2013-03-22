{* Your footer *}

<div style="width:30%;float:left;">
<ul class="linklist">
<li>{translate text='wp_link_oeffnungszeiten'}</li>
<li>{translate text='wp_link_adresse'}</li>
</ul>
</div>


<div style="width:30%;float:left;">
<ul class="linklist">
<li>{translate text="wp_link_impressum"}</li>
<li>{translate text='wp_link_datenschutz'}</li>
{if $lookfor != ''}
    <li><a href="https://katalog.b.tu-harburg.de/DB=1/{translate text='opclang'}/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM={$lookfor}" target="_blank">{translate text='classic_catalog'}</a></li>
{else}
    <li><a href="https://katalog.b.tu-harburg.de/DB=1/{translate text='opclang'}/" target="_blank">{translate text='classic_catalog'}</a></li>
{/if}
</ul>
</div>

<div style="width:30%;float:left;">
<ul class="linklist">
<li>{translate text='wp_link_getalibrarycard'}</li>
<li>{translate text = "acq_prop"} </li>
<li>{translate text='wp_link_tubfindblog'}</li>
</ul>
</div>

<div class="clear"></div>
