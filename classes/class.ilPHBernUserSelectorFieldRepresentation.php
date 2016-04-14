<?php
require_once('./Modules/DataCollection/classes/Fields/Plugin/class.ilDclPluginFieldRepresentation.php');

/**
 * Class ilPHBernUserSelectorFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernUserSelectorFieldRepresentation extends ilDclPluginFieldRepresentation {

	protected $pl;

	public function __construct(ilDclBaseFieldModel $field) {
		parent::__construct($field);

		$this->pl = ilPHBernUserSelectorPlugin::getInstance();
	}


	/**
	 * Add filters to gui
	 * @param ilTable2GUI $table
	 *
	 * @return int
	 */
	public function addFilterInputFieldToTable(ilTable2GUI $table) {
		global $ilUser;
		$input = new ilTextInputGUI($this->lng->txt("login")."/".$this->lng->txt("email")."/".$this->lng->txt("name"), "filter_".$this->field->getId());
		//$input->setDataSource($this->ctrl->getLinkTargetByClass('ilObjUserFolderGUI', "addUserAutoComplete", "", true));
		$input->setDataSource('ilias.php?ref_id=7&admin_mode=settings&cmd=addUserAutoComplete&cmdClass=ilobjuserfoldergui&cmdNode=d1:wj&baseClass=ilAdministrationGUI&cmdMode=asynch');
		$input->setSize(20);
		$input->setSubmitFormOnEnter(true);

		$this->setupFilterInputField($input);
		$table->addFilterItem($input);
		$input->readFromSession();

		$value = $input->getValue();
		return $ilUser->getUserIdByLogin($value);
	}


	/**
	 * Return field inputs
	 *
	 * @param ilPropertyFormGUI $form
	 * @param int               $record_id
	 *
	 * @return ilDclTextInputGUI|ilSelectInputGUI
	 * @throws ilFormException
	 */
	public function getInputField(ilPropertyFormGUI $form, $record_id = 0) {
		global $rbacreview;
		//Property Selector-type
		if ($this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT)) {
			$input = new ilDclTextInputGUI($this->field->getTitle(), 'field_' . $this->field->getId());
			$input->setMulti(true);
			$input->setInfo(ilPHBernUserSelectorPlugin::getInstance()->txt("user_selector_hint"));
		} else {
			$input = new ilSelectInputGUI($this->field->getTitle(), 'field_' . $this->field->getId());
			$input->setMulti(true);
			$users = array();
			if($this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP)) {
				$users = $rbacreview->assignedUsers($this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP));
			}

			$options = array(''=>$this->lng->txt('dcl_please_select'));
			foreach($users as $user_id) {
				$user = new ilObjUser($user_id);
				$options[$user_id] = $user->getFullname();
			}
			$input->setOptions($options);
		}

		$this->setupInputField($input, $this->field);

		return $input;
	}


	/**
	 * @inheritDoc
	 */
	protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);

		$pl =

		$prop_email_input = new ilCheckboxInputGUI($this->pl->txt('user_email_input'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT));
		$opt->addSubItem($prop_email_input);

		$prop_role_limit = new ilSelectInputGUI($this->pl->txt('limit_to_role'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP));
		$global_roles = $this->field->getRoles(ilRbacReview::FILTER_ALL);
		$prop_role_limit->setOptions($global_roles);
		$opt->addSubItem($prop_role_limit);

		return $opt;
	}


}