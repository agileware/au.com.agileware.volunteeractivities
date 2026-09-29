# Volunteer Activities (au.com.agileware.volunteeractivities)

This is a [CiviCRM](https://civicrm.org) extension which adds a "Volunteer Activities" tab to the
Contact Summary page, listing the volunteer-related Activities recorded against a contact. It gives
staff a quick, dedicated view of a volunteer's activity history without having to filter the
contact's main Activities tab by hand.

**This extension requires the [CiviVolunteer](https://github.com/civicrm/org.civicrm.volunteer)
extension to be installed and enabled.** It reads the `Volunteer Role` custom field and `volunteer
role` option group provided by CiviVolunteer, and has no useful function without it.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Usage

Once installed (and with CiviVolunteer enabled), a new **Volunteer Activities** tab appears on every
Contact Summary page, alongside the standard tabs such as Activities and Relationships.

The tab shows a paginated, sortable-by-date table of the contact's Activities of type **Volunteer**
or **Volunteer Commendation**, with the following columns:

* **Activity Date**
* **Volunteer Role** (from the CiviVolunteer `Volunteer Role Id` custom field)
* **Subject**
* **Campaign**
* **Supervisor** (the contact recorded against the activity with the "Assignee"/supervisor role)
* **Location**
* **Status**
* A row-level actions menu (**more**), offering View, Edit and Delete links for the activity,
  subject to the user's normal CiviCRM Activity permissions (e.g. the `delete activities` permission
  is required to see the Delete option)

Data is loaded asynchronously via an AJAX endpoint
(`civicrm/ajax/volunteeractivity`) that backs the datatable shown on the tab; there is no separate
settings page, search, or report screen provided by this extension.

### Prerequisite check

On the Manage Extensions page and on the Contact Summary page, the extension checks whether
CiviVolunteer (`org.civicrm.volunteer`) is installed and enabled. If it is not, a status message is
displayed explaining that the Volunteer Activities extension was installed successfully but that
CiviVolunteer must also be installed and enabled before volunteer activities can be shown.

## Special configuration requirements

This extension has no settings page and requires no API keys, tokens, or other credentials. The
only setup requirement is:

* The [CiviVolunteer](https://github.com/civicrm/org.civicrm.volunteer) extension
  (`org.civicrm.volunteer`) must be installed and enabled.
* Users need the standard **access CiviCRM** permission to view the tab and the AJAX data feed
  (the same permission used for the menu items registered by this extension). Viewing, editing or
  deleting individual activities from the row actions is further governed by CiviCRM's normal
  Activity permissions (e.g. `access all cases and activities` / `delete activities`, as applicable
  to the contact).

## Requirements

* CiviCRM 5.51+
* [CiviVolunteer](https://github.com/civicrm/org.civicrm.volunteer) (`org.civicrm.volunteer`)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin
Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

About the Authors
-----------------

This CiviCRM extension was developed by the team at [Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services including:

  * CiviCRM migration
  * CiviCRM integration
  * CiviCRM extension development
  * CiviCRM support
  * CiviCRM hosting
  * CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact Agileware](https://agileware.com.au/contact) today!

![Agileware](logo/agileware-logo.png)
