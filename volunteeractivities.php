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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function volunteeractivities_civicrm_install() {
  _volunteeractivities_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function volunteeractivities_civicrm_enable() {
  _volunteeractivities_civix_civicrm_enable();
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
