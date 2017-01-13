<?php
require_once("./Modules/DataCollection/classes/Fields/Text/class.ilDclTextFieldModel.php");
require_once("./Modules/DataCollection/classes/Helpers/class.ilDclRecordQueryObject.php");

/**
 * Class ilPHBernUserSelectorFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernUserSelectorFieldModel extends ilDclTextFieldModel {

	const PROP_USER_INPUT_TYPE = 'phber_uselect_input_type';
	const PROP_USER_EMAIL_INPUT = "phbe_uselect_email_input";
	const PROP_USER_LIMIT_GROUP = "phbe_uselect_user_group";

	const PROP_HIDE_ON = "phbe_cdate_hide_on";
	const FIELD = "f";
	const VALUE = "v";

	const INPUT_TYPE_TEXT = 0;
	const INPUT_TYPE_SELECT = 1;

	/**
	 * @inheritDoc
	 */
	public function __construct($a_id = 0) {
		parent::__construct($a_id);

		$this->setStorageLocationOverride(1);
	}


	/**
	 * @inheritDoc
	 */
	public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = NULL) {
		global $ilDB;

		$join_str = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = " . $ilDB->quote($this->getId(), 'integer') . ") ";
		$join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value LIKE " . $ilDB->quote("%$filter_value%", 'text') . ") ";

		$sql_obj = new ilDclRecordQueryObject();
		$sql_obj->setJoinStatement($join_str);

		return $sql_obj;
	}


	/**
	 * @inheritDoc
	 */
	public function getValidFieldProperties() {
		return array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME, self::PROP_USER_EMAIL_INPUT, self::PROP_USER_LIMIT_GROUP, self::PROP_USER_INPUT_TYPE, self::PROP_HIDE_ON);
	}


	/**
	 * Check validity
	 * @param      $value
	 * @param null $record_id
	 *
	 * @return bool
	 * @throws ilDclInputException
	 */
	public function checkValidity($value, $record_id = NULL) {
		global $ilUser;

		if(!is_array($value)) {
			throw new ilDclInputException(ilDclInputException::TYPE_EXCEPTION);
		}


		if(!$ilUser->userExists(array_filter(ilObjUser::_lookupId($value)))) {
			throw new ilDclInputException(ilDclInputException::CUSTOM_MESSAGE, ilPHBernUserSelectorPlugin::getInstance()->txt('not_valid_user'));
		}

		return true;
	}



	/**
	 * Return roles
	 * @param int  $filter
	 * @param bool $with_text
	 *
	 * @return array
	 */
	public function getRoles($filter, $with_text = true) {
		global $rbacreview;
		$opt = array();
		$role_ids = array();
		foreach ($rbacreview->getRolesByFilter($filter) as $role) {
			$opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
			$role_ids[] = $role['obj_id'];
		}
		if ($with_text) {
			return $opt;
		} else {
			return $role_ids;
		}
	}
}