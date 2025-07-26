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
 * Class CRM_Inventory_Page_Dashboard
 *
 * Main dashboard page for inventory management
 */
class CRM_Inventory_Page_Dashboard extends CRM_Core_Page {

  public function run() {
    // Check permissions
    if (!CRM_Core_Permission::check('access inventory')) {
      CRM_Core_Error::statusBounce(E::ts('You do not have permission to access inventory management.'));
    }

    // Get dashboard statistics
    $this->getDashboardStats();

    // Get recent activity
    $this->getRecentActivity();

    // Get alerts and notifications
    $this->getAlerts();

    // Set page title
    CRM_Utils_System::setTitle(E::ts('Inventory Management Dashboard'));

    parent::run();
  }

  /**
   * Get dashboard statistics.
   */
  private function getDashboardStats() {
    // Product statistics
    $productStats = $this->getProductStats();
    $this->assign('productStats', $productStats);

    // Sales statistics
    $salesStats = $this->getSalesStats();
    $this->assign('salesStats', $salesStats);

    // Warehouse statistics
    $warehouseStats = $this->getWarehouseStats();
    $this->assign('warehouseStats', $warehouseStats);
  }

  /**
   * Get product statistics.
   *
   * @return array
   */
  private function getProductStats() {
    $query = "
      SELECT
        COUNT(DISTINCT p.id) as total_products,
        COUNT(DISTINCT CASE WHEN p.is_active = 1 THEN p.id END) as active_products,
        COUNT(pv.id) as total_variants,
        COUNT(CASE WHEN pv.contact_id IS NULL AND pv.sales_id IS NULL AND pv.is_active = 1 THEN 1 END) as available_variants,
        COUNT(CASE WHEN pv.contact_id IS NOT NULL AND pv.is_active = 1 THEN 1 END) as assigned_variants,
        COUNT(CASE WHEN pv.is_suspended = 1 THEN 1 END) as suspended_variants,
        COUNT(CASE WHEN pv.is_problem = 1 THEN 1 END) as problem_variants
      FROM civicrm_inventory_product p
      LEFT JOIN civicrm_inventory_product_variant pv ON p.id = pv.product_id
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $stats = [];
    if ($dao->fetch()) {
      $stats = [
        'total_products' => $dao->total_products,
        'active_products' => $dao->active_products,
        'total_variants' => $dao->total_variants,
        'available_variants' => $dao->available_variants,
        'assigned_variants' => $dao->assigned_variants,
        'suspended_variants' => $dao->suspended_variants,
        'problem_variants' => $dao->problem_variants,
      ];
    }

    return $stats;
  }

  /**
   * Get sales statistics.
   *
   * @return array
   */
  private function getSalesStats() {
    $monthlyStats = CRM_Inventory_BAO_InventorySales::getSalesStatistics('month');
    $weeklyStats = CRM_Inventory_BAO_InventorySales::getSalesStatistics('week');
    $todayStats = CRM_Inventory_BAO_InventorySales::getSalesStatistics('today');

    return [
      'monthly' => $monthlyStats,
      'weekly' => $weeklyStats,
      'today' => $todayStats,
    ];
  }

  /**
   * Get warehouse statistics.
   *
   * @return array
   */
  private function getWarehouseStats() {
    $query = "
      SELECT
        COUNT(*) as total_warehouses,
        COUNT(CASE WHEN is_refrigerated = 1 THEN 1 END) as refrigerated_warehouses
      FROM civicrm_inventory_warehouse
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $stats = [];
    if ($dao->fetch()) {
      $stats = [
        'total_warehouses' => $dao->total_warehouses,
        'refrigerated_warehouses' => $dao->refrigerated_warehouses,
      ];
    }

    return $stats;
  }

  /**
   * Get recent activity.
   */
  private function getRecentActivity() {
    // Recent sales
    $recentSales = $this->getRecentSales();
    $this->assign('recentSales', $recentSales);

    // Recent assignments
    $recentAssignments = $this->getRecentAssignments();
    $this->assign('recentAssignments', $recentAssignments);

    // Recent changelog entries
    $recentChanges = $this->getRecentChanges();
    $this->assign('recentChanges', $recentChanges);
  }

  /**
   * Get recent sales.
   *
   * @return array
   */
  private function getRecentSales() {
    $query = "
      SELECT
        s.*,
        c.display_name,
        COUNT(sd.id) as item_count
      FROM civicrm_inventory_sales s
      LEFT JOIN civicrm_contact c ON s.contact_id = c.id
      LEFT JOIN civicrm_inventory_sales_detail sd ON s.id = sd.sales_id
      WHERE s.sale_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
      GROUP BY s.id
      ORDER BY s.sale_date DESC
      LIMIT 10
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $sales = [];
    while ($dao->fetch()) {
      $sales[] = [
        'id' => $dao->id,
        'code' => $dao->code,
        'contact_name' => $dao->display_name,
        'sale_date' => $dao->sale_date,
        'status_id' => $dao->status_id,
        'item_count' => $dao->item_count,
      ];
    }

    return $sales;
  }

  /**
   * Get recent assignments.
   *
   * @return array
   */
  private function getRecentAssignments() {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        c.display_name
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      LEFT JOIN civicrm_contact c ON pv.contact_id = c.id
      WHERE pv.contact_id IS NOT NULL
      AND pv.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
      ORDER BY pv.updated_at DESC
      LIMIT 10
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $assignments = [];
    while ($dao->fetch()) {
      $assignments[] = [
        'id' => $dao->id,
        'product_label' => $dao->product_label,
        'unique_id' => $dao->product_variant_unique_id,
        'contact_name' => $dao->display_name,
        'assigned_date' => $dao->updated_at,
        'status' => $dao->status,
      ];
    }

    return $assignments;
  }

  /**
   * Get recent changelog entries.
   *
   * @return array
   */
  private function getRecentChanges() {
    $query = "
      SELECT
        cl.*,
        pv.product_variant_unique_id,
        p.label as product_label,
        c.display_name as modified_by_name
      FROM civicrm_inventory_product_changelog cl
      LEFT JOIN civicrm_inventory_product_variant pv ON cl.product_variant_id = pv.id
      LEFT JOIN civicrm_inventory_product p ON pv.product_id = p.id
      LEFT JOIN civicrm_contact c ON cl.contact_id = c.id
      WHERE cl.created_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
      ORDER BY cl.created_date DESC
      LIMIT 10
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $changes = [];
    while ($dao->fetch()) {
      $changes[] = [
        'id' => $dao->id,
        'product_label' => $dao->product_label,
        'unique_id' => $dao->product_variant_unique_id,
        'status_id' => $dao->status_id,
        'created_date' => $dao->created_date,
        'modified_by_name' => $dao->modified_by_name,
      ];
    }

    return $changes;
  }

  /**
   * Get alerts and notifications.
   */
  private function getAlerts() {
    $alerts = [];

    // Low stock alerts
    $lowStockProducts = CRM_Inventory_BAO_InventoryProduct::getProductsNeedingReorder();
    if (!empty($lowStockProducts)) {
      $alerts[] = [
        'type' => 'warning',
        'title' => E::ts('Low Stock Alert'),
        'message' => E::ts('%1 product(s) need reordering', [1 => count($lowStockProducts)]),
        'action_url' => CRM_Utils_System::url('civicrm/inventory/products', 'reset=1&low_stock=1'),
        'action_text' => E::ts('View Products'),
      ];
    }

    // Devices needing replacement
    $devicesNeedingReplacement = CRM_Inventory_BAO_InventoryProductVariant::getDevicesNeedingReplacement();
    if (!empty($devicesNeedingReplacement)) {
      $alerts[] = [
        'type' => 'error',
        'title' => E::ts('Devices Need Replacement'),
        'message' => E::ts('%1 device(s) are marked as problematic', [1 => count($devicesNeedingReplacement)]),
        'action_url' => CRM_Utils_System::url('civicrm/inventory/devices', 'reset=1&status=problem'),
        'action_text' => E::ts('View Devices'),
      ];
    }

    // Expiring warranties
    $expiringWarranties = CRM_Inventory_BAO_InventoryProductVariant::getExpiringWarranties(30);
    if (!empty($expiringWarranties)) {
      $alerts[] = [
        'type' => 'info',
        'title' => E::ts('Expiring Warranties'),
        'message' => E::ts('%1 device warranties expire within 30 days', [1 => count($expiringWarranties)]),
        'action_url' => CRM_Utils_System::url('civicrm/inventory/warranties', 'reset=1&expiring=1'),
        'action_text' => E::ts('View Warranties'),
      ];
    }

    // Sales needing assignment
    $salesNeedingAssignment = CRM_Inventory_BAO_InventorySales::getSalesNeedingAssignment();
    if (!empty($salesNeedingAssignment)) {
      $alerts[] = [
        'type' => 'warning',
        'title' => E::ts('Sales Need Assignment'),
        'message' => E::ts('%1 sale(s) need product assignment', [1 => count($salesNeedingAssignment)]),
        'action_url' => CRM_Utils_System::url('civicrm/inventory/sales', 'reset=1&needs_assignment=1'),
        'action_text' => E::ts('View Sales'),
      ];
    }

    $this->assign('alerts', $alerts);
  }

}
