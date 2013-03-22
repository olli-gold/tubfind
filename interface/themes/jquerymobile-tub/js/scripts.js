$.mobile.pushStateEnabled = false;

{if $nlurls}
    /*{foreach from=$nlurls key=recordurl item=urldesc}
            {translate text="NL"}: <a href="{$recordurl}">{$urldesc}</a><br/>
                {/foreach}
                */
                document.getElementById("nlurls{$summId|escape}").childNodes[0].nodeValue = "Test";
{/if}
