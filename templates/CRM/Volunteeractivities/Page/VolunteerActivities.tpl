{strip}
  {if count($activities) > 0}
  <table class="selector row-highlight">
    <thead class="sticky">
    <tr>
      {foreach from=$columnHeaders item=header}
        <th scope="col">
            {$header}
        </th>
      {/foreach}
    </tr>
    </thead>

    {foreach from=$activities item=activity}
      <tr>
          <td>{$activity.datetime|crmDate}</td>
          <td>{$activity.volunteer_role}</td>
          <td>{$activity.subject}</td>
          <td>{$activity.campaign_name}</td>
          <td>{$activity.supervisor}</td>
          <td>{$activity.location}</td>
          <td>{$activity.status}</td>
      </tr>
    {/foreach}
  </table>
  {else}
  <div class="messages status no-popup">
    <div class="icon inform-icon"></div>
      {ts}No volunteer activities have been recorded for this contact.{/ts}
    </div>
  </div>
  {/if}
{/strip}
