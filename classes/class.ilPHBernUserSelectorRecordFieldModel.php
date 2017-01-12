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
		if(is_array($value)) {
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
		return json_decode($value, true);
	}

	/**
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function parseExportValue($value) {
//		if (!$this->field->hasProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT) && is_array($value)) {
//			foreach ($value as $key => $input) {
//				$user = new ilObjUser($input);
//				$value[$key] = $user->getEmail();
//			}
//		}
		if(is_array($value))
			return implode(", ", $value);
		else
			return $value;
	}


	/**
	 * @inheritDoc
	 */
	public function getValueFromExcel($excel, $row, $col) {
		$value = $excel->getCell($row, $col);

		$split = explode(', ', $value);
//
//		if (!$this->field->hasProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT) && is_array($split)) {
//			foreach ($split as $key => $input) {
//				$user_id = ilObjUser::getUserIdByEmail($input);
//				if($user_id > 0) {
//					$split[$key] = $user_id;
//				} else {
//					unset($split[$key]);
//				}
//
//			}
//		}
		return $split;
	}
}