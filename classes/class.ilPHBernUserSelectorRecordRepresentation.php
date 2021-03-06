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
			if (ilObjUser::_loginExists($user) || ilObjUser::_exists($user)) {
				if (!is_numeric($user)) {
					$user = ilObjUser::_lookupId($user);
				}
				$user = new ilObjUser($user);
				$value .= ($as_email ? $user->getEmail() : ($user->getFirstname() . ' ' . $user->getLastname())) .", ";
			} else {
				$value .= $user . ', ';
			}
		}

		$value = substr($value, 0, -2);

		return $value;
	}
}