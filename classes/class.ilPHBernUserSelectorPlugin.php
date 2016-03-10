<?php

require_once('./Modules/DataCollection/classes/Fields/Plugin/class.ilDclFieldTypePlugin.php');

/**
 * Class ilPHBernUserSelectorPlugin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
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
}