<?php
/**
 * @copyright	Copyright © 2016 - All rights reserved.
 * @license		GNU General Public License v2.0
 * @generator	http://xdsoft/joomla-module-generator/
 */
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
/* Available fields:"image_in_the_spot","custom_image", */
// Include assets
$doc->addStyleSheet(JURI::root()."modules/mod_pm_article_image_spot/assets/css/style.css");
$doc->addScript(JURI::root()."modules/mod_pm_article_image_spot/assets/js/script.js");
// $width 			= $params->get("width");

/**
	$db = JFactory::getDBO();
	$db->setQuery("SELECT * FROM #__mod_pm_article_image_spot where del=0 and module_id=".$module->id);
	$objects = $db->loadAssocList();
*/
require JModuleHelper::getLayoutPath('mod_pm_article_image_spot', $params->get('layout', 'default'));