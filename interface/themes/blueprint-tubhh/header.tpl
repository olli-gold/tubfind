<div style="width;100%;">
    <div class="tulogo">
	<a href="http://www.tu-harburg.de" alt="TUHH-Home" title="TUHH-Home">
	<img src="{$path}/interface/themes/blueprint-tubhh/images/logo/logo-tuhh-230x105.jpg" /></a>
    </div><!-- tulogo -->

    <div class="branding">
	<a href="{translate text="wp_linkhome"}"><h1 style="font-size:200%;font-weight:bold;line-height:1em;padding-left:20px;">{translate text="wp_universitaetsbibliothek"}</h1></a>
	<a href="{$url}">
	<!-- <img style="width:150px;height:50px;padding-left:20px;float:left;" src="{$path}/interface/themes/blueprint-tubhh/images/logo/tubfind_logo-grau.jpg" alt="TUBfind-Home" title="TUBfind-Home"/> -->
	</a>
	&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red; font-weight:bold;">Dies ist das Entwicklungs- und Testsystem!</span>
	<div style="float:left;padding-left:180px;">
        <form method="post" name="german" action="" id="germanForm"><input type="hidden" name="mylang" value="de" /></form>
        <form method="post" name="english" action="" id="englishForm"><input type="hidden" name="mylang" value="en" /></form>
	<a href="#" onClick="document.german.submit();">DE</a> | <a href="#" onClick="document.english.submit();">EN</a>
        {if isset($gbvmessage)}
            <br/><span style="color:red;font-weight:bold;">{$gbvmessage}</span>
        {/if}
	</div>
	
        <div style="float:right;padding-right:30px;"><!-- login -->
	</div> <!-- login -->

    </div><!-- tubbranding -->

</div><!-- tuheader -->
<div class="clear"></div>




  {* wird ausgeblendet ueber nicht erfuellte if-Klausel ;-) *}
  {if $thisWillNeverBeShown}
  {*if is_array($allLangs) && count($allLangs) > 1*}
  <form method="post" name="langForm" action="" id="langForm">
    <!--<label for="langForm_mylang">{translate text="Language"}:</label>-->
    <select id="langForm_mylang" name="mylang" class="jumpMenu">
      {foreach from=$allLangs key=langCode item=langName}
        <option value="{$langCode}"{if $userLang == $langCode} selected="selected"{/if}>{translate text=$langName}</option>
      {/foreach}
    </select>
    <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
  </form>
  {/if}


<br/>

