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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

use CRM_Inventory_ExtensionUtil as E;

class CRM_Inventory_BAO_InventorySales extends CRM_Inventory_DAO_InventorySales {

  /**
   * Create a new InventorySales based on array-data.
   *
   * @param array $params
   *
   * @return CRM_Inventory_DAO_InventorySales|NULL
   */
  public static function create($params) {
    $className = 'CRM_Inventory_DAO_InventorySales';
    $entityName = 'InventorySales';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);

    // Set defaults
    if (empty($params['id'])) {
      $instance->sale_date = date('Y-m-d H:i:s');
      $instance->status_id = 'placed';
      $instance->code = self::generateOrderCode();
    }

    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Generate unique order code.
   *
   * @return string
   */
  private static function generateOrderCode() {
    do {
      $code = 'ORD-' . strtoupper(uniqid());
      $exists = CRM_Core_DAO::singleValueQuery(
        "SELECT COUNT(*) FROM civicrm_inventory_sales WHERE code = %1",
        [1 => [$code, 'String']]
      );
    } while ($exists > 0);

    return $code;
  }

  /**
   * Create sale from contribution and membership.
   *
   * @param int $contributionId
   * @param int $contactId
   * @param int $membershipId
   * @param array $products
   * @return CRM_Inventory_DAO_InventorySales|FALSE
   */
  public static function createFromContribution($contributionId, $contactId, $membershipId = NULL, $products = []) {
    // Create sale record
    $saleParams = [
      'contact_id' => $contactId,
      'contribution_id' => $contributionId,
      'status_id' => 'placed',
      'is_shipping_required' => 1,
      'needs_assignment' => 1,
    ];

    $sale = self::create($saleParams);

    if ($sale && !empty($products)) {
      foreach ($products as $product) {
        self::addSaleDetail($sale->id, $product, $membershipId);
      }
    }

    return $sale;
  }

  /**
   * Add sale detail item.
   *
   * @param int $saleId
   * @param array $product
   * @param int $membershipId
   * @return bool
   */
  public static function addSaleDetail($saleId, $product, $membershipId = NULL) {
    $params = [
      'sales_id' => $saleId,
      'product_variant_id' => $product['variant_id'] ?? NULL,
      'product_quantity' => $product['quantity'] ?? 1,
      'warehouse_id' => $product['warehouse_id'] ?? NULL,
      'purchase_price' => $product['price'] ?? 0,
      'product_title' => $product['title'] ?? '',
      'product_sub_title' => $product['subtitle'] ?? '',
      'type' => $product['type'] ?? 'device',
      'membership_id' => $membershipId,
      'contribution_id' => $product['contribution_id'] ?? NULL,
    ];

    $saleDetail = new CRM_Inventory_DAO_InventorySalesDetail();
    $saleDetail->copyValues($params);
    return $saleDetail->save();
  }

  /**
   * Get sale with details.
   *
   * @param int $saleId
   * @return array
   */
  public static function getSaleWithDetails($saleId) {
    // Get sale info
    $query = "
      SELECT
        s.*,
        c.display_name,
        con.total_amount,
        con.contribution_status_id
      FROM civicrm_inventory_sales s
      LEFT JOIN civicrm_contact c ON s.contact_id = c.id
      LEFT JOIN civicrm_contribution con ON s.contribution_id = con.id
      WHERE s.id = %1
    ";

    $params = [1 => [$saleId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $sale = [];
    if ($dao->fetch()) {
      $sale = [
        'id' => $dao->id,
        'code' => $dao->code,
        'contact_id' => $dao->contact_id,
        'contact_name' => $dao->display_name,
        'sale_date' => $dao->sale_date,
        'status_id' => $dao->status_id,
        'is_paid' => $dao->is_paid,
        'is_fulfilled' => $dao->is_fulfilled,
        'is_shipping_required' => $dao->is_shipping_required,
        'needs_assignment' => $dao->needs_assignment,
        'has_assignment' => $dao->has_assignment,
        'contribution_amount' => $dao->total_amount,
        'contribution_status' => $dao->contribution_status_id,
      ];
    }

    // Get sale details
    $detailQuery = "
      SELECT
        sd.*,
        p.label as product_label,
        p.product_code,
        pv.product_variant_unique_id
      FROM civicrm_inventory_sales_detail sd
      LEFT JOIN civicrm_inventory_product_variant pv ON sd.product_variant_id = pv.id
      LEFT JOIN civicrm_inventory_product p ON pv.product_id = p.id
      WHERE sd.sales_id = %1
    ";

    $detailDao = CRM_Core_DAO::executeQuery($detailQuery, $params);

    $sale['details'] = [];
    while ($detailDao->fetch()) {
      $sale['details'][] = [
        'id' => $detailDao->id,
        'product_title' => $detailDao->product_title ?: $detailDao->product_label,
        'product_code' => $detailDao->product_code,
        'unique_id' => $detailDao->product_variant_unique_id,
        'quantity' => $detailDao->product_quantity,
        'price' => $detailDao->purchase_price,
        'type' => $detailDao->type,
        'membership_id' => $detailDao->membership_id,
      ];
    }

    return $sale;
  }

  /**
   * Update sale status.
   *
   * @param int $saleId
   * @param string $status
   * @return bool
   */
  public static function updateStatus($saleId, $status) {
    $params = [
      'id' => $saleId,
      'status_id' => $status,
    ];

    // Set additional fields based on status
    switch ($status) {
      case 'shipped':
        $params['is_fulfilled'] = 1;
        break;
      case 'completed':
        $params['is_fulfilled'] = 1;
        $params['is_paid'] = 1;
        break;
    }

    $sale = self::create($params);
    return $sale ? TRUE : FALSE;
  }

  /**
   * Assign products to sale.
   *
   * @param int $saleId
   * @param array $assignments
   * @return bool
   */
  public static function assignProducts($saleId, $assignments) {
    foreach ($assignments as $assignment) {
      $saleDetailId = $assignment['sale_detail_id'];
      $variantId = $assignment['variant_id'];

      // Update sale detail with variant
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_inventory_sales_detail
        SET product_variant_id = %1
        WHERE id = %2
      ", [
        1 => [$variantId, 'Integer'],
        2 => [$saleDetailId, 'Integer'],
      ]);

      // Update variant with sale
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_inventory_product_variant
        SET sales_id = %1, status = 'sold'
        WHERE id = %2
      ", [
        1 => [$saleId, 'Integer'],
        2 => [$variantId, 'Integer'],
      ]);
    }

    // Update sale as having assignments
    self::create([
      'id' => $saleId,
      'has_assignment' => 1,
      'needs_assignment' => 0,
    ]);

    return TRUE;
  }

  /**
   * Get sales needing assignment.
   *
   * @return array
   */
  public static function getSalesNeedingAssignment() {
    $query = "
      SELECT
        s.*,
        c.display_name,
        COUNT(sd.id) as item_count
      FROM civicrm_inventory_sales s
      LEFT JOIN civicrm_contact c ON s.contact_id = c.id
      LEFT JOIN civicrm_inventory_sales_detail sd ON s.id = sd.sales_id
      WHERE s.needs_assignment = 1
      AND s.status_id IN ('placed', 'processing')
      GROUP BY s.id
      ORDER BY s.sale_date ASC
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
   * Get sales statistics.
   *
   * @param string $period
   * @return array
   */
  public static function getSalesStatistics($period = 'month') {
    $dateCondition = '';
    switch ($period) {
      case 'today':
        $dateCondition = "AND DATE(s.sale_date) = CURDATE()";
        break;
      case 'week':
        $dateCondition = "AND s.sale_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        break;
      case 'month':
        $dateCondition = "AND s.sale_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
      case 'year':
        $dateCondition = "AND s.sale_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
    }

    $query = "
      SELECT
        COUNT(*) as total_sales,
        COUNT(CASE WHEN s.status_id = 'completed' THEN 1 END) as completed_sales,
        COUNT(CASE WHEN s.status_id = 'shipped' THEN 1 END) as shipped_sales,
        COUNT(CASE WHEN s.status_id = 'placed' THEN 1 END) as pending_sales,
        SUM(sd.purchase_price * sd.product_quantity) as total_value
      FROM civicrm_inventory_sales s
      LEFT JOIN civicrm_inventory_sales_detail sd ON s.id = sd.sales_id
      WHERE 1=1 {$dateCondition}
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $stats = [];
    if ($dao->fetch()) {
      $stats = [
        'total_sales' => $dao->total_sales,
        'completed_sales' => $dao->completed_sales,
        'shipped_sales' => $dao->shipped_sales,
        'pending_sales' => $dao->pending_sales,
        'total_value' => $dao->total_value,
      ];
    }

    return $stats;
  }

  /**
   * Create sale from membership renewal.
   *
   * @param int $membershipId
   * @param int $contactId
   * @param int $contributionId
   * @return CRM_Inventory_DAO_InventorySales|FALSE
   */
  public static function createFromMembershipRenewal($membershipId, $contactId, $contributionId = NULL) {
    // Check if this membership has associated products
    $products = CRM_Core_DAO::executeQuery("
      SELECT DISTINCT p.id, p.label, p.current_price
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      WHERE pv.membership_id = %1 AND pv.is_active = 1
    ", [1 => [$membershipId, 'Integer']]);

    if ($products->N == 0) {
      return FALSE;
    }

    $saleParams = [
      'contact_id' => $contactId,
      'contribution_id' => $contributionId,
      'status_id' => 'renewal',
      'is_shipping_required' => 0,
      'needs_assignment' => 0,
    ];

    return self::create($saleParams);
  }

}
