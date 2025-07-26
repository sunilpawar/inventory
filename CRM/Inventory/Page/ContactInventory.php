<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

use CRM_Inventory_ExtensionUtil as E;

/**
 * Class CRM_Inventory_Page_ContactInventory
 *
 * Contact inventory tab page
 */
class CRM_Inventory_Page_ContactInventory extends CRM_Core_Page {

  /**
   * Contact ID
   *
   * @var int
   */
  protected $_contactId;

  /**
   * @var string
   */
  protected $_permission;

  /**
   * @var int
   */
  protected $_action;

  public function preProcess() {
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);

    // Check permissions
    $this->_permission = CRM_Contact_BAO_Contact_Permission::allow($this->_contactId, CRM_Core_Permission::VIEW);
    if (!$this->_permission) {
      CRM_Core_Error::statusBounce(E::ts('You do not have permission to access this contact.'));
    }

    $this->assign('contactId', $this->_contactId);
    $this->assign('action', $this->_action);
  }

  public function run() {
    $this->preProcess();

    // Get contact display name
    $contact = civicrm_api3('Contact', 'getsingle', [
      'id' => $this->_contactId,
      'return' => ['display_name', 'contact_type'],
    ]);

    $this->assign('displayName', $contact['display_name']);
    $this->assign('contactType', $contact['contact_type']);

    // Get inventory data
    $this->getInventoryData();

    // Get related data
    $this->getRelatedData();

    // Set page title
    CRM_Utils_System::setTitle(E::ts('Inventory - %1', [1 => $contact['display_name']]));

    parent::run();
  }

  /**
   * Get inventory data for the contact.
   */
  private function getInventoryData() {
    // Get current inventory
    $currentInventory = CRM_Inventory_BAO_InventoryProductVariant::getContactInventorySummary($this->_contactId);
    $this->assign('currentInventory', $currentInventory);

    // Get inventory statistics
    $inventoryStats = $this->getInventoryStats();
    $this->assign('inventoryStats', $inventoryStats);

    // Get device history
    $deviceHistory = $this->getDeviceHistory();
    $this->assign('deviceHistory', $deviceHistory);

    // Get warranty information
    $warrantyInfo = $this->getWarrantyInfo();
    $this->assign('warrantyInfo', $warrantyInfo);
  }

  /**
   * Get inventory statistics for the contact.
   *
   * @return array
   */
  private function getInventoryStats() {
    $query = "
      SELECT
        COUNT(*) as total_devices,
        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_devices,
        COUNT(CASE WHEN is_suspended = 1 THEN 1 END) as suspended_devices,
        COUNT(CASE WHEN is_problem = 1 THEN 1 END) as problem_devices,
        COUNT(CASE WHEN status = 'replaced' THEN 1 END) as replaced_devices,
        MIN(created_at) as first_device_date,
        MAX(created_at) as latest_device_date
      FROM civicrm_inventory_product_variant
      WHERE contact_id = %1
    ";

    $params = [1 => [$this->_contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $stats = [];
    if ($dao->fetch()) {
      $stats = [
        'total_devices' => $dao->total_devices,
        'active_devices' => $dao->active_devices,
        'suspended_devices' => $dao->suspended_devices,
        'problem_devices' => $dao->problem_devices,
        'replaced_devices' => $dao->replaced_devices,
        'first_device_date' => $dao->first_device_date,
        'latest_device_date' => $dao->latest_device_date,
      ];
    }

    return $stats;
  }

  /**
   * Get device history for the contact.
   *
   * @return array
   */
  private function getDeviceHistory() {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        p.product_code,
        cl.status_id as change_status,
        cl.created_date as change_date,
        c.display_name as changed_by
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      LEFT JOIN civicrm_inventory_product_changelog cl ON pv.id = cl.product_variant_id
      LEFT JOIN civicrm_contact c ON cl.contact_id = c.id
      WHERE pv.contact_id = %1
      ORDER BY COALESCE(cl.created_date, pv.created_at) DESC
    ";

    $params = [1 => [$this->_contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $history = [];
    while ($dao->fetch()) {
      $history[] = [
        'id' => $dao->id,
        'product_label' => $dao->product_label,
        'product_code' => $dao->product_code,
        'unique_id' => $dao->product_variant_unique_id,
        'phone_number' => $dao->product_variant_phone_number,
        'status' => $dao->status,
        'change_status' => $dao->change_status,
        'change_date' => $dao->change_date ?: $dao->created_at,
        'changed_by' => $dao->changed_by,
        'is_active' => $dao->is_active,
        'is_suspended' => $dao->is_suspended,
        'is_problem' => $dao->is_problem,
        'warranty_start_date' => $dao->warranty_start_date,
        'warranty_end_date' => $dao->warranty_end_date,
      ];
    }

    return $history;
  }

  /**
   * Get warranty information for contact's devices.
   *
   * @return array
   */
  private function getWarrantyInfo() {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        DATEDIFF(pv.warranty_end_date, NOW()) as days_until_expiry,
        CASE
          WHEN pv.warranty_end_date < NOW() THEN 'expired'
          WHEN pv.warranty_end_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 'expiring'
          ELSE 'active'
        END as warranty_status
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      WHERE pv.contact_id = %1
      AND pv.is_active = 1
      AND pv.warranty_end_date IS NOT NULL
      ORDER BY pv.warranty_end_date ASC
    ";

    $params = [1 => [$this->_contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $warranties = [];
    while ($dao->fetch()) {
      $warranties[] = [
        'id' => $dao->id,
        'product_label' => $dao->product_label,
        'unique_id' => $dao->product_variant_unique_id,
        'warranty_start_date' => $dao->warranty_start_date,
        'warranty_end_date' => $dao->warranty_end_date,
        'days_until_expiry' => $dao->days_until_expiry,
        'warranty_status' => $dao->warranty_status,
      ];
    }

    return $warranties;
  }

  /**
   * Get related data (memberships, sales, etc.)
   */
  private function getRelatedData() {
    // Get memberships with inventory
    $membershipsWithInventory = $this->getMembershipsWithInventory();
    $this->assign('membershipsWithInventory', $membershipsWithInventory);

    // Get sales history
    $salesHistory = $this->getSalesHistory();
    $this->assign('salesHistory', $salesHistory);

    // Get replacement history
    $replacementHistory = $this->getReplacementHistory();
    $this->assign('replacementHistory', $replacementHistory);
  }

  /**
   * Get memberships that have inventory associated.
   *
   * @return array
   */
  private function getMembershipsWithInventory() {
    $query = "
      SELECT DISTINCT
        m.*,
        mt.name as membership_type_name,
        COUNT(pv.id) as device_count
      FROM civicrm_membership m
      INNER JOIN civicrm_membership_type mt ON m.membership_type_id = mt.id
      LEFT JOIN civicrm_inventory_product_variant pv ON m.id = pv.membership_id
      WHERE m.contact_id = %1
      AND pv.id IS NOT NULL
      GROUP BY m.id
      ORDER BY m.start_date DESC
    ";

    $params = [1 => [$this->_contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $memberships = [];
    while ($dao->fetch()) {
      $memberships[] = [
        'id' => $dao->id,
        'membership_type_name' => $dao->membership_type_name,
        'start_date' => $dao->start_date,
        'end_date' => $dao->end_date,
        'status_id' => $dao->status_id,
        'device_count' => $dao->device_count,
      ];
    }

    return $memberships;
  }

  /**
   * Get sales history for the contact.
   *
   * @return array
   */
  private function getSalesHistory() {
    $query = "
      SELECT
        s.*,
        COUNT(sd.id) as item_count,
        SUM(sd.purchase_price * sd.product_quantity) as total_value
      FROM civicrm_inventory_sales s
      LEFT JOIN civicrm_inventory_sales_detail sd ON s.id = sd.sales_id
      WHERE s.contact_id = %1
      GROUP BY s.id
      ORDER BY s.sale_date DESC
      LIMIT 10
    ";

    $params = [1 => [$this->_contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $sales = [];
    while ($dao->fetch()) {
      $sales[] = [
        'id' => $dao->id,
        'code' => $dao->code,
        'sale_date' => $dao->sale_date,
        'status_id' => $dao->status_id,
        'item_count' => $dao->item_count,
        'total_value' => $dao->total_value,
      ];
    }

    return $sales;
  }

  /**
   * Get replacement history for the contact.
   *
   * @return array
   */
  private function getReplacementHistory() {
    $query = "
      SELECT
        r.*,
        old_p.label as old_product_label,
        old_pv.product_variant_unique_id as old_unique_id,
        new_p.label as new_product_label,
        new_pv.product_variant_unique_id as new_unique_id
      FROM civicrm_inventory_product_variant_replacement r
      LEFT JOIN civicrm_inventory_product_variant old_pv ON r.old_product_id = old_pv.id
      LEFT JOIN civicrm_inventory_product old_p ON old_pv.product_id = old_p.id
      LEFT JOIN civicrm_inventory_product_variant new_pv ON r.new_product_id = new_pv.id
      LEFT JOIN civicrm_inventory_product new_p ON new_pv.product_id = new_p.id
      WHERE r.contact_id = %1
      ORDER BY r.created_at DESC
    ";

    $params = [1 => [$this->_contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $replacements = [];
    while ($dao->fetch()) {
      $replacements[] = [
        'id' => $dao->id,
        'old_product_label' => $dao->old_product_label,
        'old_unique_id' => $dao->old_unique_id,
        'new_product_label' => $dao->new_product_label,
        'new_unique_id' => $dao->new_unique_id,
        'is_warranty' => $dao->is_warranty,
        'source' => $dao->source,
        'created_at' => $dao->created_at,
        'shipped_on' => $dao->shipped_on,
      ];
    }

    return $replacements;
  }

  /**
   * Get tabs for the contact inventory page.
   *
   * @return array
   */
  public function getTabs() {
    $tabs = [
      'current' => [
        'title' => E::ts('Current Inventory'),
        'class' => 'livePage',
        'id' => 'current-inventory',
      ],
      'history' => [
        'title' => E::ts('History'),
        'class' => 'livePage',
        'id' => 'inventory-history',
      ],
      'warranties' => [
        'title' => E::ts('Warranties'),
        'class' => 'livePage',
        'id' => 'warranties',
      ],
      'sales' => [
        'title' => E::ts('Sales'),
        'class' => 'livePage',
        'id' => 'sales-history',
      ],
    ];

    return $tabs;
  }

}
