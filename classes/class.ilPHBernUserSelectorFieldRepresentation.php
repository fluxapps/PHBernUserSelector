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


	/**
	 * ilPHBernUserSelectorFieldRepresentation constructor.
	 *
	 * @param ilDclBaseFieldModel $field
	 */
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

		// setup autocomplete
		$ref_id = isset($_GET['ref_id'])? $_GET['ref_id'] : 0;
		$this->ctrl->setParameterByClass('ildclrecordlistgui', 'ref_id', $ref_id);
		$this->ctrl->setParameterByClass('ildclrecordlistgui', 'search', 1);
		$input->setDataSource($this->ctrl->getLinkTargetByClass(array('ildclrecordlistgui'), "listRecords", "", true));
		$this->ctrl->clearParametersByClass('ildclrecordlistgui');

		$input->setSize(20);
		$input->setSubmitFormOnEnter(true);

		$this->setupFilterInputField($input);
		$table->addFilterItem($input);
		$input->readFromSession();

		// handle ajax requests
		$this->handleUserAutoComplete();

		$value = $input->getValue();
		return $ilUser->getUserIdByLogin($value);
	}

	/**
	 * Show auto complete results
	 */
	protected function handleUserAutoComplete()
	{
		$ref_id = isset($_GET['ref_id'])? $_GET['ref_id'] : 0;
		if(ilObjDataCollectionAccess::hasReadAccess($ref_id) && isset($_GET['search']) == 1) {
			include_once './Services/User/classes/class.ilUserAutoComplete.php';
			$auto = new ilUserAutoComplete();
			$auto->setSearchFields(array('login','firstname','lastname','email'));
			$auto->enableFieldSearchableCheck(false);
			//$auto->setMoreLinkAvailable(true);

			if(($_REQUEST['fetchall']))
			{
				$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
			}

			echo $auto->getList($_REQUEST['term']);
			exit();
		}
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
		if (($this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_INPUT_TYPE) == ilPHBernUserSelectorFieldModel::INPUT_TYPE_SELECT)
				&& $this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP)) {
			$input = new ilSelectInputGUI($this->field->getTitle(), 'field_' . $this->field->getId());
			$input->setMulti(true);
			$users = array();
			$limit_to_group = $this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP);
			if($limit_to_group) {
				$limit_to_group = (is_array($limit_to_group))? $limit_to_group : array($limit_to_group);
				foreach($limit_to_group as $group) {
					$users += $rbacreview->assignedUsers($group);
				}
				$users = array_unique($users);
			}

			foreach($users as $user_id) {
				$user = new ilObjUser($user_id);
				$options[$user->getLogin()] = $user->getLogin() . " [{$user->getFullname()}]";
			}
			asort($options);
			$zero_option = array(''=>$this->lng->txt('dcl_please_select'));
			$options = $zero_option + $options;
			$input->setOptions($options);
		} else {
			// User name, login, email filter
			include_once("./Services/Form/classes/class.ilTextInputGUI.php");
			$input = new ilTextInputGUI($this->field->getTitle(), 'field_' . $this->field->getId());
			$input->setMulti(true);
			$this->ctrl->setParameterByClass('ilPHBernUserSelectorPlugin', 'field_id', $this->getField()->getId());
			$input->setDataSource($this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilPHBernUserSelectorPlugin'),
				"addUserAutoComplete", "", true));
			$input->setSize(20);
		}

		$this->setupInputField($input, $this->field);

		return $input;
	}


	/**
	 * @inheritDoc
	 */
	protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);

		$prop_input_type= new ilRadioGroupInputGUI($this->pl->txt('user_input_type'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_USER_INPUT_TYPE));
		$radio_opt = new ilRadioOption($this->pl->txt('input_type_text'), ilPHBernUserSelectorFieldModel::INPUT_TYPE_TEXT);
		$prop_input_type->addOption($radio_opt);
		$radio_opt = new ilRadioOption($this->pl->txt('input_type_select'), ilPHBernUserSelectorFieldModel::INPUT_TYPE_SELECT);
		$prop_input_type->addOption($radio_opt);
		$prop_input_type->setValue('text_input');
		$opt->addSubItem($prop_input_type);

		$prop_email_input = new ilCheckboxInputGUI($this->pl->txt('user_email_input'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_USER_EMAIL_INPUT));
		$opt->addSubItem($prop_email_input);

		/* Dropdown with roles
		$prop_role_limit = new ilSelectInputGUI($this->pl->txt('limit_to_role'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP));
		$global_roles = $this->field->getRoles(ilRbacReview::FILTER_ALL);
		$prop_role_limit->setOptions($global_roles);*/

		$prop_role_limit = new ilTextInputGUI($this->pl->txt('limit_to_role'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_USER_LIMIT_GROUP));
		$prop_role_limit->setMulti(true);
		$opt->addSubItem($prop_role_limit);

		return $opt;
	}


}