{js filename="jquery.cookie.js"}
{if $bookBag}
  {js filename="cart.js"}
  {assign var=bookBagItems value=$bookBag->getItems()}
{/if}
<a id="logo" href="{$url}"></a>
<div id="headerRight">
  {if $bookBag}
  <div id="cartSummary" class="cartSummary">
      <a id="cartItems" title="{translate text='View Book Bag'}" class="bookbag" href="{$url}/Cart/Home"><strong><span>{$bookBagItems|@count}</span></strong> {translate text='items'} {if $bookBag->isFull()}({translate text='bookbag_full'}){/if}</a>
      <a id="viewCart" title="{translate text='View Book Bag'}" class="viewCart bookbag offscreen" href="{$url}/Cart/Home"><strong><span id="cartSize">{$bookBagItems|@count}</span></strong> {translate text='items'}<span id="cartStatus">{if $bookBag->isFull()}({translate text='bookbag_full'}){else}&nbsp;{/if}</span></a>
  </div>
  {/if}
  <div id="logoutOptions"{if !$user} class="hide"{/if}>
    <a class="account" href="{$path}/MyResearch/Home">{translate text="Your Account"}</a> |
    <a class="logout" href="{$path}/MyResearch/Logout">{translate text="Log Out"}</a>
  </div>
  <div id="loginOptions"{if $user} class="hide"{/if}>
  {if $authMethod == 'Shibboleth'}
    <a class="login" href="{$sessionInitiator}">{translate text="Institutional Login"}</a>
  {else}
    <a class="login" href="{$path}/MyResearch/Home">{translate text="Login"}</a>
  {/if}
  </div>
  {if is_array($allLangs) && count($allLangs) > 1}
  <form method="post" name="langForm" action="" id="langForm">
    <label for="langForm_mylang">{translate text="Language"}:</label>
    <select id="langForm_mylang" name="mylang" class="jumpMenu">
      {foreach from=$allLangs key=langCode item=langName}
        <option value="{$langCode}"{if $userLang == $langCode} selected="selected"{/if}>{translate text=$langName}</option>
      {/foreach}
    </select>
    <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
  </form>
  {/if}
</div>

<div class="clear"></div>
