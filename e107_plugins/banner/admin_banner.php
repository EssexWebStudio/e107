<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner Administration
 *
*/

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 *
 */


// TODO FIXME needs validation (e.g. Click URL field is not checked  to be sure it's an URL)

require_once('../../class2.php');
if (!getperms('D'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'banner';

require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'file_class.php');
$fl = e107::getFile();
$frm = e107::getForm();
$mes = e107::getMessage();
$tp = e107::getParser();

include_lan(e_PLUGIN.'banner/languages/'.e_LANGUAGE.'_admin_banner.php');
include_lan(e_PLUGIN.'banner/languages/'.e_LANGUAGE.'_menu_banner.php');


if(e_QUERY)
{
	list($action, $sub_action, $id) = explode('.', e_QUERY);
}

$images = $fl->get_files(e_IMAGE.'banners/','','standard');


$menu_pref = e107::getConfig('menu')->getPref('');
if (isset($_POST['update_menu']))
{
	$temp['banner_caption']		= $tp->toDB($_POST['banner_caption']);
	$temp['banner_amount']		= intval($_POST['banner_amount']);
	$temp['banner_rendertype']	= intval($_POST['banner_rendertype']);

	if (isset($_POST['multiaction_cat_active']))
	{
		/*$array_cat = explode("-", $_POST['catid']);
		$cat='';
		for($i = 0; $i < count($array_cat); $i++)
		{
			$cat .= $tp->toDB($array_cat[$i])."|";
		}
		$cat = substr($cat, 0, -1);*/
		$cat = implode('|', $tp->toDB($_POST['multiaction_cat_active']));
		$temp['banner_campaign'] = $cat;
	}
	if ($admin_log->logArrayDiffs($temp,$menu_pref,'BANNER_01'))
	{
		$menuPref = e107::getConfig('menu');
		//e107::getConfig('menu')->setPref('', $menu_pref);
		//e107::getConfig('menu')->save(false, true, false);
		foreach ($temp as $k => $v)
		{
			$menuPref->setPref($k, $v);
		}
		$menuPref->save(false, true, false);

		//banners_adminlog('01', $menu_pref['banner_caption'].'[!br!]'.$menu_pref['banner_amount'].', '.$menu_pref['banner_rendertype'].'[!br!]'.$menu_pref['banner_campaign']);
	}
}



if (vartrue($_POST['createbanner']) || vartrue($_POST['updatebanner']))
{
	$start_date = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	$end_date = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
	$cli = $tp->toDB($_POST['client_name'] ? $_POST['client_name'] : $_POST['banner_client_sel']);
	$cLogin = $tp->toDB($_POST['client_login']);
	$cPassword = $tp->toDB($_POST['client_password']);
	$banImage = $tp->toDB($_POST['banner_image']);
	$banURL = $tp->toDB($_POST['click_url']);
	$cam = $tp->toDB($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);

	/* FIXME - can be removed?
	if ($_POST['banner_pages'])
	{	// Section redundant?
		$postcampaign = $tp->toDB($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
		$pagelist = explode("\r", $_POST['banner_pages']);
		for($i = 0 ; $i < count($pagelist) ; $i++)
		{
			$pagelist[$i] = trim($pagelist[$i]);
		}
		$plist = implode("|", $pagelist);
		$pageparms = $postcampaign."^".$_POST['banner_listtype']."-".$plist;
		$pageparms = preg_replace("#\|$#", "", $pageparms);
		$pageparms = (trim($_POST['banner_pages']) == '') ? '' : $pageparms;
		$cam = $pageparms;
		$logString = $postcampaign.'[!br!]';
	}
	else
	{
		$cam = $tp->toDB($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
	}
	*/

	$logString .= $cam.'[!br!]'.$cli.'[!br!]'.$banImage.'[!br!]'.$banURL;
	if ($_POST['createbanner'])
	{
		e107::getMessage()->addAuto($sql->db_Insert("banner", "0, '".$cli."', '".$cLogin."', '".$cPassword."', '".$banImage."', '".$banURL."', '".intval($_POST['impressions_purchased'])."', '{$start_date}', '{$end_date}', '".intval($_POST['banner_class'])."', 0, 0, '', '".$cam."'"), 'insert', LAN_CREATED, false, false);
		banners_adminlog('02',$logString);
	}
	else // updating, not creating
	{
		e107::getMessage()->addAuto($sql->db_Update("banner", "banner_clientname='".$cli."', banner_clientlogin='".$cLogin."', banner_clientpassword='".$cPassword."', banner_image='".$banImage."', banner_clickurl='".$banURL."', banner_impurchased='".intval($_POST['impressions_purchased'])."', banner_startdate='{$start_date}', banner_enddate='{$end_date}', banner_active='".intval($_POST['banner_class'])."', banner_campaign='".$cam."' WHERE banner_id=".intval($_POST['eid'])), 'update', LAN_UPDATED, false, false);
		banners_adminlog('03',$logString);
	}

	unset($_POST['client_name'], $_POST['client_login'], $_POST['client_password'], $_POST['banner_image'], $_POST['click_url'], $_POST['impressions_purchased'], $start_date, $end_date, $_POST['banner_enabled'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['banner_class'], /*$_POST['banner_pages'],*/ $_POST['banner_listtype']);
}

/* DELETE ACTIONS */
if (isset($_POST['delete_cancel'])) // delete cancelled - redirect back to 'manage'
{
	session_write_close();
	header('Location:'.e_SELF);
	exit;
}

if (vartrue($action) == "delete" && $sub_action && varsettrue($_POST['delete_confirm'])) // delete has been confirmed, process
{
	if($sql->db_Delete("banner", "banner_id=".intval($sub_action)))
	{
		$mes->addSuccess(LAN_DELETED);
		banners_adminlog('04','Id: '.intval($sub_action));
	}
	else  // delete failed - redirect back to 'manage' and display message
	{
		$mes->addWarning(LAN_DELETED_FAILED);
		session_write_close();
		header('Location:'.e_SELF);
		exit;
	}
}
elseif ($action == "delete" && $sub_action) // confirm delete
{ // shown only if JS is disabled or by direct url hit (?delete.banner_id)
	$mes->addWarning(LAN_CONFIRMDEL);
	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<fieldset id='core-banner-delete-confirm'>
		<legend class='e-hideme'>".LAN_CONFIRMDEL."</legend>
			<div class='buttons-bar center'>
				".$frm->admin_button('delete_confirm', LAN_CONFDELETE, 'delete')."
				".$frm->admin_button('delete_cancel', LAN_CANCEL, 'cancel')."
				<input type='hidden' name='id' value='".$sub_action."' />
			</div>
		</fieldset>
		</form>
	";
	$ns->tablerender(LAN_CONFDELETE, $mes->render() . $text);

	require_once(e_ADMIN."footer.php");
	exit;
}


if ($sql->db_Select("banner"))
{
	while ($banner_row = $sql->db_Fetch())
	{
		if (strpos($banner_row['banner_campaign'], "^") !== FALSE) {
			$campaignsplit = explode("^", $banner_row['banner_campaign']);
			$banner_row['banner_campaign'] = $campaignsplit[0];
		}

		if ($banner_row['banner_campaign']) {
			$campaigns[] = $banner_row['banner_campaign'];
		}
		if ($banner_row['banner_clientname']) {
			$clients[] = $banner_row['banner_clientname'];
		}
		if ($banner_row['banner_clientlogin']) {
			$logins[] = $banner_row['banner_clientlogin'];
		}
		if ($banner_row['banner_clientpassword']) {
			$passwords[] = $banner_row['banner_clientpassword'];
		}
	}
}


if (!$action) {
	$text = "
		<form method='post' action='".e_SELF."' id='core-banner-list-form'>
			<fieldset id='core-banner-list'>
				<legend class='e-hideme'>".LAN_MANAGE."</legend>
				<table class='table adminlist'>
					<colgroup span='7'>
						<col style='width: 5%'></col>
						<col style='width: 35%'></col>
						<col style='width: 10%'></col>
						<col style='width: 10%'></col>
						<col style='width: 15%'></col>
						<col style='width: 15%'></col>
						<col style='width: 10%'></col>
					</colgroup>
					<thead>
						<tr>
							<th class='center'>".LAN_ID."</th>
							<th>".BNRLAN_9."</th>
							<th class='center'>".BNRLAN_10."</th>
							<th class='center'>".BNRLAN_11."</th>
							<th class='center'>".BNRLAN_12."</th>
							<th class='center'>".BNRLAN_13."</th>
							<th class='center last'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>
	";

	if (!$banner_total = $sql->db_Select("banner")) {
		$text .= "<tr><td colspan='7' class='center'>".BNRLAN_15."</td></tr>";
	} else {
		while ($banner_row = $sql->db_Fetch()) {

			$clickpercentage = ($banner_row['banner_clicks'] && $banner_row['banner_impressions'] ? round(($banner_row['banner_clicks'] / $banner_row['banner_impressions']) * 100)."%" : "-");
			$impressions_left = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] - $banner_row['banner_impressions'] : BNRLAN_16);
			$impressions_purchased = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] : BNRLAN_16);

			$start_date = ($banner_row['banner_startdate'] ? strftime("%d %B %Y", $banner_row['banner_startdate']) : LAN_NONE);
			$end_date = ($banner_row['banner_enddate'] ? strftime("%d %B %Y", $banner_row['banner_enddate']) : LAN_NONE);

			if (strpos($banner_row['banner_campaign'], "^") !== FALSE) {
				$campaignsplit = explode("^", $banner_row['banner_campaign']);
				$banner_row['banner_campaign'] = $campaignsplit[0];
				$textvisivilitychanged = "(*)";
			} else {
				$textvisivilitychanged = "";
			}

			$text .= "
						<tr>
							<td class='center'>".$banner_row['banner_id']."</td>
							<td class='e-pointer' onclick=\"e107Helper.toggle('banner-infocell-{$banner_row['banner_id']}')\">
								<a href='#banner-infocell-{$banner_row['banner_id']}' class='action e-expandit f-right' title='".BNRLAN_65."'><img class='action info S16' src='".E_16_CAT_ABOUT."' alt='' /></a>
								".($banner_row['banner_clientname'] ? $banner_row['banner_clientname'] : BNRLAN_66)."
								<div class='e-hideme clear' id='banner-infocell-{$banner_row['banner_id']}'>
									<div class='indent'>
										<div class='field-spacer'><strong>".BNRLAN_24.": </strong>".$banner_row['banner_campaign']."</div>
										<div class='field-spacer'><strong>".LAN_VISIBILITY." </strong>".r_userclass_name($banner_row['banner_active'])." ".$textvisivilitychanged."</div>
										<div class='field-spacer'><strong>".BNRLAN_45.": </strong>".$start_date."</div>
										<div class='field-spacer'><strong>".BNRLAN_21.": </strong>".$end_date."</div>
									</div>
								</div>
							</td>
							<td class='center'>".$banner_row['banner_clicks']."</td>
							<td class='center'>".$clickpercentage."</td>
							<td class='center'>".$impressions_purchased."</td>
							<td class='center'>".$impressions_left."</td>
							<td class='center'>

								<a href='".e_SELF."?create.edit.".$banner_row['banner_id']."'>".ADMIN_EDIT_ICON."</a>
								<a class='action delete' id='banner-delete-{$banner_row['banner_id']}' href='".e_SELF."?delete.".$banner_row['banner_id']."' rel='no-confirm' title='".LAN_CONFDELETE."'>".ADMIN_DELETE_ICON."</a>
							</td>
						</tr>
				";
		}
	}
	$text .= "
					</tbody>
				</table>
				<input type='hidden' id='delete_confirm' name='delete_confirm' value='0' />
			</fieldset>
		</form>
		<script type='text/javascript'>
			\$\$('a[id^=banner-delete-]').each( function(element) {
				element.observe('click', function(e) {
					var el = e.findElement('a.delete'), msg = el.readAttribute('title') || e107.getModLan('delete_confirm');
					 e.stop();
					if( !e107Helper.confirm(msg) ) return;
					else {
						\$('delete_confirm').value = 1;
						\$('core-banner-list-form').writeAttribute('action', el.href).submit();
					}
				});
			});
		</script>
	";

	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.LAN_MANAGE, $mes->render() . $text);
}

if ($action == "create") {

	if ($sub_action == "edit" && $id) {

		if (!$sql->db_Select("banner", "*", "banner_id = '".$id."' " )) {
			$text .= "<div class='center'>".BNRLAN_15."</div>";
		} else {
			while ($banner_row = $sql->db_Fetch()) {

				$_POST['client_name'] = $banner_row['banner_clientname'];
				$_POST['client_login'] = $banner_row['banner_clientlogin'];
				$_POST['client_password'] = $banner_row['banner_clientpassword'];
				$_POST['banner_image'] = $banner_row['banner_image'];
				$_POST['click_url'] = $banner_row['banner_clickurl'];
				$_POST['impressions_purchased'] = $banner_row['banner_impurchased'];
				$_POST['banner_campaign'] = $banner_row['banner_campaign'];
				$_POST['banner_active'] = $banner_row['banner_active'];

				if ($banner_row['banner_startdate']) {
					$tmp = getdate($banner_row['banner_startdate']);
					$_POST['startmonth'] = $tmp['mon'];
					$_POST['startday'] = $tmp['mday'];
					$_POST['startyear'] = $tmp['year'];
				}
				if ($banner_row['banner_enddate']) {
					$tmp = getdate($banner_row['banner_enddate']);
					$_POST['endmonth'] = $tmp['mon'];
					$_POST['endday'] = $tmp['mday'];
					$_POST['endyear'] = $tmp['year'];
				}

				if (strpos($_POST['banner_campaign'], "^") !== FALSE) {
					$campaignsplit = explode("^", $_POST['banner_campaign']);
					$listtypearray = explode("-", $campaignsplit[1]);
					$listtype = $listtypearray[0];
					$campaign_pages = str_replace("|", "", $listtypearray[1]);
					$_POST['banner_campaign'] = $campaignsplit[0];
				} else {
					$_POST['banner_campaign'] = $banner_row['banner_campaign'];
				}

			}
		}
	}

	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-banner-edit'>
			<legend class='e-hideme'>".($sub_action == "edit" ? LAN_UPDATE : LAN_CREATE)."</legend>
			<table class='table adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".BNRLAN_24."</td>
						<td>
	";

	if (count($campaigns)) {
		$for_var = array();
		$text .= "
							<div class='field-spacer'>
							<select name='banner_campaign_sel' id='banner_campaign_sel' class='tbox'>
								<option>".LAN_SELECT."</option>
		";
		$c = 0;
		while ($campaigns[$c]) {
			if (!isset($for_var[$campaigns[$c]])) {
				$text .= "<option".(($_POST['banner_campaign'] == $campaigns[$c]) ? " selected='selected'" : "").">".$campaigns[$c]."</option>";
				$for_var[$campaigns[$c]] = $campaigns[$c];
			}
			$c++;
		}
		unset($for_var);
		//TODO - ajax add campaign - FIXME currently not working as intended 
		$text .= "
							</select> ".$frm->admin_button('add_new_campaign', BNRLAN_26a, 'other', '', array('other' => "onclick=\"e107Helper.toggle('add-new-campaign-cont', false); \$('banner_campaign_sel').selectedIndex=0; return false;\""))."
							</div>

							<div class='field-spacer e-hideme' id='add-new-campaign-cont'>
								<input class='tbox' type='text' size='30' maxlength='100' name='banner_campaign' value='' />
								<span class='field-help'>".BNRLAN_26."</span>
							</div>
		";
	}
	else
	{
		$text .= "<input class='tbox' type='text' size='30' maxlength='100' name='banner_campaign' value='' />";
	}
	$text .= "
						<span class='field-help'>".BNRLAN_25."</span></td>
					</tr>
					<tr>
					<td>".BNRLAN_27."</td>
					<td>
	";

	if (count($clients)) {
		$text .= "
						<div class='field-spacer'>
						<select name='banner_client_sel' id='banner_client_sel' class='tbox' onchange=\"Banner_Change_Details()\">
							<option>".LAN_SELECT."</option>
		";
		$c = 0;
		while ($clients[$c]) {
			if (!isset($for_var[$clients[$c]])) {
				$text .= "<option".(($_POST['client_name'] == $clients[$c]) ? " selected='selected'" : "").">".$clients[$c]."</option>";
				$for_var[$clients[$c]] = $clients[$c];
			}
			$c++;
		}
		unset($for_var);
		//TODO - ajax add client FIXME - currently not working as intended
		$text .= "
						</select> ".$frm->admin_button('add_new_client', BNRLAN_29a, 'other', '', array('other' => "onclick=\"e107Helper.toggle('add-new-client-cont', false); \$('banner_client_sel').selectedIndex=0; return false;\""))."
						</div>

						<div class='field-spacer e-hideme' id='add-new-client-cont'>
							<input class='tbox' type='text' size='30' maxlength='100' name='client_name' value='' />
							<span class='field-help'>".BNRLAN_29."</span>
						</div>
						<script type='text/javascript'>
							function Banner_Change_Details() {
								var login_field = \$('clientlogin'), password_field = \$('clientpassword'), client_field = \$('banner_client_sel');
								switch(client_field.selectedIndex-1)
								{
		";

		$c = 0;
		$i = 0;
		while ($logins[$c])
		{
			if (!isset($for_var[$logins[$c]])) {
				$text .= "
									case ".$i.":
									login_field.value = \"".$logins[$c]."\";
									password_field.value = \"".$passwords[$c]."\";
									break;";
				$for_var[$logins[$c]] = $logins[$c];
				$i++;
			}
			$c++;
		}
		unset($for_var);

		$text .= "
									default:
									login_field.value = \"\";
									password_field.value = \"\";
									break;
								}
							}
						</script>
		";
	}
	else
	{
		$text .= "
							<input class='tbox' type='text' size='30' maxlength='100' name='client_name' value='' />
							<span class='field-help'>".BNRLAN_29."</span>
		";
	}

	$text .= "
						<span class='field-help'>".BNRLAN_28."</span></td>
					</tr>
					<tr>
						<td>".BNRLAN_30."</td>
						<td>
							<input class='tbox input-text' type='text' size='30' maxlength='20' id='clientlogin' name='client_login' value='".$_POST['client_login']."' />
						</td>
					</tr>
					<tr>
						<td>".BNRLAN_31."</td>
						<td>
							<input class='tbox input-text' type='text' size='30' maxlength='50' id='clientpassword' name='client_password' value='".$_POST['client_password']."' />
						</td>
					</tr>
					<tr>
						<td>".BNRLAN_32."</td>
						<td>
							<div class='field-spacer'>
								<button class='btn button action' type='button' value='no-value' onclick='e107Helper.toggle(\"banner-repo\")'><span>".BNRLAN_43."</span></button> 
							</div>
							<div class='e-hideme' id='banner-repo'>
	";
	$c = 0;
	while ($images[$c])
	{

		$image = $images[$c]['path'].$images[$c]['fname'];

		$fileext1 = substr(strrchr($image, "."), 1);
		$fileext2 = substr(strrchr($image, "."), 0);

		$text .= "
								<div class='field-spacer'>
									".$frm->radio('banner_image', $images[$c]['fname'], (basename($image) == $_POST['banner_image']))."
		";

		if ($fileext1 == 'swf')
		{ //FIXME - swfObject
			$text .= "
									<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width='468' height='60'>
										<param name='movie' value='".e_IMAGE."banners/".$images[$c]['fname']."'>
										<param name='quality' value='high'><param name='SCALE' value='noborder'>
										<embed src='".e_IMAGE."banners/".$images[$c]['fname']."' width='468' height='60' scale='noborder' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash'></embed>
									</object>
			";
		}
		else if($fileext1 == "php" || $fileext1 == "html" || $fileext1 == "js")
		{
			$text .= $frm->label(BNRLAN_46.": ".$images[$c]['fname'],'banner_image', $images[$c]['fname']);
		}
		else
		{
			$text .= $frm->label("<img src='$image' alt='' />", 'banner_image', $images[$c]['fname']);
		}
		$text .= "
								</div>
		";

		$c++;
	}
	$text .= "
							</div>
						</td>
					</tr>
					<tr>
						<td>".BNRLAN_33."</td>
						<td>
							<input class='tbox input-text' type='text' size='50' maxlength='150' name='click_url' value='".$_POST['click_url']."' />
						</td>
					</tr>
					<tr>
						<td>".BNRLAN_34."</td>
						<td>
							<input class='tbox input-text' type='text' size='10' maxlength='10' name='impressions_purchased' value='".$_POST['impressions_purchased']."' />
							<span class='field-help'>".BNRLAN_38."</span>
						</td>
					</tr>
					<tr>
					<td>".BNRLAN_36."</td>
					<td>
						<select name='startday' class='tbox'>
							<option value='0'>&nbsp;</option>
	";

	for($a = 1; $a <= 31; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['startday']) ? " selected='selected'" : "").">".$a."</option>";
	}

	$text .= "
						</select>
						<select name='startmonth' class='tbox'>
							<option value='0'>&nbsp;</option>
	";
	for($a = 1; $a <= 12; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['startmonth']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<select name='startyear' class='tbox'>
							<option value='0'>&nbsp;</option>
	";
	for($a = 2003; $a <= 2010; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['startyear']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<span class='field-help'>".BNRLAN_38."</span>
					</td>
				</tr>
				<tr>
					<td>".BNRLAN_37."</td>
					<td>
						<select name='endday' class='tbox'>
							<option value='0'>&nbsp;</option>
	";
	for($a = 1; $a <= 31; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['endday']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<select name='endmonth' class='tbox'>
							<option value='0'>&nbsp;</option>";
	for($a = 1; $a <= 12; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['endmonth']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<select name='endyear' class='tbox'>
							<option value='0}'>&nbsp;</option>
	";
	for($a = 2003; $a <= 2010; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['endyear']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<span class='field-help'>".BNRLAN_38."</span>
					</td>
				</tr>
				<tr>
					<td>".LAN_VISIBILITY."</td>
					<td>
						".$e_userclass->uc_dropdown('banner_class', $_POST['banner_active'], 'public,member,guest,admin,classes,nobody,classes')."
					</td>
				</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>

	";
	if 	($sub_action == "edit" && $id) 
	{
		$text .= $frm->admin_button('updatebanner','no-value','create',LAN_UPDATE);
		$text .= "<input type='hidden' name='eid' value='".$id."' />";
	} 
	else 
	{
		$text .= $frm->admin_button('createbanner','no-value','create',LAN_CREATE);
	}

	$text .= "
			</div>
		</fieldset>
	</form>
		";

	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.($sub_action == "edit" ? LAN_UPDATE : LAN_CREATE), $text);

}



if ($action == "menu")
{
  $in_catname = array();		// Notice removal
  $all_catname = array();

	$array_cat_in = explode("|", $menu_pref['banner_campaign']);
	if (!$menu_pref['banner_caption'])
	{
		$menu_pref['banner_caption'] = BANNER_MENU_L1;
	}

	$category_total = $sql -> db_Select("banner", "DISTINCT(banner_campaign) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");
	while ($banner_row = $sql -> db_Fetch())
	{
		$all_catname[] = $banner_row['banner_campaign'];

		if (in_array($banner_row['banner_campaign'], $array_cat_in))
		{
			$in_catname[] = $banner_row['banner_campaign'];
		}
	}


	$text = "
		<form method='post' action='".e_SELF."?menu' id='menu_conf_form'>
			<fieldset id='core-banner-menu'>
				<legend class='e-hideme'>".BANNER_MENU_L5."</legend>
				<table class='table adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td>".BANNER_MENU_L3.": </td>
							<td>".$frm->text('banner_caption', $menu_pref['banner_caption'])."</td>
						</tr>
						<tr>
							<td>".BANNER_MENU_L6."</td>
							<td>
	";

	if($all_catname)
	{
		foreach($all_catname as $name)
		{
			//$text .= "<option value='{$name}'>{$name}</option>";
			$text .= "
									<div class='field-spacer'>
										".$frm->checkbox('multiaction_cat_active[]', $name, in_array($name, $in_catname)).$frm->label($name, 'multiaction_cat_active[]', $name)."
									</div>
			";
		}
		$text .= "
									<div class='field-spacer'>
										".$frm->admin_button('check_all', LAN_CHECKALL, 'other')."
										".$frm->admin_button('uncheck_all', LAN_UNCHECKALL, 'other')."
									</div>
		";
	}
	else
	{
		$text .= BNRLAN_67;
	}
	$text .= "

							</td>
						</tr>
						<tr>
							<td>".BANNER_MENU_L19."</td>
							<td>".$frm->text('banner_amount', $menu_pref['banner_amount'], 2, array ('class' => 'tbox input-text'))."<span class='field-help'>".BANNER_MENU_L20."</span></td>
						</tr>
						<tr>
							<td>".BANNER_MENU_L10."</td>
							<td>
								<select class='tbox select' id='banner_rendertype' name='banner_rendertype'>
									".$frm->option(BANNER_MENU_L11, 0, (empty($menu_pref['banner_rendertype'])))."
									".$frm->option("1 - ".BANNER_MENU_L12, 1, ($menu_pref['banner_rendertype'] == "1"))."
									".$frm->option("2 - ".BANNER_MENU_L13, 2, ($menu_pref['banner_rendertype'] == "2"))."
									".$frm->option("3 - ".BANNER_MENU_L14, 3, ($menu_pref['banner_rendertype'] == "3"))."
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>".
					$frm->admin_button('update_menu','no-value','update', LAN_UPDATE)."
				</div>
			</fieldset>
		</form>
	";

	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.BNRLAN_68, $mes->render() . $text);
}


function admin_banner_adminmenu() 
{

	$qry = e_QUERY;
	$act = vartrue($qry,'main');
	
	$var['main']['text'] = BNRLAN_58;
	$var['main']['link'] = e_SELF;

	$var['create']['text'] = BNRLAN_59;
	$var['create']['link'] = e_SELF."?create";

	$var['menu']['text'] = BNRLAN_61;
	$var['menu']['link'] = e_SELF."?menu";

	e107::getNav()->admin(LAN_PLUGIN_BANNER_NAME, $act, $var);
}

require_once(e_ADMIN."footer.php");


// Log event to admin log
function banners_adminlog($msg_num='00', $woffle='')
{
  global $admin_log;
  $pref = e107::getPref();

//  if (!varset($pref['admin_log_log']['admin_banners'],0)) return;
  $admin_log->log_event('BANNER_'.$msg_num,$woffle,E_LOG_INFORMATIVE,'');
}


?>