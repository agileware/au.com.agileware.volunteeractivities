{strip}
  <table class="volunteer-activities-selector crm-ajax-table">
    <thead class="sticky">
    <tr>
      {foreach from=$columnHeaders item=header}
        <th data-data="{$header.key}" scope="col">
            {$header.title}
        </th>
      {/foreach}
    </tr>
    </thead>

  </table>
{/strip}

{literal}
  <script type="text/javascript">
    (function($) {
      CRM.$('.volunteer-activities-selector').data({
        "ajax": {
          "method": "POST",
          "url": {/literal}'{crmURL p="civicrm/ajax/volunteeractivity" h=0 q="cid=$contactId"}'{literal},
          "data": function (d) {

          }
        },
        "ordering": false
      });
    })(CRM.$);
  </script>
{/literal}
