<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>XML Sitemap</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <style type="text/css">
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                        margin: 0;
                        background-color: #f9fafb; /* Lighter gray background */
                        color: #1f2937; /* Darker text for contrast */
                    }
                    #content {
                        margin: 40px auto;
                        max-width: 960px;
                        background-color: #fff;
                        padding: 20px 40px;
                        border-radius: 8px; /* Rounded corners */
                        box-shadow: 0 4px 10px rgba(0,0,0,0.05); /* Subtle shadow for depth */
                    }
                    h1 {
                        font-size: 28px;
                        font-weight: 600;
                        text-align: center;
                        color: #111827;
                    }
                    .expl {
                        margin: 12px 0 30px;
                        line-height: 1.5;
                        text-align: center;
                        color: #6b7280; /* Softer text color */
                    }
                    a {
                        color: #3b82f6; /* Modern blue for links */
                        text-decoration: none;
                    }
                    a:hover {
                        text-decoration: underline;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 14px;
                    }
                    thead th {
                        text-align: left;
                        padding: 12px 15px;
                        border-bottom: 2px solid #e5e7eb; /* Lighter, solid border */
                        font-weight: 600;
                        color: #4b5563;
                    }
                    tbody td {
                        padding: 12px 15px;
                        border-bottom: 1px solid #f3f4f6; /* Very light row separator */
                    }
                    tbody tr:last-child td {
                        border-bottom: none;
                    }
                    tbody tr:hover td {
                        background-color: #f9fafb; /* Light hover effect */
                    }
                    td a {
                        color: #1f2937;
                        font-weight: 500;
                    }
                    td a:hover {
                        color: #3b82f6;
                    }
                </style>
            </head>
            <body>
                <div id="content">
                    <h1>XML Sitemap</h1>
                    <p class="expl">
                        This sitemap is generated for search engines and helps them index the site's content more effectively.<br/>
                        Learn more about XML sitemaps on <a href="https://sitemaps.org" target="_blank" rel="noopener">sitemaps.org</a>.
                    </p>

                    <xsl:if test="count(sitemap:sitemapindex/sitemap:sitemap) &gt; 0">
                        <p class="expl">
                            This XML Sitemap Index contains <strong><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></strong> sitemaps.
                        </p>
                        <table id="sitemap">
                            <thead>
                                <tr>
                                    <th width="75%">Sitemap</th>
                                    <th width="25%">Last Modified</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                                    <tr>
                                        <td>
                                            <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                                        </td>
                                        <td>
                                            <xsl:value-of select="concat(substring(sitemap:lastmod,0,11), ' ', substring(sitemap:lastmod,12,8))"/>
                                        </td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </xsl:if>

                    <xsl:if test="count(sitemap:sitemapindex/sitemap:sitemap) &lt; 1">
                        <p class="expl">
                            This XML Sitemap contains <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong> URLs.
                        </p>
                        <table id="sitemap">
                            <thead>
                                <tr>
                                    <th width="75%">URL</th>
                                    <th width="10%">Images</th>
                                    <th width="15%">Last Modified</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="sitemap:urlset/sitemap:url">
                                    <tr>
                                        <td>
                                            <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                                        </td>
                                        <td>
                                            <xsl:value-of select="count(image:image)"/>
                                        </td>
                                        <td>
                                            <xsl:value-of select="concat(substring(sitemap:lastmod,0,11), ' ', substring(sitemap:lastmod,12,8))"/>
                                        </td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </xsl:if>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>