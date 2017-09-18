<?php

require_once 'volunteeractivities.civix.php';
use CRM_Volunteeractivities_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function volunteeractivities_civicrm_config(&$config) {
  _volunteeractivities_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function volunteeractivities_civicrm_xmlMenu(&$files) {
  _volunteeractivities_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function volunteeractivities_civicrm_install() {
  _volunteeractivities_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function volunteeractivities_civicrm_postInstall() {
  _volunteeractivities_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function volunteeractivities_civicrm_uninstall() {
  _volunteeractivities_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function volunteeractivities_civicrm_enable() {
  _volunteeractivities_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function volunteeractivities_civicrm_disable() {
  _volunteeractivities_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function volunteeractivities_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _volunteeractivities_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function volunteeractivities_civicrm_managed(&$entities) {
  _volunteeractivities_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function volunteeractivities_civicrm_caseTypes(&$caseTypes) {
  _volunteeractivities_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function volunteeractivities_civicrm_angularModules(&$angularModules) {
  _volunteeractivities_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function volunteeractivities_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _volunteeractivities_civix_civicrm_alterSettingsFolders($metaDataFolders);
}


function volunteeractivities_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == "CRM_Admin_Page_Extensions" || $pageName == "CRM_Contact_Page_View_Summary") {
    _volunteerActivities_prereqCheck();
  }
}

function _volunteerActivities_prereqCheck() {
  $unmet = CRM_Volunteeractivities_Upgrader::checkExtensionDependencies();
  CRM_Volunteeractivities_Upgrader::displayDependencyErrors($unmet);
}


/**
 * Implements volunteeractivities_civicrm_tabset().
 * Implementing this to add a custom tab to view Volunteer Activities of a contact.
 */
function volunteeractivities_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName == "civicrm/contact/view") {
    $tab[count($tabs)] = array(
      'title' => ts('Volunteer Activities'),
      'id'  => 'volunteeractivity',
      'url' => CRM_Utils_System::url('civicrm/contact/view/volunteeractivities', 'reset=1&cid=' . $context['contact_id']),
      'class' => 'livePage',
    );
    $tabs = array_merge(array_slice($tabs, 0, 5), $tab, array_slice($tabs, 5));
  }
}
