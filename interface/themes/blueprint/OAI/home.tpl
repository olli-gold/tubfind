<div class="span-18">
  <h1>OAI Server</h1>
  <p>
    This OAI server is OAI 2.0 compliant.<br/>
    The OAI Server URL is: {$url}/OAI/Server
  </p>

  <h2>Available Functionality:</h2>
  <dl>
    <dt>Identify</dt>
    <dd>Returns the Identification information of this OAI Server.</dd>
    <dd>
      <form method="get" action="{$path}/OAI/Server">
        <input type="hidden" name="verb" value="Identify"/>
        <p>Accepts no additional parameters.</p>
        <input class="button" type="submit" name="submit" value="{translate text='Go'}"/>
      </form>
    </dd>

    <dt>ListIdentifiers</dt>
    <dd>Returns a listing of available identifiers</dd>
    <dd>
      <form method="get" action="{$path}/OAI/Server">
        <input type="hidden" name="verb" value="ListIdentifiers"/>
        <label class="span-3" for="ListIdentifier_from">{translate text="From"}:</label> <input id="ListIdentifier_from" type="text" name="from"/><br class="clear"/>
        <label class="span-3" for="ListIdentifier_until">{translate text="Until"}:</label> <input id="ListIdentifier_until" type="text" name="until"/><br class="clear"/>
        <label class="span-3" for="ListIdentifier_set">{translate text="Set"}:</label> <input id="ListIdentifier_set" type="text" name="set"/><br class="clear"/>
        <label class="span-3" for="ListIdentifier_metadataPrefix">{translate text="Metadata Prefix"}:</label> <input id="ListIdentifier_metadataPrefix" type="text" name="metadataPrefix"/><br class="clear"/>
        <label class="span-3" for="ListIdentifier_resumptionToken">{translate text="Resumption Token"}:</label> <input id="ListIdentifier_resumptionToken" type="text" name="resumptionToken"/><br class="clear"/>
        <input class="push-3 button" type="submit" name="submit" value="{translate text='Go'}"/><br class="clear"/>
      </form>
    </dd>

    <dt>ListMetadataFormats</dt>
    <dd>Returns a listing of available metadata formats.</dd>
    <dd>
      <form method="get" action="{$path}/OAI/Server">
        <input type="hidden" name="verb" value="ListMetadataFormats"/>
        <label class="span-3" for="ListMetadataFormats_identifier">{translate text="Identifier"}:</label> <input id="ListMetadataFormats_identifier" type="text" name="identifier"/><br class="clear"/>
        <input class="push-3 button" type="submit" name="submit" value="{translate text='Go'}"/><br class="clear"/>
      </form>
    </dd>

    <dt>ListSets</dt>
    <dd>Returns a listing of available sets.</dd>
    <dd>
      <form method="get" action="{$path}/OAI/Server">
        <input type="hidden" name="verb" value="ListSets"/>
        <label class="span-3" for="ListSets_metadataPrefix">{translate text="Metadata Prefix"}:</label> <input id="ListSets_metadataPrefix" type="text" name="metadataPrefix"/><br class="clear"/>
        <label class="span-3" for="ListSets_resumptionToken">{translate text="Resumption Token"}:</label> <input id="ListSets_resumptionToken" type="text" name="resumptionToken"/><br class="clear"/>
        <input class="push-3 button" type="submit" name="submit" value="{translate text='Go'}"/><br class="clear"/>
      </form>
    </dd>

    <dt>ListRecords</dt>
    <dd>Returns a listing of available records.</dd>
    <dd>
      <form method="get" action="{$path}/OAI/Server">
        <input type="hidden" name="verb" value="ListRecords"/>
        <label class="span-3" for="ListRecords_from">{translate text="From"}:</label> <input id="ListRecords_from" type="text" name="from"/><br class="clear"/>
        <label class="span-3" for="ListRecords_until">{translate text="Until"}:</label> <input id="ListRecords_until" type="text" name="until"/><br class="clear"/>
        <label class="span-3" for="ListRecords_set">{translate text="Set"}:</label> <input id="ListRecords_set" type="text" name="set"/><br class="clear"/>
        <label class="span-3" for="ListRecords_metadataPrefix">{translate text="Metadata Prefix"}:</label> <input id="ListRecords_metadataPrefix" type="text" name="metadataPrefix"/><br class="clear"/>
        <label class="span-3" for="ListRecords_resumptionToken">{translate text="Resumption Token"}:</label> <input id="ListRecords_resumptionToken" type="text" name="resumptionToken"/><br class="clear"/>
        <input class="push-3 button" type="submit" name="submit" value="{translate text='Go'}"/><br class="clear"/>
      </form>
    </dd>

    <dt>GetRecord</dt>
    <dd>Returns a single record.</dd>
    <dd>
      <form method="get" action="{$path}/OAI/Server">
        <input type="hidden" name="verb" value="GetRecord"/>
        <label class="span-3" for="GetRecord_identifier">{translate text="Identifier"}:</label> <input id="GetRecord_identifier" type="text" name="identifier"/><br class="clear"/>
        <label class="span-3" for="GetRecord_metadataPrefix">{translate text="Metadata Prefix"}:</label> <input id="GetRecord_metadataPrefix" type="text" name="metadataPrefix"/><br class="clear"/>
          <input class="push-3 button" type="submit" name="submit" value="{translate text='Go'}"/><br class="clear"/>
      </form>
    </dd>
  </dl>
</div>

<div class="span-5 last">
</div>

<div class="clear"></div>