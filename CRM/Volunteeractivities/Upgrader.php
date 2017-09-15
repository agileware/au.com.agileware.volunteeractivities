<?php
use CRM_Volunteeractivities_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Volunteeractivities_Upgrader extends CRM_Volunteeractivities_Upgrader_Base {
  /**
   * Extension Dependency Check
   *
   * @return Array of names of unmet extension dependencies; NOTE: returns an
   *         empty array when all dependencies are met.
   */
  public static function checkExtensionDependencies() {
    $manager = CRM_Extension_System::singleton()->getManager();
    $dependencies = array(
      'org.civicrm.volunteer',
    );
    $unmet = array();
    foreach ($dependencies as $ext) {
      if ($manager->getStatus($ext) != CRM_Extension_Manager::STATUS_INSTALLED) {
        array_push($unmet, $ext);
      }
    }
    return $unmet;
  }

  /**
   * Look up extension dependency error messages and display as Core Session Status
   *
   * @param array $unmet
   */
  public static function displayDependencyErrors($unmet) {
    foreach ($unmet as $ext) {
      $message = self::getUnmetDependencyErrorMessage($ext);
      CRM_Core_Session::setStatus($message, ts('Prerequisite check failed.', array('domain' => 'org.civicrm.volunteer')), 'error');
    }
  }

  /**
   * Mapping of extensions names to localized dependency error messages
   *
   * @param string $unmet an extension name
   */
  public static function getUnmetDependencyErrorMessage($unmet) {
    switch ($unmet) {
      case 'org.civicrm.volunteer':
        return ts('VolunteerActivities was installed successfully, but you must also install and enable the <a href="%1" target="_blank">CiviVolunteer Extension</a> before you can see volunteer activites of contact.', array(1 => 'https://github.com/civicrm/org.civicrm.volunteer', 'domain' => 'au.com.agileware.volunteeractivities'));
    }
    CRM_Core_Error::fatal(ts('Unknown error key: %1', array(1 => $unmet, 'domain' => 'au.com.agileware.volunteeractivities')));
  }

}
