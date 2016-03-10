<?php
require_once('./Modules/DataCollection/classes/Fields/Plugin/class.ilDclPluginRecordFieldModel.php');
/**
 * Class ilPHBernUserSelectorRecordFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernUserSelectorRecordFieldModel extends ilDclPluginRecordFieldModel
{
	/**
	 * Serialize data before storing to db
	 * @param $value mixed
	 *
	 * @return mixed
	 */
	public function serializeData($value) {
		global $ilUser;
		if(is_array($value)) {
			if ($this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT)) {
				foreach ($value as $key => $input) {
					$value[$key] = $ilUser->getUserIdByEmail($input);
				}
			}

			$value = json_encode($value);
		}
		return $value;
	}

	/**
	 * Deserialize data before applying to field
	 * @param $value mixed
	 *
	 * @return mixed
	 */
	public function deserializeData($value) {
		global $ilUser;
		$deserialize = json_decode($value, true);
		if($deserialize != false) {
			if ($this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT)) {
				foreach ($deserialize as $key => $input) {
					$user = new ilObjUser($input);
					$deserialize[$key] = $user->getEmail();
				}
			}

			return $deserialize;
		}
		return $value;
	}
}