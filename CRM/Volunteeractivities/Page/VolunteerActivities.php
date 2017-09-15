<?php
use CRM_Volunteeractivities_ExtensionUtil as E;

class CRM_Volunteeractivities_Page_VolunteerActivities extends CRM_Core_Page {

  public function run() {
    $contact = $this->validateContact();
    CRM_Utils_System::setTitle(E::ts('Volunteer Activities of ') . $contact["display_name"]);
    $activities = $this->getVolunteerActivities($contact);
    $columnHeaders = array(
      "Activity Date",
      "Volunteer Role",
      "Subject",
      "Campaign",
      "Supervisor",
      "Location",
      "Status",
    );
    $this->assign('columnHeaders', $columnHeaders);
    $this->assign('activities', $activities);
    parent::run();
  }

  private function getVolunteerActivities($contact) {
    $activities = civicrm_api3("ActivityContact", 'get', array(
      "contact_id"     => $contact["id"],
      'return'         => array(
        "activity_id.activity_date_time",
        "activity_id.subject",
        "activity_id.campaign_id.title",
        "activity_id.location",
        "activity_id.status_id",
        "activity_id",
      ),
      "api.OptionValue.get.activity_status" => array(
        'value' => "\$value.activity_id.status_id",
        'option_group_id.name' => 'activity_status',
      ),
      "api.CustomValue.get.Volunteer_Role_Id" => array(
        'entity_id' => "\$value.activity_id",
        'entity_table' => 'Activity',
        'return.CiviVolunteer:Volunteer_Role_Id' => 1,
      ),
      "api.OptionValue.get.volunteer_role" => array(
        'value' => "\$value.api.CustomValue.get.Volunteer_Role_Id.values.0.latest",
        'option_group_id.name' => 'volunteer_role',
      ),
      "record_type_id" => 1,
      "sequential"     => 1,
      "options" => array(
        'sort' => "activity_id.activity_date_time DESC",
      ),
    ));
    $activities = $this->formatActivityValues($activities);
    return $activities;
  }

  private function formatActivityValues($activities) {
    $formattdActivities = array();
    if ($activities["count"] == 0) {
      return $formattdActivities;
    }
    $activities = $activities["values"];
    foreach ($activities as $activity) {
      $formattedActivity = array(
        "datetime" => $activity["activity_id.activity_date_time"],
        "subject"  => $activity["activity_id.subject"],
        "id"       => $activity["activity_id"],
      );
      $this->formatExtraFields($activity, $formattedActivity);
      $formattdActivities[] = $formattedActivity;
    }
    return $formattdActivities;
  }

  private function formatExtraFields($activity, &$formattedActivity) {
    $this->saveValueIfExists("activity_id.campaign_id.title", "campaign_name", $activity, $formattedActivity);
    $this->saveValueIfExists("activity_id.location", "location", $activity, $formattedActivity);
    $this->saveVolunteerRole($activity, $formattedActivity);
    $this->saveActivityStatus($activity, $formattedActivity);
    $this->saveActivitySupervisor($activity, $formattedActivity);
  }

  private function saveActivitySupervisor($activity, &$formattedActivity) {
    $activitiesSupervisor = civicrm_api3("ActivityContact", 'get', array(
        "activity_id"             => $formattedActivity["id"],
        "record_type_id" => 3,
        "sequential"     => 1,
        'return'         => array(
          "contact_id.display_name",
        ),
    ));
    $formattedActivity["supervisor"] = "";
    if ($activitiesSupervisor["count"] > 0) {
      $formattedActivity["supervisor"] = $activitiesSupervisor["values"][0]["contact_id.display_name"];
    }
  }

  private function saveVolunteerRole($activity, &$formattedActivity) {
    $formattedActivity["volunteer_role"] = "";
    if ($activity["api.OptionValue.get.volunteer_role"]["count"] > 0) {
      $formattedActivity["volunteer_role"] = $activity["api.OptionValue.get.volunteer_role"]["values"][0]["label"];
    }
  }

  private function saveActivityStatus($activity, &$formattedActivity) {
    $formattedActivity["status"] = "";
    if ($activity["api.OptionValue.get.activity_status"]["count"] > 0) {
      $formattedActivity["status"] = $activity["api.OptionValue.get.activity_status"]["values"][0]["label"];
    }
  }

  private function saveValueIfExists($sourcekey, $destinationkey, $sourceArray, &$destinationArray) {
    $destinationArray[$destinationkey] = "";
    if (array_key_exists($sourcekey, $sourceArray)) {
      $destinationArray[$destinationkey] = $sourceArray[$sourcekey];
    }
  }

  private function validateContact() {
    $contact_id = CRM_Utils_Request::retrieve('cid', 'Positive',
      $this, FALSE, 0
    );
    $contact = civicrm_api3("Contact  ", 'get', array(
      "id"         => $contact_id,
      "sequential" => 1,
    ));
    if ($contact["count"] == 0) {
      throw new Exception(ts('Could not find contact.'));
    }
    $contact = $contact["values"][0];
    return $contact;
  }

}
