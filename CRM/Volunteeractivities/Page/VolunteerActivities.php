<?php
use CRM_Volunteeractivities_ExtensionUtil as E;

class CRM_Volunteeractivities_Page_VolunteerActivities extends CRM_Core_Page {

  public function run() {
    $contact = CRM_Volunteeractivities_Page_AJAX::validateContact();
    CRM_Utils_System::setTitle(E::ts('Volunteer Activities of ') . $contact["display_name"]);
    $columnHeaders = array(
      array(
        "key"   => "datetime",
        "title" => "Activity Date",
      ),
      array(
        "key"   => "volunteer_role",
        "title" => "Volunteer Role",
      ),
      array(
        "key"   => "subject",
        "title" => "Subject",
      ),
      array(
        "key"   => "campaign_name",
        "title" => "Campaign",
      ),
      array(
        "key"   => "supervisor",
        "title" => "Supervisor",
      ),
      array(
        "key"   => "location",
        "title" => "Location",
      ),
      array(
        "key"   => "status",
        "title" => "Status",
      ),
    );
    $this->assign('contactId',$contact["id"]);
    $this->assign('columnHeaders', $columnHeaders);
    parent::run();
  }
}
