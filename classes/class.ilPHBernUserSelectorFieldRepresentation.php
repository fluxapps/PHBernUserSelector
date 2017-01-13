<?php
require_once('./Modules/DataCollection/classes/Fields/Plugin/class.ilDclPluginFieldRepresentation.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/Helper/class.srDclContentImporterMultiLineInputGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/class.ilDclContentImporterPlugin.php');

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
		$input->setDataSource($this->ctrl->getLinkTargetByClass(array('ilobjdatacollectiongui', 'ildclrecordlistgui'), "listRecords", "", true));
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
		global $rbacreview, $tpl;
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
			include_once("./Services/Form/classes/class.ilTextInputGUI.php");
			$input = new ilTextInputGUI($this->field->getTitle(), 'field_' . $this->field->getId());
			$input->setMulti(true);
			$this->ctrl->setParameterByClass('ilPHBernUserSelectorPlugin', 'field_id', $this->getField()->getId());
			$input->setDataSource($this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilPHBernUserSelectorPlugin'),
				"addUserAutoComplete", "", true));
			$input->setSize(20);
			$input->setInfo($this->pl->txt('info_autocomplete'));
		}

		$this->setupInputField($input, $this->field);

		if($this->field->hasProperty(ilPHBernUserSelectorFieldModel::PROP_HIDE_ON)) {
			$field_value_pairs = $this->field->getProperty(ilPHBernUserSelectorFieldModel::PROP_HIDE_ON);
			$or = '';
			$condition = '';
			foreach ($field_value_pairs as $array) {
				$field_id = $array[ilPHBernUserSelectorFieldModel::FIELD];
				$field_value = $array[ilPHBernUserSelectorFieldModel::VALUE];

				$condition .= $or . '$("#field_'.$field_id.'").val() == "'.$field_value.'"';
				$or = ' || ';
				if(isset($_POST['field_'.$field_id]) && $_POST['field_'.$field_id] == $field_value) {
					$input->setRequired(false);
				}
			}

			if ($condition) {
				$script = '$("#field_'.$field_id.'")
				.change(function () {
					if('.$condition.') {
						$("#field_'.$this->field->getId().'").val("");
						$("#il_prop_cont_field_'.$this->field->getId().'").hide();
					} else {
						$("#il_prop_cont_field_'.$this->field->getId().'").show();
					}
				})
				.change();';

				$tpl->addOnLoadCode($script);

			}

		}

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

		$multiinput = new srDclContentImporterMultiLineInputGUI($this->pl->txt('hide_on_field'), $this->getPropertyInputFieldId(ilPHBernUserSelectorFieldModel::PROP_HIDE_ON));
		$multiinput->setInfo("Field & Field-Value");
		$multiinput->setTemplateDir(ilDclContentImporterPlugin::getInstance()->getDirectory());

		$input = new ilSelectInputGUI($this->pl->txt('hide_on_field'),ilPHBernUserSelectorFieldModel::FIELD);

		$fields = ilDclCache::getTableCache($this->field->getTableId())->getFields();
		$options = array(''=>'');
		foreach($fields as $field) {
			$options[$field->getId()] = $field->getTitle();
		}
		$input->setOptions($options);
		$multiinput->addInput($input);

		$input = new ilTextInputGUI('Datacollection Ref-ID', ilPHBernUserSelectorFieldModel::VALUE);
		$multiinput->addInput($input);

		$opt->addSubItem($multiinput);

		return $opt;
	}


}