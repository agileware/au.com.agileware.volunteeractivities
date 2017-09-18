<div class="crm-activity-selector-{$context}">
  <table class="volunteer-activities-selector crm-ajax-table">
    <thead>
    <tr>
      {foreach from=$columnHeaders item=header}
        <th data-data="{$header.key}" class="crm-contact-volunteeractivity-{$header.key}">
            {$header.title}
        </th>
      {/foreach}
    </tr>
    </thead>

  </table>

  {literal}
    <script type="text/javascript">
      (function($) {
        CRM.$('.volunteer-activities-selector').data({
          "ajax": {
            "method": "POST",
            "url": {/literal}'{crmURL p="civicrm/ajax/volunteeractivity" h=0 q="snippet=4&cid=$contactId"}'{literal},
            "data": function (d) {

            }
          },
          "ordering": false
        });
      })(CRM.$);
    </script>
  {/literal}
</div>
