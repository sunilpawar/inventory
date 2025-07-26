{* Contact Inventory Tab Template *}

<div class="crm-content-block crm-contact-inventory">

  {* Statistics Summary *}
  <div class="crm-section inventory-summary">
    <div class="summary-cards">
      <div class="summary-card">
        <div class="summary-value">{$inventoryStats.total_devices}</div>
        <div class="summary-label">{ts}Total Devices{/ts}</div>
      </div>
      <div class="summary-card active">
        <div class="summary-value">{$inventoryStats.active_devices}</div>
        <div class="summary-label">{ts}Active{/ts}</div>
      </div>
      <div class="summary-card suspended">
        <div class="summary-value">{$inventoryStats.suspended_devices}</div>
        <div class="summary-label">{ts}Suspended{/ts}</div>
      </div>
      <div class="summary-card problem">
        <div class="summary-value">{$inventoryStats.problem_devices}</div>
        <div class="summary-label">{ts}Problem{/ts}</div>
      </div>
    </div>
  </div>

  {* Tab Navigation *}
  <div class="crm-section inventory-tabs">
    <div id="inventory-tabs" class="ui-tabs ui-corner-all">
      <ul class="ui-tabs-nav ui-corner-all">
        <li class="ui-tabs-tab ui-corner-top ui-tabs-active">
          <a href="#current-inventory">{ts}Current Inventory{/ts}</a>
        </li>
        <li class="ui-tabs-tab ui-corner-top">
          <a href="#inventory-history">{ts}History{/ts}</a>
        </li>
        <li class="ui-tabs-tab ui-corner-top">
          <a href="#warranties">{ts}Warranties{/ts}</a>
        </li>
        <li class="ui-tabs-tab ui-corner-top">
          <a href="#sales-history">{ts}Sales{/ts}</a>
        </li>
      </ul>

      {* Current Inventory Tab *}
      <div id="current-inventory" class="ui-tabs-panel">
        {if $currentInventory}
          <table class="display crm-inventory-table">
            <thead>
            <tr>
              <th>{ts}Product{/ts}</th>
              <th>{ts}Device ID{/ts}</th>
              <th>{ts}Phone Number{/ts}</th>
              <th>{ts}Status{/ts}</th>
              <th>{ts}Warranty{/ts}</th>
              <th>{ts}Membership{/ts}</th>
              <th>{ts}Assigned Date{/ts}</th>
              <th>{ts}Actions{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$currentInventory item=device}
              <tr class="device-row {if $device.is_suspended}suspended{/if} {if $device.is_problem}problem{/if}">
                <td>
                  <strong>{$device.product_label}</strong>
                  <br><small>{$device.product_code}</small>
                </td>
                <td>{$device.unique_id}</td>
                <td>{$device.phone_number}</td>
                <td>
                    <span class="crm-status status-{$device.status}">
                      {$device.status}
                      {if $device.is_suspended}<br><span class="suspended-label">{ts}SUSPENDED{/ts}</span>{/if}
                      {if $device.is_problem}<br><span class="problem-label">{ts}PROBLEM{/ts}</span>{/if}
                    </span>
                </td>
                <td>
                  {if $device.warranty_start_date}
                    {$device.warranty_start_date|crmDate:"short"} -<br>
                    {$device.warranty_end_date|crmDate:"short"}
                  {else}
                    {ts}No warranty{/ts}
                  {/if}
                </td>
                <td>
                  {if $device.membership_status}
                    <span class="membership-status-{$device.membership_status}">
                        {$device.membership_start_date|crmDate} - {$device.membership_end_date|crmDate}
                      </span>
                  {else}
                    {ts}No membership{/ts}
                  {/if}
                </td>
                <td>{$device.created_at|crmDate}</td>
                <td>
                  <div class="btn-group">
                    <a href="{crmURL p='civicrm/inventory/device/view' q="reset=1&id=`$device.id`"}" class="btn btn-xs btn-primary">{ts}View{/ts}</a>
                    {if $device.is_suspended}
                      <a href="{crmURL p='civicrm/inventory/device/reactivate' q="reset=1&id=`$device.id`"}" class="btn btn-xs btn-success">{ts}Reactivate{/ts}</a>
                    {else}
                      <a href="{crmURL p='civicrm/inventory/device/suspend' q="reset=1&id=`$device.id`"}" class="btn btn-xs btn-warning">{ts}Suspend{/ts}</a>
                    {/if}
                    {if $device.is_problem}
                      <a href="{crmURL p='civicrm/inventory/device/replace' q="reset=1&id=`$device.id`"}" class="btn btn-xs btn-danger">{ts}Replace{/ts}</a>
                    {/if}
                  </div>
                </td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <div class="messages status no-popup">
            <div class="icon inform-icon"></div>
            {ts}This contact has no current inventory assigned.{/ts}
          </div>
        {/if}
      </div>

      {* History Tab *}
      <div id="inventory-history" class="ui-tabs-panel">
        {if $deviceHistory}
          <table class="display crm-inventory-history-table">
            <thead>
            <tr>
              <th>{ts}Date{/ts}</th>
              <th>{ts}Product{/ts}</th>
              <th>{ts}Device ID{/ts}</th>
              <th>{ts}Action{/ts}</th>
              <th>{ts}Status{/ts}</th>
              <th>{ts}Changed By{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$deviceHistory item=history}
              <tr>
                <td>{$history.change_date|crmDate}</td>
                <td>
                  {$history.product_label}
                  <br><small>{$history.product_code}</small>
                </td>
                <td>{$history.unique_id}</td>
                <td>
                  {if $history.change_status}
                    <span class="action-{$history.change_status}">{$history.change_status}</span>
                  {else}
                    <span class="action-assigned">ASSIGNED</span>
                  {/if}
                </td>
                <td>
                  <span class="crm-status status-{$history.status}">{$history.status}</span>
                </td>
                <td>{$history.changed_by}</td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <div class="messages status no-popup">
            <div class="icon inform-icon"></div>
            {ts}No device history found.{/ts}
          </div>
        {/if}
      </div>

      {* Warranties Tab *}
      <div id="warranties" class="ui-tabs-panel">
        {if $warrantyInfo}
          <table class="display crm-warranty-table">
            <thead>
            <tr>
              <th>{ts}Product{/ts}</th>
              <th>{ts}Device ID{/ts}</th>
              <th>{ts}Warranty Start{/ts}</th>
              <th>{ts}Warranty End{/ts}</th>
              <th>{ts}Days Until Expiry{/ts}</th>
              <th>{ts}Status{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$warrantyInfo item=warranty}
              <tr class="warranty-{$warranty.warranty_status}">
                <td>{$warranty.product_label}</td>
                <td>{$warranty.unique_id}</td>
                <td>{$warranty.warranty_start_date|crmDate}</td>
                <td>{$warranty.warranty_end_date|crmDate}</td>
                <td>
                  {if $warranty.days_until_expiry < 0}
                    <span class="expired">{ts}Expired{/ts} ({$warranty.days_until_expiry * -1} {ts}days ago{/ts})</span>
                  {elseif $warranty.days_until_expiry <= 30}
                    <span class="expiring">{$warranty.days_until_expiry} {ts}days{/ts}</span>
                  {else}
                    {$warranty.days_until_expiry} {ts}days{/ts}
                  {/if}
                </td>
                <td>
                    <span class="warranty-status warranty-{$warranty.warranty_status}">
                      {if $warranty.warranty_status == 'expired'}{ts}Expired{/ts}
                      {elseif $warranty.warranty_status == 'expiring'}{ts}Expiring Soon{/ts}
                      {else}{ts}Active{/ts}
                      {/if}
                    </span>
                </td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <div class="messages status no-popup">
            <div class="icon inform-icon"></div>
            {ts}No warranty information available.{/ts}
          </div>
        {/if}
      </div>

      {* Sales History Tab *}
      <div id="sales-history" class="ui-tabs-panel">
        {if $salesHistory}
          <table class="display crm-sales-history-table">
            <thead>
            <tr>
              <th>{ts}Order Code{/ts}</th>
              <th>{ts}Date{/ts}</th>
              <th>{ts}Status{/ts}</th>
              <th>{ts}Items{/ts}</th>
              <th>{ts}Total Value{/ts}</th>
              <th>{ts}Actions{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$salesHistory item=sale}
              <tr>
                <td>{$sale.code}</td>
                <td>{$sale.sale_date|crmDate}</td>
                <td>
                  <span class="crm-status status-{$sale.status_id}">{$sale.status_id}</span>
                </td>
                <td>{$sale.item_count}</td>
                <td>${$sale.total_value|number_format:2}</td>
                <td>
                  <a href="{crmURL p='civicrm/inventory/sales/view' q="reset=1&id=`$sale.id`"}" class="btn btn-xs btn-primary">{ts}View{/ts}</a>
                </td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <div class="messages status no-popup">
            <div class="icon inform-icon"></div>
            {ts}No sales history found.{/ts}
          </div>
        {/if}
      </div>
    </div>
  </div>

  {* Quick Actions *}
  <div class="crm-section quick-actions">
    <h3>{ts}Quick Actions{/ts}</h3>
    <div class="action-buttons">
      <a href="{crmURL p='civicrm/inventory/device/assign' q="reset=1&cid=`$contactId`"}" class="button">
        <i class="crm-i fa-plus"></i> {ts}Assign Device{/ts}
      </a>
      <a href="{crmURL p='civicrm/inventory/sales/add' q="reset=1&cid=`$contactId`"}" class="button">
        <i class="crm-i fa-shopping-cart"></i> {ts}Create Sale{/ts}
      </a>
      <a href="{crmURL p='civicrm/inventory/device/replace' q="reset=1&cid=`$contactId`"}" class="button">
        <i class="crm-i fa-exchange-alt"></i> {ts}Replace Device{/ts}
      </a>
    </div>
  </div>

</div>

{* CSS Styles *}
<style>
  .crm-contact-inventory {
    padding: 20px 0;
  }

  .inventory-summary {
    margin-bottom: 30px;
  }

  .summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
  }

  .summary-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .summary-card.active { border-left: 4px solid #28a745; }
  .summary-card.suspended { border-left: 4px solid #ffc107; }
  .summary-card.problem { border-left: 4px solid #dc3545; }

  .summary-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
  }

  .summary-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    margin-top: 5px;
  }

  .crm-inventory-table,
  .crm-inventory-history-table,
  .crm-warranty-table,
  .crm-sales-history-table {
    width: 100%;
    margin-top: 15px;
  }

  .device-row.suspended {
    background-color: #fff3cd;
  }

  .device-row.problem {
    background-color: #f8d7da;
  }

  .crm-status {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
  }

  .status-active { background: #d4edda; color: #155724; }
  .status-suspended { background: #fff3cd; color: #856404; }
  .status-problem, .status-defective { background: #f8d7da; color: #721c24; }
  .status-replaced { background: #e2e3e5; color: #383d41; }
  .status-placed, .status-processing { background: #d1ecf1; color: #0c5460; }
  .status-shipped { background: #cce7ff; color: #004085; }
  .status-completed { background: #d4edda; color: #155724; }

  .suspended-label, .problem-label {
    color: #dc3545;
    font-weight: bold;
    font-size: 10px;
  }

  .warranty-status.warranty-expired { color: #dc3545; }
  .warranty-status.warranty-expiring { color: #fd7e14; }
  .warranty-status.warranty-active { color: #28a745; }

  .warranty-expired { background-color: #f8d7da; }
  .warranty-expiring { background-color: #fff3cd; }

  .action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .action-buttons .button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }

  .btn-group {
    display: flex;
    gap: 2px;
  }

  .btn-xs {
    padding: 2px 6px;
    font-size: 11px;
    border-radius: 2px;
  }

  .btn-primary { background: #007cba; color: white; border: 1px solid #007cba; }
  .btn-success { background: #28a745; color: white; border: 1px solid #28a745; }
  .btn-warning { background: #ffc107; color: #212529; border: 1px solid #ffc107; }
  .btn-danger { background: #dc3545; color: white; border: 1px solid #dc3545; }

  .expired { color: #dc3545; font-weight: bold; }
  .expiring { color: #fd7e14; font-weight: bold; }

  #inventory-tabs .ui-tabs-panel {
    min-height: 300px;
    padding: 20px;
  }
</style>

{* JavaScript *}
<script>
  {literal}
  CRM.$(function($) {
    // Initialize tabs
    $('#inventory-tabs').tabs();

    // Initialize DataTables for better sorting/filtering
    $('.crm-inventory-table, .crm-inventory-history-table, .crm-warranty-table, .crm-sales-history-table').DataTable({
      "pageLength": 25,
      "order": [[ 0, "desc" ]],
      "responsive": true
    });
  });
  {/literal}
</script>
