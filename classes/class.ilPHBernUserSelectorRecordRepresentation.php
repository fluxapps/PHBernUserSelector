<?php
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordRepresentation.php');
/**
 * Class ilPHBernUserSelectorRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernUserSelectorRecordRepresentation extends ilDclBaseRecordRepresentation {

	/**
	 * Outputs html of a certain field
	 * @param mixed $value
	 * @param bool|true $link
	 *
	 * @return string
	 */
	public function getHTML($link = true) {
		$as_email = $this->record_field->getField()->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT);

		$value = "";
		$users = is_array($this->record_field->getValue())? $this->record_field->getValue() : array($this->record_field->getValue());
		foreach($users as $user) {
			$user = new ilObjUser(ilObjUser::_lookupId($user));
			$value .= ($as_email ? $user->getEmail() : $user->getFullname()) .", ";
		}

		$value = substr($value, 0, -2);

		return $value;
	}
}