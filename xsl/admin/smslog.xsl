<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="tpl.default.xsl" />
	<xsl:decimal-format name="sek" decimal-separator="," grouping-separator="&#160;" />

	<xsl:template name="tabs">
	</xsl:template>

	<xsl:template match="/">
		<xsl:if test="/root/content[../meta/action = 'index']">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - SMS log'" />
				<xsl:with-param name="h1"    select="'SMS log'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<xsl:template match="content[../meta/action = 'index']">
		<form method="get" style="margin-top: 20px;">
			<fieldset>
				<label>Search</label>

				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'q'"/>
					<xsl:with-param name="label" select="'Search string:'" />
					<xsl:with-param name="value" select="/root/meta/url_params/q" />
				</xsl:call-template>

				<button class="longman neutral right" style="margin-top: 24px;">Search</button>
			</fieldset>
		</form>

		<xsl:call-template name="pagination">
			<xsl:with-param name="current_page"  select="number(/root/meta/url_params/page)" />
			<xsl:with-param name="link_first"    select="'/admin/smslog?page=1'" />
			<xsl:with-param name="link_previous" select="concat(concat(concat('/admin/smslog?q=',/root/meta/url_params/q),'&amp;page='),number(/root/meta/url_params/page) - 1)" />
			<xsl:with-param name="link_next"     select="concat(concat(concat('/admin/smslog?q=',/root/meta/url_params/q),'&amp;page='),number(/root/meta/url_params/page) + 1)" />
			<xsl:with-param name="link_last"     select="concat(concat(concat('/admin/smslog?q=',/root/meta/url_params/q),'&amp;page='),number(pages))" />
		</xsl:call-template>
		<table class="clear" style="width: 800px;">
			<thead>
				<tr>
					<th class="small_row">Id</th>
					<th>Sent status</th>
					<th>Received status</th>
					<th>Receiver</th>
					<th>Message</th>
					<th>Added to queue</th>
					<th>Sent</th>
					<!--th>-</th-->
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="smslog/sms">
					<tr>
						<td><xsl:value-of select="@id"/></td>
						<td>
							<xsl:choose>
								<xsl:when test="status = 'sent'"><strong class="yes">Sent</strong></xsl:when>
								<xsl:when test="status = 'queue'"><strong class="no">Queued</strong></xsl:when>
								<xsl:when test="status = 'failed'"><strong class="no">Failed</strong></xsl:when>
							</xsl:choose>
						</td>
						<td>
							<xsl:choose>
								<xsl:when test="dlr_status = 'delivered'"><strong class="yes">Delivered</strong></xsl:when>
								<xsl:when test="dlr_status = 'failed'"><strong class="no">Failed</strong></xsl:when>
							</xsl:choose>
						</td>
						<td><xsl:value-of select="to" /></td>
						<td><xsl:value-of select="substring(msg, 1, 20)" /></td>
						<td style="white-space: nowrap;"><xsl:value-of select="queued" /></td>
						<td style="white-space: nowrap;"><xsl:value-of select="sent" /></td>
						<!--td><a href="/admin/smslog?id={@id}">Visa</a></td-->
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
		<xsl:call-template name="pagination">
			<xsl:with-param name="current_page"  select="number(/root/meta/url_params/page)" />
			<xsl:with-param name="link_first"    select="'/admin/smslog?page=1'" />
			<xsl:with-param name="link_previous" select="concat('/admin/smslog?page=', number(/root/meta/url_params/page) - 1)" />
			<xsl:with-param name="link_next"     select="concat('/admin/smslog?page=', number(/root/meta/url_params/page) + 1)" />
			<xsl:with-param name="link_last"     select="concat('/admin/smslog?page=', number(pages))" />
		</xsl:call-template>
	</xsl:template>

</xsl:stylesheet>