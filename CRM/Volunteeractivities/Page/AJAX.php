<?php
use CRM_Volunteeractivities_ExtensionUtil as E;

class CRM_Volunteeractivities_Page_AJAX extends CRM_Core_Page {

  /**
   * Static function to fetch the volunteer activities for Datatable.
   * @output json string of activities.
   */
  public static function getVolunteerActivities() {
    $requiredParameters = array(
      'cid' => 'Integer',
    );

    $optionalParameters = array();
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

    $activities = self::filterVolunteerActivities($contact, $params);
    CRM_Utils_JSON::output(array(
      "data"         => $activities["records"],
      "recordsTotal" => $activities["total"],
      "recordsFiltered" => $activities["total"],
    ));

  }

  /**
   * Function to filter the volunteer activities of particular contact with given params from the Datatables.
   * @param
   * $contact
   *   Conatact of which volunteer activities needs to fetch.
   * $params
   *   Parameters from datatables to get the length and start position of the result set.
   * @return array with filtered records and total count of activities.
   */
  private static function filterVolunteerActivities($contact, $params) {
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
        "activity_id.activity_type_id",
        "activity_id.source_record_id",
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
    $activities = self::formatActivityValues($activities, $contact["id"]);
    return array(
      "records" => $activities,
      "total"   => $activitiescount,
    );
  }

  /**
   * This function formats the values of filtered activities.
   * @param
   * $activities
   *   Filtered activities from filterVolunteerActivities() function.
   * $contactid
   *   Contact ID for which activities are getting fetched.
   * @return formatted activities with required format by datatables on Frontend.
   */
  private static function formatActivityValues($activities, $contactid) {
    $formattdActivities = array();
    if ($activities["count"] == 0) {
      return $formattdActivities;
    }
    $activities = $activities["values"];

    $page = new CRM_Core_Page();
    CRM_Contact_Page_View::checkUserPermission($page, $contactid);
    $permissions = array($page->_permission);
    if (CRM_Core_Permission::check('delete activities')) {
      $permissions[] = CRM_Core_Permission::DELETE;
    }

    $mask = CRM_Core_Action::mask($permissions);

    foreach ($activities as $activity) {
      $formattedActivity = array(
        "datetime" => $activity["activity_id.activity_date_time"],
        "subject"  => $activity["activity_id.subject"],
        "id"       => $activity["activity_id"],
      );
      self::formatExtraFields($activity, $formattedActivity);
      self::addActivityLinks($activity, $contactid, $formattedActivity);
      $formattedActivity['datetime'] = CRM_Utils_Date::customFormat($formattedActivity['datetime']);
      $formattdActivities[] = $formattedActivity;
    }
    return $formattdActivities;
  }

  /**
   * This function adds Operation links for each activity.
   * Operation links suuch as View, Edit & Delete.
   * @param
   * $activity
   *    Fetched activity array from database. We need this to get basic activity details e.g. activity_type_id, activity_id etc...
   * $contactid
   *    Contact ID for which activities are gettiing fetched.
   * $formattedActivity
   *    Reference to the formatted activity array to directly add the links into the array.
   */
  private static function addActivityLinks($activity, $contactid, &$formattedActivity) {
    $formattedActivity['links'] = '';
    $accessMailingReport = FALSE;

    $actionLinks = CRM_Activity_Selector_Activity::actionLinks(
      CRM_Utils_Array::value('activity_id.activity_type_id', $activity),
      CRM_Utils_Array::value('activity_id.source_record_id', $values),
      $accessMailingReport,
      CRM_Utils_Array::value('activity_id', $activity)
    );

    $actionMask = array_sum(array_keys($actionLinks)) & $mask;

    $formattedActivity['links'] = CRM_Core_Action::formLink($actionLinks,
      $actionMask,
      array(
        'id' => $activity['activity_id'],
        'cid' => $contactid,
      ),
      ts('more'),
      FALSE,
      'activity.tab.row',
      'Activity',
      $activity['activity_id']
    );
  }

  /**
   * A wrapper function to call all the formating functions.
   * @param
   * $activity
   *    Original activity array fetched from database. We need this to match the current data.
   * $formattedActivity
   *    Reference to the formatted activity to manipulate the data directly into the array.
   */
  private static function formatExtraFields($activity, &$formattedActivity) {
    self::saveValueIfExists("activity_id.campaign_id.title", "campaign_name", $activity, $formattedActivity);
    self::saveValueIfExists("activity_id.location", "location", $activity, $formattedActivity);
    self::saveVolunteerRole($activity, $formattedActivity);
    self::saveActivityStatus($activity, $formattedActivity);
    self::saveActivitySupervisor($activity, $formattedActivity);
  }

  /**
   * A function to get the supervisor(with-contact) of the activity. If none is found we set it to blank.
   * @param
   * $activity
   *    Original activity array fetched from database. We need this to match the current data.
   * $formattedActivity
   *    Reference to the formatted activity to add the data directly into the array.
   */
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

  /**
   * A function to save the volunteer role of the contact for particular activity. If none is found we set it to blank.
   * @param
   * $activity
   *    Original activity array fetched from database. We need this to match the current data.
   * $formattedActivity
   *    Reference to the formatted activity to add the data directly into the array.
   */
  private static function saveVolunteerRole($activity, &$formattedActivity) {
    $formattedActivity["volunteer_role"] = "";
    if ($activity["api.OptionValue.get.volunteer_role"]["count"] > 0) {
      $formattedActivity["volunteer_role"] = $activity["api.OptionValue.get.volunteer_role"]["values"][0]["label"];
    }
  }

  /**
   * A function to save the activity status. If none is found we set it to blank.
   * @param
   * $activity
   *    Original activity array fetched from database. We need this to match the current data.
   * $formattedActivity
   *    Reference to the formatted activity to add the data directly into the array.
   */
  private static function saveActivityStatus($activity, &$formattedActivity) {
    $formattedActivity["status"] = "";
    if ($activity["api.OptionValue.get.activity_status"]["count"] > 0) {
      $formattedActivity["status"] = $activity["api.OptionValue.get.activity_status"]["values"][0]["label"];
    }
  }

  /**
   * A function to save the particular value from the array into formatted activity. If none is found we set it to blank.
   * @param
   * $sourcekey
   *    Key for which we want to find the value.
   * $destinationkey
   *    Key by which we want to save the value in formatted array.
   * $sourceArray
   *    Array from which we want to fetch the value.
   * $destinationArray
   *   Reference to the destination array to save the data directly into the array.
   */
  private static function saveValueIfExists($sourcekey, $destinationkey, $sourceArray, &$destinationArray) {
    $destinationArray[$destinationkey] = "";
    if (array_key_exists($sourcekey, $sourceArray)) {
      $destinationArray[$destinationkey] = $sourceArray[$sourcekey];
    }
  }

  /**
   * A function to validate the given contact by given cid(Contact ID)
   * @return Exception|Contact Array
   */
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
