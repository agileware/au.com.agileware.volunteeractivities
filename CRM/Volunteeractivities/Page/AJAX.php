<?php
use CRM_Volunteeractivities_ExtensionUtil as E;

class CRM_Volunteeractivities_Page_AJAX extends CRM_Core_Page {

  public static function getVolunteerActivities() {
    $requiredParameters = array(
      'cid' => 'Integer',
    );

    $optionalParameters = array(

    );
    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    $params += CRM_Core_Page_AJAX::validateParams($requiredParameters, $optionalParameters);
    $params['contact_id'] = $params['cid'];
    unset($params['cid']);

    $contact = self::validateContact();
    $params["start"] = CRM_Utils_Request::retrieve('start', 'Positive',
      $this, FALSE, 0
    );
    $params["length"] = CRM_Utils_Request::retrieve('length', 'Positive',
      $this, FALSE, 25
    );

    $activities = self::filterVolunteerActivities($contact,$params);
    CRM_Utils_JSON::output(array(
      "data"         => $activities["records"],
      "recordsTotal" => $activities["total"],
      "recordsFiltered" => $activities["total"],
    ));
  }

  private static function filterVolunteerActivities($contact,$params) {
    $volunteerActivityTypes = civicrm_api3("OptionValue", 'get', array(
      "option_group_id.name" => 'activity_type',
      "name"                 => array('IN' => array('Volunteer', 'volunteer_commendation')),
      "sequential"           => 1,
    ));

    $volunteerActivityTypeIds = array();
    foreach ($volunteerActivityTypes["values"] as $volunteerActivityType) {
      $volunteerActivityTypeIds[] = $volunteerActivityType["value"];
    }

    $params = array(
      "contact_id"       => $contact["id"],
      'activity_id.activity_type_id' => array('IN' => $volunteerActivityTypeIds),
      'return'           => array(
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
        'sort'  => "activity_id.activity_date_time DESC",
        'limit' => $params["length"],
        'offset' => $params["start"],
      ),
    );

    $activities = civicrm_api3("ActivityContact", 'get', $params);
    unset($params["options"]);
    $activitiescount = civicrm_api3("ActivityContact", 'getcount', $params);
    $activities = self::formatActivityValues($activities);
    return array(
      "records" => $activities,
      "total"   => $activitiescount
    );
  }

  private static function formatActivityValues($activities) {
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
      self::formatExtraFields($activity, $formattedActivity);
      $formattedActivity['datetime'] = CRM_Utils_Date::customFormat($formattedActivity['datetime']);
      $formattdActivities[] = $formattedActivity;
    }
    return $formattdActivities;
  }

  private static function formatExtraFields($activity, &$formattedActivity) {
    self::saveValueIfExists("activity_id.campaign_id.title", "campaign_name", $activity, $formattedActivity);
    self::saveValueIfExists("activity_id.location", "location", $activity, $formattedActivity);
    self::saveVolunteerRole($activity, $formattedActivity);
    self::saveActivityStatus($activity, $formattedActivity);
    self::saveActivitySupervisor($activity, $formattedActivity);
  }

  private static function saveActivitySupervisor($activity, &$formattedActivity) {
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

  private static function saveVolunteerRole($activity, &$formattedActivity) {
    $formattedActivity["volunteer_role"] = "";
    if ($activity["api.OptionValue.get.volunteer_role"]["count"] > 0) {
      $formattedActivity["volunteer_role"] = $activity["api.OptionValue.get.volunteer_role"]["values"][0]["label"];
    }
  }

  private static function saveActivityStatus($activity, &$formattedActivity) {
    $formattedActivity["status"] = "";
    if ($activity["api.OptionValue.get.activity_status"]["count"] > 0) {
      $formattedActivity["status"] = $activity["api.OptionValue.get.activity_status"]["values"][0]["label"];
    }
  }

  private static function saveValueIfExists($sourcekey, $destinationkey, $sourceArray, &$destinationArray) {
    $destinationArray[$destinationkey] = "";
    if (array_key_exists($sourcekey, $sourceArray)) {
      $destinationArray[$destinationkey] = $sourceArray[$sourcekey];
    }
  }

  public static function validateContact() {
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
