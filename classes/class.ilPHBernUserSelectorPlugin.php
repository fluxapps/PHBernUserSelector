<?php

require_once('./Modules/DataCollection/classes/Fields/Plugin/class.ilDclFieldTypePlugin.php');

/**
 * Class ilPHBernUserSelectorPlugin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 *
 * @ilCtrl_isCalledBy ilPHBernUserSelectorPlugin: ilUIPluginRouterGUI
 */
class ilPHBernUserSelectorPlugin extends ilDclFieldTypePlugin {

	/**
	 * Get Plugin Name. Must be same as in class name il<Name>Plugin
	 * and must correspond to plugins subdirectory name.
	 *
	 * Must be overwritten in plugin class of plugin
	 *
	 * @return    string    Plugin Name
	 */
	function getPluginName() {
		return "PHBernUserSelector";
	}

	public function executeCommand() {
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd();
		switch($cmd) {
			default:
				$this->{$cmd}();
				break;
		}
	}

	public function addUserAutoComplete() {
		require_once 'Modules/DataCollection/classes/Helpers/class.ilDclCache.php';
		$field = ilDclCache::getFieldCache($_GET['field_id']);
		$limit_to_group = $field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP);
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname','email'));
		$auto->enableFieldSearchableCheck(false);
		$auto->setMoreLinkAvailable(true);

		if(($_REQUEST['fetchall']))
		{
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}

		$list = $auto->getList($_REQUEST['term']);
		if ($limit_to_group) {
			global $rbacreview;
			$limit_to_group = (is_array($limit_to_group))? $limit_to_group : array($limit_to_group);
			global $ilLog;
			$array = json_decode($list, true);
			$ilLog->write(print_r($array, true));
			foreach($array['items'] as $key => $item) {
				if (!$rbacreview->isAssignedToAtLeastOneGivenRole($item['id'], $limit_to_group)) {
					unset($array['items'][$key]);
				}
			}
			$list = json_encode($array);
		}
		echo $list;
		exit();
	}
}