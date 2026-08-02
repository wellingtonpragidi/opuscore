<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>Sitemap XML</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<link rel="shortcut icon" href="favicon.ico" />
				<style type="text/css">
					body {
						font-family: "Lucida Grande", "Lucida Sans Unicode", Tahoma, Verdana, system-ui, Segoe UI, Roboto, Oxygen, Ubuntu, Fira Sans, Droid Sans, Helvetica Neue, sans-serif;
						font-size: 14px;
						margin:0 0;
					}
					#header {
					    background-color: #475263;
					    padding: 25px 30px 25px 30px;
					    color: #E7F1D4;
					}
					#header h1 {
					    margin: 13px 0 11px;
					}
					#header p {
					    margin-top: 0;
					}
					#header a {
					    color: #E7F1D4;
					}
					#content {
					    margin: 15px;
					}
					#content a {
					    font-family: monospace;
					    color: #783739;
					}
					table {
					    counter-reset: mynum;
					}
					tr th {
						text-align: left;
						padding: 6px 11px 7px 11px;
						font-size: 14px;
					}
					tr td {
						font-size: 13px;
					}
					tr th:first-child, tr td:first-child {
						text-align: center;
						width: 30px
					}
					tr th:first-child {
						padding: 6px 0 7px 0;
					}
					tr td:first-child:before {
						content: counter(mynum);
  						counter-increment: mynum;
					}
					tr:nth-child(odd) {
					    background-color: #E7F1D4;
					}
					#footer {
						padding: 10px 3px 3px 15px;
						margin-top: 15px;
						font-size: 12px;
						color: #777;
					}
					#footer a {
						color: #656565;
					}
				</style>
			</head>
			<body>
				<xsl:apply-templates></xsl:apply-templates>
				<div id="footer">
					Sitemap XML gerado por <a href="https://webship.com.br" title="Desenvolvimento web">webship</a>.
				</div>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="sitemap:urlset">
		<div id="header">
			<h1>Mapa do site - XML</h1>
			<p>Sitemap XML gerado por <a href="https://webship.com.br" title="Desenvolvimento e designer web">webship</a> destinado para mecanismos de busca como <a href="https://www.google.com/">Google</a> ou <a href="https://www.bing.com/">Bing</a>.</p>
		</div>
		<div id="content">
			<table cellpadding="5">
				<tr style="border-bottom:1px black solid;">
					<th>Nº</th>
					<th>URL</th>
					<th>Última modificação (GMT/UTC)</th>
				</tr>
				<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
				<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
				<xsl:for-each select="./sitemap:url">
					<tr>
						<td></td>
						<td>
							<xsl:variable name="itemURL">
								<xsl:value-of select="sitemap:loc"/>
							</xsl:variable>
							<a href="{$itemURL}">
								<xsl:value-of select="sitemap:loc"/>
							</a>
						</td>
						<td>
							<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</div>
	</xsl:template>
	
</xsl:stylesheet>