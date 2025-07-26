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

class CRM_Inventory_BAO_InventoryProduct extends CRM_Inventory_DAO_InventoryProduct {

  /**
   * Create a new InventoryProduct based on array-data.
   *
   * @param array $params
   *
   * @return CRM_Inventory_DAO_InventoryProduct|NULL
   */
  public static function create($params) {
    $className = 'CRM_Inventory_DAO_InventoryProduct';
    $entityName = 'InventoryProduct';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Get available products for a membership type.
   *
   * @param int $membershipTypeId
   * @return array
   */
  public static function getProductsForMembershipType($membershipTypeId) {
    $query = "
      SELECT p.*, pm.is_product_serialize
      FROM civicrm_inventory_product p
      INNER JOIN civicrm_inventory_product_membership pm ON p.id = pm.product_id
      WHERE pm.membership_type_id = %1
      AND p.is_active = 1
      AND p.is_discontinued = 0
      ORDER BY p.label
    ";

    $params = [1 => [$membershipTypeId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $products = [];
    while ($dao->fetch()) {
      $products[$dao->id] = [
        'id' => $dao->id,
        'label' => $dao->label,
        'product_code' => $dao->product_code,
        'description' => $dao->product_description,
        'current_price' => $dao->current_price,
        'is_serialize' => $dao->is_serialize,
        'quantity_available' => $dao->quantity_available,
        'image_thumbnail' => $dao->image_thumbnail,
      ];
    }

    return $products;
  }

  /**
   * Get product inventory status.
   *
   * @param int $productId
   * @return array
   */
  public static function getInventoryStatus($productId) {
    $query = "
      SELECT
        p.quantity_available,
        p.minimum_quantity_stock_level,
        p.maximum_quantity_stock_level,
        p.reorder_point,
        COUNT(pv.id) as variant_count,
        COUNT(CASE WHEN pv.sales_id IS NULL THEN 1 END) as available_variants
      FROM civicrm_inventory_product p
      LEFT JOIN civicrm_inventory_product_variant pv ON p.id = pv.product_id AND pv.is_active = 1
      WHERE p.id = %1
      GROUP BY p.id
    ";

    $params = [1 => [$productId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    if ($dao->fetch()) {
      return [
        'quantity_available' => $dao->quantity_available,
        'minimum_stock_level' => $dao->minimum_quantity_stock_level,
        'maximum_stock_level' => $dao->maximum_quantity_stock_level,
        'reorder_point' => $dao->reorder_point,
        'variant_count' => $dao->variant_count,
        'available_variants' => $dao->available_variants,
        'needs_reorder' => $dao->available_variants <= $dao->reorder_point,
        'stock_status' => self::getStockStatus($dao->available_variants, $dao->minimum_quantity_stock_level, $dao->maximum_quantity_stock_level),
      ];
    }

    return [];
  }

  /**
   * Get stock status based on current levels.
   *
   * @param int $current
   * @param int $minimum
   * @param int $maximum
   * @return string
   */
  private static function getStockStatus($current, $minimum, $maximum) {
    if ($current <= 0) {
      return 'out_of_stock';
    }
    if ($current <= $minimum) {
      return 'low_stock';
    }
    if ($current >= $maximum) {
      return 'overstock';
    }
    return 'in_stock';
  }

  /**
   * Get products that need reordering.
   *
   * @return array
   */
  public static function getProductsNeedingReorder() {
    $query = "
      SELECT
        p.*,
        COUNT(CASE WHEN pv.sales_id IS NULL AND pv.is_active = 1 THEN 1 END) as available_variants
      FROM civicrm_inventory_product p
      LEFT JOIN civicrm_inventory_product_variant pv ON p.id = pv.product_id
      WHERE p.is_active = 1
      GROUP BY p.id
      HAVING available_variants <= p.reorder_point
      ORDER BY available_variants ASC
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $products = [];
    while ($dao->fetch()) {
      $products[] = [
        'id' => $dao->id,
        'label' => $dao->label,
        'product_code' => $dao->product_code,
        'available_variants' => $dao->available_variants,
        'reorder_point' => $dao->reorder_point,
        'minimum_stock_level' => $dao->minimum_quantity_stock_level,
      ];
    }

    return $products;
  }

  /**
   * Update product inventory after sale.
   *
   * @param int $productId
   * @param int $quantity
   * @return bool
   */
  public static function updateInventoryAfterSale($productId, $quantity = 1) {
    $product = new CRM_Inventory_DAO_InventoryProduct();
    $product->id = $productId;

    if ($product->find(TRUE)) {
      $product->quantity_available = max(0, $product->quantity_available - $quantity);
      return $product->save();
    }

    return FALSE;
  }

  /**
   * Get product categories with product counts.
   *
   * @return array
   */
  public static function getProductCategoriesWithCounts() {
    $query = "
      SELECT
        c.*,
        COUNT(p.id) as product_count,
        COUNT(CASE WHEN p.is_active = 1 THEN 1 END) as active_product_count
      FROM civicrm_inventory_category c
      LEFT JOIN civicrm_inventory_product p ON c.id = p.product_category_id
      GROUP BY c.id
      ORDER BY c.title
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $categories = [];
    while ($dao->fetch()) {
      $categories[$dao->id] = [
        'id' => $dao->id,
        'title' => $dao->title,
        'slug' => $dao->slug,
        'product_count' => $dao->product_count,
        'active_product_count' => $dao->active_product_count,
      ];
    }

    return $categories;
  }

  /**
   * Search products by various criteria.
   *
   * @param array $params
   * @return array
   */
  public static function searchProducts($params = []) {
    $whereClause = "WHERE p.is_active = 1";
    $sqlParams = [];
    $paramCount = 1;

    if (!empty($params['search_term'])) {
      $whereClause .= " AND (p.label LIKE %{$paramCount} OR p.product_code LIKE %{$paramCount} OR p.product_description LIKE %{$paramCount})";
      $searchTerm = '%' . $params['search_term'] . '%';
      $sqlParams[$paramCount] = [$searchTerm, 'String'];
      $paramCount++;
    }

    if (!empty($params['category_id'])) {
      $whereClause .= " AND p.product_category_id = %{$paramCount}";
      $sqlParams[$paramCount] = [$params['category_id'], 'Integer'];
      $paramCount++;
    }

    if (!empty($params['membership_type_id'])) {
      $whereClause .= " AND EXISTS (SELECT 1 FROM civicrm_inventory_product_membership pm WHERE pm.product_id = p.id AND pm.membership_type_id = %{$paramCount})";
      $sqlParams[$paramCount] = [$params['membership_type_id'], 'Integer'];
      $paramCount++;
    }

    $query = "
      SELECT
        p.*,
        c.title as category_title,
        COUNT(pv.id) as variant_count,
        COUNT(CASE WHEN pv.sales_id IS NULL AND pv.is_active = 1 THEN 1 END) as available_variants
      FROM civicrm_inventory_product p
      LEFT JOIN civicrm_inventory_category c ON p.product_category_id = c.id
      LEFT JOIN civicrm_inventory_product_variant pv ON p.id = pv.product_id
      {$whereClause}
      GROUP BY p.id
      ORDER BY p.label
    ";

    $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);

    $products = [];
    while ($dao->fetch()) {
      $products[] = [
        'id' => $dao->id,
        'label' => $dao->label,
        'product_code' => $dao->product_code,
        'description' => $dao->product_description,
        'current_price' => $dao->current_price,
        'category_title' => $dao->category_title,
        'variant_count' => $dao->variant_count,
        'available_variants' => $dao->available_variants,
        'image_thumbnail' => $dao->image_thumbnail,
        'stock_status' => self::getStockStatus($dao->available_variants, $dao->minimum_quantity_stock_level, $dao->maximum_quantity_stock_level),
      ];
    }

    return $products;
  }

  /**
   * Delete a product and all related data.
   *
   * @param int $id
   * @return bool
   */
  public static function deleteProduct($id) {
    // Check if product has any sales
    $salesCount = CRM_Core_DAO::singleValueQuery(
      "SELECT COUNT(*) FROM civicrm_inventory_sales_detail WHERE product_variant_id IN (SELECT id FROM civicrm_inventory_product_variant WHERE product_id = %1)",
      [1 => [$id, 'Integer']]
    );

    if ($salesCount > 0) {
      throw new CRM_Core_Exception(E::ts('Cannot delete product with existing sales records.'));
    }

    // Delete related data
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_inventory_product_membership WHERE product_id = %1", [1 => [$id, 'Integer']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_inventory_product_meta WHERE product_id = %1", [1 => [$id, 'Integer']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_inventory_product_variant WHERE product_id = %1", [1 => [$id, 'Integer']]);

    // Delete the product
    $product = new CRM_Inventory_DAO_InventoryProduct();
    $product->id = $id;
    return $product->delete();
  }

}
