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

	/**
	 * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function parseExportValue($value) {
		if (!$this->field->hasProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT) && is_array($value)) {
			foreach ($value as $key => $input) {
				$user = new ilObjUser($input);
				$value[$key] = $user->getFullname();
			}
		}
		if(is_array($value))
			return implode(", ", $value);
		else
			return $value;
	}
}