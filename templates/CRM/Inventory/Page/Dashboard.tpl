{* Inventory Management Dashboard Template *}

<div class="crm-content-block">

  {* Alerts Section *}
  {if $alerts}
    <div class="crm-section alerts-section">
      <h3>{ts}Alerts & Notifications{/ts}</h3>
      {foreach from=$alerts item=alert}
        <div class="messages {$alert.type}">
          <strong>{$alert.title}</strong>: {$alert.message}
          {if $alert.action_url}
            <a href="{$alert.action_url}" class="button">{$alert.action_text}</a>
          {/if}
        </div>
      {/foreach}
    </div>
  {/if}

  {* Statistics Overview *}
  <div class="crm-section statistics-section">
    <h3>{ts}Inventory Overview{/ts}</h3>

    <div class="stats-grid">
      {* Product Statistics *}
      <div class="stats-card">
        <h4>{ts}Products{/ts}</h4>
        <div class="stats-content">
          <div class="stat-item">
            <span class="stat-value">{$productStats.total_products}</span>
            <span class="stat-label">{ts}Total Products{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$productStats.active_products}</span>
            <span class="stat-label">{ts}Active Products{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$productStats.available_variants}</span>
            <span class="stat-label">{ts}Available Devices{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$productStats.assigned_variants}</span>
            <span class="stat-label">{ts}Assigned Devices{/ts}</span>
          </div>
        </div>
        <div class="stats-actions">
          <a href="{crmURL p='civicrm/inventory/products' q='reset=1'}" class="button">{ts}Manage Products{/ts}</a>
        </div>
      </div>

      {* Sales Statistics *}
      <div class="stats-card">
        <h4>{ts}Sales (This Month){/ts}</h4>
        <div class="stats-content">
          <div class="stat-item">
            <span class="stat-value">{$salesStats.monthly.total_sales}</span>
            <span class="stat-label">{ts}Total Sales{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$salesStats.monthly.completed_sales}</span>
            <span class="stat-label">{ts}Completed{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$salesStats.monthly.pending_sales}</span>
            <span class="stat-label">{ts}Pending{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">${$salesStats.monthly.total_value|number_format:2}</span>
            <span class="stat-label">{ts}Total Value{/ts}</span>
          </div>
        </div>
        <div class="stats-actions">
          <a href="{crmURL p='civicrm/inventory/sales' q='reset=1'}" class="button">{ts}Manage Sales{/ts}</a>
        </div>
      </div>

      {* Warehouse Statistics *}
      <div class="stats-card">
        <h4>{ts}Warehouses{/ts}</h4>
        <div class="stats-content">
          <div class="stat-item">
            <span class="stat-value">{$warehouseStats.total_warehouses}</span>
            <span class="stat-label">{ts}Total Warehouses{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$warehouseStats.refrigerated_warehouses}</span>
            <span class="stat-label">{ts}Refrigerated{/ts}</span>
          </div>
        </div>
        <div class="stats-actions">
          <a href="{crmURL p='civicrm/inventory/warehouses' q='reset=1'}" class="button">{ts}Manage Warehouses{/ts}</a>
        </div>
      </div>

      {* Problem Devices *}
      <div class="stats-card">
        <h4>{ts}Device Status{/ts}</h4>
        <div class="stats-content">
          <div class="stat-item">
            <span class="stat-value">{$productStats.suspended_variants}</span>
            <span class="stat-label">{ts}Suspended{/ts}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{$productStats.problem_variants}</span>
            <span class="stat-label">{ts}Problem Devices{/ts}</span>
          </div>
        </div>
        <div class="stats-actions">
          <a href="{crmURL p='civicrm/inventory/devices' q='reset=1&status=problem'}" class="button">{ts}View Problem Devices{/ts}</a>
        </div>
      </div>
    </div>
  </div>

  {* Recent Activity *}
  <div class="crm-section recent-activity-section">
    <h3>{ts}Recent Activity{/ts}</h3>

    <div class="activity-tabs">
      {* Recent Sales *}
      <div class="activity-tab">
        <h4>{ts}Recent Sales{/ts}</h4>
        {if $recentSales}
          <table class="display">
            <thead>
            <tr>
              <th>{ts}Order Code{/ts}</th>
              <th>{ts}Customer{/ts}</th>
              <th>{ts}Date{/ts}</th>
              <th>{ts}Status{/ts}</th>
              <th>{ts}Items{/ts}</th>
              <th>{ts}Actions{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$recentSales item=sale}
              <tr>
                <td>{$sale.code}</td>
                <td>{$sale.contact_name}</td>
                <td>{$sale.sale_date|crmDate}</td>
                <td>
                  <span class="crm-status status-{$sale.status_id}">{$sale.status_id}</span>
                </td>
                <td>{$sale.item_count}</td>
                <td>
                  <a href="{crmURL p='civicrm/inventory/sales/view' q="reset=1&id=`$sale.id`"}" class="action-item">View</a>
                </td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <p>{ts}No recent sales.{/ts}</p>
        {/if}
      </div>

      {* Recent Assignments *}
      <div class="activity-tab">
        <h4>{ts}Recent Device Assignments{/ts}</h4>
        {if $recentAssignments}
          <table class="display">
            <thead>
            <tr>
              <th>{ts}Device ID{/ts}</th>
              <th>{ts}Product{/ts}</th>
              <th>{ts}Contact{/ts}</th>
              <th>{ts}Date{/ts}</th>
              <th>{ts}Status{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$recentAssignments item=assignment}
              <tr>
                <td>{$assignment.unique_id}</td>
                <td>{$assignment.product_label}</td>
                <td>{$assignment.contact_name}</td>
                <td>{$assignment.assigned_date|crmDate}</td>
                <td>
                  <span class="crm-status status-{$assignment.status}">{$assignment.status}</span>
                </td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <p>{ts}No recent assignments.{/ts}</p>
        {/if}
      </div>

      {* Recent Changes *}
      <div class="activity-tab">
        <h4>{ts}Recent Status Changes{/ts}</h4>
        {if $recentChanges}
          <table class="display">
            <thead>
            <tr>
              <th>{ts}Device ID{/ts}</th>
              <th>{ts}Product{/ts}</th>
              <th>{ts}Status Change{/ts}</th>
              <th>{ts}Date{/ts}</th>
              <th>{ts}Modified By{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$recentChanges item=change}
              <tr>
                <td>{$change.unique_id}</td>
                <td>{$change.product_label}</td>
                <td>
                  <span class="crm-status status-{$change.status_id}">{$change.status_id}</span>
                </td>
                <td>{$change.created_date|crmDate}</td>
                <td>{$change.modified_by_name}</td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {else}
          <p>{ts}No recent changes.{/ts}</p>
        {/if}
      </div>
    </div>
  </div>

  {* Quick Actions *}
  <div class="crm-section quick-actions-section">
    <h3>{ts}Quick Actions{/ts}</h3>
    <div class="quick-actions-grid">
      <a href="{crmURL p='civicrm/inventory/products/add' q='reset=1'}" class="action-card">
        <i class="crm-i fa-plus"></i>
        <span>{ts}Add Product{/ts}</span>
      </a>
      <a href="{crmURL p='civicrm/inventory/import' q='reset=1'}" class="action-card">
        <i class="crm-i fa-upload"></i>
        <span>{ts}Import Devices{/ts}</span>
      </a>
      <a href="{crmURL p='civicrm/inventory/batch' q='reset=1'}" class="action-card">
        <i class="crm-i fa-list"></i>
        <span>{ts}Batch Process{/ts}</span>
      </a>
      <a href="{crmURL p='civicrm/inventory/reports' q='reset=1'}" class="action-card">
        <i class="crm-i fa-chart-bar"></i>
        <span>{ts}View Reports{/ts}</span>
      </a>
    </div>
  </div>

</div>

{* CSS Styles *}
<style>
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stats-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .stats-card h4 {
    margin: 0 0 15px 0;
    color: #333;
    border-bottom: 2px solid #0074cc;
    padding-bottom: 10px;
  }

  .stats-content {
    margin-bottom: 15px;
  }

  .stat-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 5px 0;
  }

  .stat-value {
    font-weight: bold;
    font-size: 18px;
    color: #0074cc;
  }

  .stat-label {
    color: #666;
    font-size: 14px;
  }

  .stats-actions .button {
    width: 100%;
    text-align: center;
  }

  .activity-tabs {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
  }

  .activity-tab {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
  }

  .activity-tab h4 {
    margin: 0 0 15px 0;
    color: #333;
    border-bottom: 2px solid #0074cc;
    padding-bottom: 10px;
  }

  .quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
  }

  .action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
  }

  .action-card:hover {
    background: #f5f5f5;
    border-color: #0074cc;
    text-decoration: none;
    color: #0074cc;
  }

  .action-card i {
    font-size: 24px;
    margin-bottom: 10px;
  }

  .crm-status {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
  }

  .status-placed, .status-processing { background: #fef3cd; color: #856404; }
  .status-shipped { background: #d1ecf1; color: #0c5460; }
  .status-completed { background: #d4edda; color: #155724; }
  .status-cancelled { background: #f8d7da; color: #721c24; }
  .status-active { background: #d4edda; color: #155724; }
  .status-suspended { background: #fef3cd; color: #856404; }
  .status-problem, .status-defective { background: #f8d7da; color: #721c24; }

  .alerts-section {
    margin-bottom: 30px;
  }

  .alerts-section .messages {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }

  .alerts-section .button {
    margin-left: 15px;
  }
</style>
