<?php
/**
 * 	TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * 	@filesource	listTestCases.php
 * 	@package 	  TestLink
 * 	@author 	  Martin Havlat
 * 	@copyright 	2007-2011, TestLink community 
 * 	@link 		  http://www.teamst.org/index.php
 *
 *
 * 	Generates tree menu with test specification.
 *   It builds the javascript tree that allows the user to choose testsuite or testcase.
 *
 *	@internal revisions
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$control = new tlTestCaseFilterControl($db, 'edit_mode');

$gui = initializeGui($db, $control);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->assign('args', $control->get_argument_string());
$smarty->assign('menuUrl', $gui->menuUrl);

$smarty->display($templateCfg->template_dir . 'tcTree.tpl');


/**
 * Initialize object with information for graphical user interface.
 * 
 * @param tlTestCaseFilterControl $control
 * @return stdClass $gui
 */
function initializeGui(&$dbHandler, &$control) 
{
	$gui = new stdClass();
	$gui->feature = $control->args->feature;
	$gui->treeHeader = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');

	$lblkey = (config_get('testcase_reorder_by') == 'NAME') ? '_alpha' : '_externalid';
	$gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

	$feature_path = array('edit_tc' => "lib/testcases/archiveData.php",
	                      'keywordsAssign' => "lib/keywords/keywordsAssign.php",
	                      'assignReqs' => "lib/requirements/reqTcAssign.php");

	$gui->tree_drag_and_drop_enabled = array('edit_tc' => 
	                                          ($control->user->HasRight($dbHandler, "mgt_modify_tc") == 'yes'),
	                                         'keywordsAssign' => false,
	                                         'assignReqs' => false);

	$gui->menuUrl = $feature_path[$gui->feature];
	return $gui;
}
?>