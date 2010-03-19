<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqView.php,v $
 * @version $Revision: 1.28 $
 * @modified $Date: 2010/03/19 15:04:09 $ by $Author: asimon83 $
 * @author Martin Havlat
 * 
 * Screen to view content of requirement.
 *	@internal revision
 *  20100319 - asimon - BUGID 1748 - implemented display of req relations
 *	20091217 - franciscom - display type and expected coverage
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('attachments.inc.php');
require_once('requirements.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args();
$gui = initialize_gui($db,$args);
$smarty = new TLSmarty();

/* contribution BUGID 2999, show permanent link */
$tproject_mgr = new testproject($db);
$prefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);

$gui->direct_link = $_SESSION['basehref'] . 'linkto.php?tprojectPrefix=' . 
                    urlencode($prefix) . '&item=req&id=' . urlencode($gui->req['req_doc_id']);
		
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqViewVersions.tpl');

/**
 *
 */
function init_args()
{
	$iParams = array("requirement_id" => array(tlInputParameter::INT_N),
			         "showReqSpecTitle" => array(tlInputParameter::INT_N),
					 // BUGID 1748
			         "relation_add_result_msg" => array(tlInputParameter::STRING_N));	
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);
	
    $args->req_id = $args->requirement_id;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
    $user = $_SESSION['currentUser'];
	$args->userID = $user->dbID;
	
    return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,$argsObj)
{
    $tproject_mgr = new testproject($dbHandler);
    $req_mgr = new requirement_mgr($dbHandler);
    $commandMgr = new reqCommands($db);

    $gui = $commandMgr->initGuiBean();
    $gui->req_cfg = config_get('req_cfg');
    $gui->tproject_name = $argsObj->tproject_name;

    $gui->grants = new stdClass();
    $gui->grants->req_mgmt = has_rights($db,"mgt_modify_req");
    
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->glueChar = config_get('testcase_cfg')->glue_character;
    $gui->pieceSep = config_get('gui_title_separator_1');
    
    $gui->req_id = $argsObj->req_id;
    $gui->req_versions = $req_mgr->get_by_id($gui->req_id);
    $gui->req = current($gui->req_versions);
    $gui->req_coverage = $req_mgr->get_coverage($gui->req_id);
    
    // This seems weird but is done to adapt template than can display multiple
    // requirements. This logic has been borrowed from test case versions management
    $gui->current_version[] = array($gui->req);
 	$gui->other_versions[] = count($gui->req_versions) > 1 ? array_slice($gui->req_versions,1) : null;
	
    $gui->show_title = false;
    $gui->main_descr = lang_get('req') . $gui->pieceSep .  $gui->req['title'];
    
    $gui->showReqSpecTitle = $argsObj->showReqSpecTitle;
    if($gui->showReqSpecTitle)
    {
        $gui->parent_descr = lang_get('req_spec_short') . $gui->pieceSep . $gui->req['req_spec_title'];
    }
    $gui->cfields = array();
    $gui->cfields[] = $req_mgr->html_table_of_custom_field_values($gui->req_id,$argsObj->tproject_id);
   	$gui->attachments[$gui->req_id] = getAttachmentInfosFrom($req_mgr,$gui->req_id);
    
    $gui->attachmentTableName = $req_mgr->getAttachmentTableName();
    $gui->reqStatus = init_labels(config_get('req_status'));
    $gui->reqTypeDomain = init_labels($gui->req_cfg->type_labels);

    // added req relations for BUGID 1748
    $gui->req_relations = FALSE;
    $gui->req_relation_select = FALSE;
    $gui->testproject_select = FALSE;
    $gui->req_add_result_msg = isset($argsObj->relation_add_result_msg) ? 
    							$argsObj->relation_add_result_msg : "";
    
    if ($gui->req_cfg->relations->enable) {
    	$gui->req_relations = $req_mgr->get_relations($gui->req_id);
    	$gui->req_relation_select = initRelationTypeSelect($db, $argsObj, $req_mgr);
    	if ($gui->req_cfg->relations->relations_between_different_testprojects) {
    		$gui->testproject_select = initTestprojectSelect($db, $argsObj, $tproject_mgr);
    	}
    }
    
    return $gui;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}


/**
 * Initializes the select field for the localized requirement relation types.
 * 
 * @param resource $db reference to database handler
 * @param array $args reference to user input data
 * @return array $htmlSelect info needed to create select box on template
 */
function initRelationTypeSelect(&$db, &$args, &$reqMgr) {
	
	$htmlSelect = array('items' => null, 'selected' => null);
	$labels = $reqMgr->get_all_relation_labels();
	$htmlSelect['items'] = array();
	
	foreach ($labels as $key => $lab) {
		$htmlSelect['items'][$key . "_source"] = $lab['source'];
		if ($lab['source'] != $lab['destination']) {
			$htmlSelect['items'][$key . "_destination"] = $lab['destination'];
		}
	}
	
	// preselect last key in array, with standard configuration that's "related to"
	// next line seems weird, but is done because PHP complains about passing function return value as reference
	$items = $htmlSelect['items'];
	$htmlSelect['selected'] = array_pop(array_keys($items));
	
	return $htmlSelect;
}

/**
 * Initializes the select field for the testprojects.
 * 
 * @param resource $db reference to database handler
 * @param array $args reference to user input data
 * 
 * @return array $htmlSelect array with info, needed to create testproject select box on template
 */
function initTestprojectSelect(&$db, &$args, &$tprojectMgr) {
	
	$testprojects = $tprojectMgr->get_accessible_for_user($args->userID, 'map', config_get('gui')->tprojects_combo_order_by);	
	$htmlSelect = array('items' => $testprojects, 'selected' => $args->tproject_id);
	
	return $htmlSelect;
}

?>
