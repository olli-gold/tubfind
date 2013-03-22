<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:exist="http://exist.sourceforge.net/NS/exist" xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:php="http://php.net/xsl">

  <xsl:output encoding="UTF-8" indent="yes" omit-xml-declaration="no"/>

  <xsl:template match="/">
    <rss version="2.0">
      <channel>
        <title>
          <xsl:value-of select="php:function('translate', 'Results for')"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="$lookfor"/>
        </title>
        <description>
          <xsl:value-of select="php:function('translate', 'Displaying the top')"/>
          <xsl:text> </xsl:text>
          <xsl:choose>
            <xsl:when test="/json/responseHeader/params/rows &gt; /json/response/numFound">
              <xsl:value-of select="/json/response/numFound"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="/json/responseHeader/params/rows"/>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:text> </xsl:text>
          <xsl:value-of select="php:function('translate', 'search results of')"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="/json/response/numFound"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="php:function('translate', 'found')"/>
          <xsl:text>.</xsl:text>
        </description>
        <link><xsl:value-of select="$searchUrl"/></link>
        <xsl:for-each select="/json/response/docs">
          <xsl:if test="./id">
            <item>
              <link>
                <xsl:value-of select="$baseUrl"/>
                <xsl:value-of select="php:function('urlencode', string(./id))"/>
              </link>
              <guid isPermaLink="true">
                <xsl:value-of select="$baseUrl"/><xsl:value-of select="php:function('urlencode', string(./id))"/>
              </guid>
              <title><xsl:value-of select="./title"/></title>
              <xsl:choose>
                <xsl:when test="./last_indexed">
                  <pubDate>
                    <xsl:value-of select="php:function('xslRssDate', string(./last_indexed))"/>
                  </pubDate>
                </xsl:when>
                <xsl:when test="./publishDate">
                  <pubDate>
                    <xsl:text>01 Jan </xsl:text>
                    <xsl:value-of select="./publishDate"/>
                    <xsl:text> 00:00:00 GMT</xsl:text>
                  </pubDate>
                </xsl:when>
              </xsl:choose>
              <xsl:for-each select="./author">
                <dc:creator><xsl:value-of select="."/></dc:creator>
              </xsl:for-each>
              <xsl:if test="./format">
                <dc:format>
                  <xsl:for-each select="./format">
                    <xsl:if test="position()&gt;1"><xsl:value-of select="string(' / ')"/></xsl:if>
                    <xsl:value-of select="."/>
                  </xsl:for-each>
                </dc:format>
              </xsl:if>
              <xsl:if test="./publishDate">
                <dc:date><xsl:value-of select="./publishDate"/></dc:date>
              </xsl:if>
            </item>
          </xsl:if>
        </xsl:for-each>
      </channel>
    </rss>
  </xsl:template>

</xsl:stylesheet>