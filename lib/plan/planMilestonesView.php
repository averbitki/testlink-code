<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: planMilestonesView.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2009/06/10 19:36:00 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("testplan.class.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($db,$args);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";

	$args->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
	$args->tplan_name = isset($_SESSION['testplanName']) ? $_SESSION['testplanName'] : "";

	return $args;
}

/*
  function: initialize_gui

  args : -

  returns:

*/
function initialize_gui(&$dbHandler,&$argsObj)
{
    $manager = new milestone_mgr($dbHandler);
    $gui = new stdClass();
    
    $gui->user_feedback = null;
    $gui->main_descr = lang_get('title_milestones') . $argsObj->tplan_name;
    $gui->action_descr = null;
    $gui->tplan_name = $argsObj->tplan_name;
    $gui->tplan_id = $argsObj->tplan_id;
	$gui->items = $manager->get_all_by_testplan($argsObj->tplan_id);
	
	$gui->grants = new stdClass();
    $gui->grants->milestone_mgmt = has_rights($dbHandler,"testplan_planning");
	$gui->grants->mgt_view_events = has_rights($dbHandler,"mgt_view_events");
	
	return $gui;
}


function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,"testplan_planning"));
}
?>