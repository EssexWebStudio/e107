<?php
/*
 * e107 website system
 *
 * Copyright (C) 2009-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 */

if (!defined('e107_INIT'))
{ 
	require_once("../../class2.php");
}

if (!e107::isInstalled('banner'))
{
	header("location:".e_BASE."index.php"); 
	exit;
}

e107::includeLan(e_PLUGIN."banner/languages/".e_LANGUAGE."_banner.php");

require_once(e_HANDLER."form_handler.php");
$rs = new form;
	
if (e_QUERY) {
	$query_string = intval(e_QUERY);
	$sql->db_Select("banner", "*", "banner_id = '{$query_string}' ");
	$row = $sql->db_Fetch();
	$ip = e107::getIPHandler()->getIP(FALSE);
	$newip = (strpos($row['banner_ip'], "{$ip}^") !== FALSE) ? $row['banner_ip'] : "{$row['banner_ip']}{$ip}^";
	$sql->db_Update("banner", "banner_clicks = banner_clicks + 1, `banner_ip` = '{$newip}' WHERE `banner_id` = '{$query_string}'");
	header("Location: {$row['banner_clickurl']}");
	exit;
}
	
require_once(HEADERF);
	
if (isset($_POST['clientsubmit'])) {
	
	$clean_login = $tp -> toDB($_POST['clientlogin']);
	$clean_password = $tp -> toDB($_POST['clientpassword']);
	
	if (!$sql->db_Select("banner", "*", "`banner_clientlogin` = '{$clean_login}' AND `banner_clientpassword` = '{$clean_password}'")) {
		$ns->tablerender(LAN_ERROR, "<br /><div style='text-align:center'>".BANNERLAN_20."</div><br />");
		require_once(FOOTERF);
		exit;
	}
	 
	$row = $sql->db_Fetch();
	$banner_total = $sql->db_Select("banner", "*", "`banner_clientname` = '{$row['banner_clientname']}'");
	 
	if (!$banner_total) {
		$ns->tablerender(LAN_ERROR, "<br /><div style='text-align:center'>".BANNERLAN_29."</div><br />");
		require_once(FOOTERF);
		exit;
	} else {
		while ($row = $sql->db_Fetch()) {
			 
			$start_date = ($row['banner_startdate'] ? strftime("%d %B %Y", $row['banner_startdate']) : BANNERLAN_31);
			$end_date = ($row['banner_enddate'] ? strftime("%d %B %Y", $row['banner_enddate']) : BANNERLAN_31);
			 
			$BANNER_TABLE_CLICKPERCENTAGE = ($row['banner_clicks'] && $row['banner_impressions'] ? round(($row['banner_clicks'] / $row['banner_impressions']) * 100)."%" : "-");
			$BANNER_TABLE_IMPRESSIONS_LEFT = ($row['banner_impurchased'] ? $row['banner_impurchased'] - $row['banner_impressions'] : BANNERLAN_30);
			$BANNER_TABLE_IMPRESSIONS_PURCHASED = ($row['banner_impurchased'] ? $row['banner_impurchased'] : BANNERLAN_30);
			$BANNER_TABLE_CLIENTNAME = $row['banner_clientname'];
			$BANNER_TABLE_BANNER_ID = $row['banner_id'];
			$BANNER_TABLE_BANNER_CLICKS = $row['banner_clicks'];
			$BANNER_TABLE_BANNER_IMPRESSIONS = $row['banner_impressions'];
			$BANNER_TABLE_ACTIVE = BANNERLAN_36.($row['banner_active'] != "255" ? LAN_YES : "<b>".LAN_NO."</b>");
			$BANNER_TABLE_STARTDATE = BANNERLAN_37." ".$start_date;
			$BANNER_TABLE_ENDDATE = BANNERLAN_34." ".$end_date;
			
			if ($row['banner_ip']) 
			{
				$tmp = explode("^", $row['banner_ip']);
				$BANNER_TABLE_IP_LAN = (count($tmp)-1);
				for($a = 0; $a <= (count($tmp)-2); $a++) {
					$BANNER_TABLE_IP .= $tmp[$a]."<br />";
				}
			}
			 
			if (!$BANNER_TABLE) 
			{
				if (file_exists(THEME."banner_template.php")) {
					require(THEME."banner_template.php");
				} else {
					require(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
				}
			}
			$textstring .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE);
		}
	}
	 
	if (!$BANNER_TABLE) 
	{
		if (file_exists(THEME."banner_template.php")) {
			require(THEME."banner_template.php");
		} else {
			require(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
		}
	}
	$textstart = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE_START);
	$textend = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE_END);
	$text = $textstart.$textstring.$textend;
	 
	echo $text;
	 
	require_once(FOOTERF);
	exit;
}
	
	
$BANNER_LOGIN_TABLE_LOGIN = $rs->form_text("clientlogin", 30, $id, 20, "tbox");
$BANNER_LOGIN_TABLE_PASSW = $rs->form_password("clientpassword", 30, "", 20, "tbox");
$BANNER_LOGIN_TABLE_SUBMIT = $rs->form_button("submit", "clientsubmit", LAN_CONTINUE);
	
if (!$BANNER_LOGIN_TABLE) {
	if (file_exists(THEME."banner_template.php")) {
		require_once(THEME."banner_template.php");
	} else {
		require_once(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
	}
}
$text = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_LOGIN_TABLE);
$ns->tablerender(BANNERLAN_19, $text);
	
	
require_once(FOOTERF);
	
