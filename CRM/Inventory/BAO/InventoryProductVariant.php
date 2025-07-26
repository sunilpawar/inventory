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

class CRM_Inventory_BAO_InventoryProductVariant extends CRM_Inventory_DAO_InventoryProductVariant {

  /**
   * Create a new InventoryProductVariant based on array-data.
   *
   * @param array $params
   *
   * @return CRM_Inventory_DAO_InventoryProductVariant|NULL
   */
  public static function create($params) {
    $className = 'CRM_Inventory_DAO_InventoryProductVariant';
    $entityName = 'InventoryProductVariant';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);

    // Set defaults
    if (empty($params['id'])) {
      $instance->created_at = date('Y-m-d H:i:s');
      $instance->is_active = 1;
      $instance->is_primary = 0;
      $instance->is_suspended = 0;
      $instance->is_problem = 0;
    }

    $instance->updated_at = date('Y-m-d H:i:s');
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Get contact inventory count.
   *
   * @param int $contactId
   * @return int
   */
  public static function getContactInventoryCount($contactId) {
    $query = "
      SELECT COUNT(*)
      FROM civicrm_inventory_product_variant pv
      WHERE pv.contact_id = %1 AND pv.is_active = 1
    ";

    return CRM_Core_DAO::singleValueQuery($query, [1 => [$contactId, 'Integer']]);
  }

  /**
   * Get contact inventory summary.
   *
   * @param int $contactId
   * @return array
   */
  public static function getContactInventorySummary($contactId) {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        p.product_code,
        p.current_price,
        m.status_id as membership_status,
        m.start_date as membership_start_date,
        m.end_date as membership_end_date
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      LEFT JOIN civicrm_membership m ON pv.membership_id = m.id
      WHERE pv.contact_id = %1 AND pv.is_active = 1
      ORDER BY pv.created_at DESC
    ";

    $params = [1 => [$contactId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $inventory = [];
    while ($dao->fetch()) {
      $inventory[] = [
        'id' => $dao->id,
        'product_label' => $dao->product_label,
        'product_code' => $dao->product_code,
        'unique_id' => $dao->product_variant_unique_id,
        'phone_number' => $dao->product_variant_phone_number,
        'status' => $dao->status,
        'is_suspended' => $dao->is_suspended,
        'is_problem' => $dao->is_problem,
        'warranty_start_date' => $dao->warranty_start_date,
        'warranty_end_date' => $dao->warranty_end_date,
        'membership_status' => $dao->membership_status,
        'membership_start_date' => $dao->membership_start_date,
        'membership_end_date' => $dao->membership_end_date,
        'created_at' => $dao->created_at,
      ];
    }

    return $inventory;
  }

  /**
   * Assign product variant to contact/membership.
   *
   * @param int $productId
   * @param int $contactId
   * @param int $membershipId
   * @param array $additionalParams
   * @return CRM_Inventory_DAO_InventoryProductVariant|FALSE
   */
  public static function assignToContact($productId, $contactId, $membershipId = NULL, $additionalParams = []) {
    // Find available product variant
    $query = "
      SELECT id FROM civicrm_inventory_product_variant
      WHERE product_id = %1
      AND contact_id IS NULL
      AND sales_id IS NULL
      AND is_active = 1
      ORDER BY created_at ASC
      LIMIT 1
    ";

    $variantId = CRM_Core_DAO::singleValueQuery($query, [1 => [$productId, 'Integer']]);

    if (!$variantId) {
      return FALSE;
    }

    // Update the variant
    $params = [
        'id' => $variantId,
        'contact_id' => $contactId,
        'membership_id' => $membershipId,
        'warranty_start_date' => date('Y-m-d H:i:s'),
        'status' => 'assigned',
      ] + $additionalParams;

    // Set warranty end date (1 year from start)
    if (empty($params['warranty_end_date'])) {
      $params['warranty_end_date'] = date('Y-m-d H:i:s', strtotime('+1 year'));
    }

    return self::create($params);
  }

  /**
   * Get available product variants for a product.
   *
   * @param int $productId
   * @return array
   */
  public static function getAvailableVariants($productId) {
    $query = "
      SELECT pv.*, p.label as product_label
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      WHERE pv.product_id = %1
      AND pv.contact_id IS NULL
      AND pv.sales_id IS NULL
      AND pv.is_active = 1
      ORDER BY pv.created_at ASC
    ";

    $params = [1 => [$productId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $variants = [];
    while ($dao->fetch()) {
      $variants[] = [
        'id' => $dao->id,
        'unique_id' => $dao->product_variant_unique_id,
        'status' => $dao->status,
        'created_at' => $dao->created_at,
        'product_label' => $dao->product_label,
      ];
    }

    return $variants;
  }

  /**
   * Update device status for batch processing.
   *
   * @param array $deviceIds
   * @param string $status
   * @param int $batchId
   * @return bool
   */
  public static function updateDeviceStatus($deviceIds, $status, $batchId = NULL) {
    if (empty($deviceIds)) {
      return FALSE;
    }

    $deviceIdList = implode(',', array_map('intval', $deviceIds));

    // Update device status
    $query = "
      UPDATE civicrm_inventory_product_variant
      SET status = %1, updated_at = NOW()
      WHERE id IN ($deviceIdList)
    ";

    CRM_Core_DAO::executeQuery($query, [1 => [$status, 'String']]);

    // Create changelog entries
    foreach ($deviceIds as $deviceId) {
      CRM_Core_DAO::executeQuery("
        INSERT INTO civicrm_inventory_product_changelog
        (product_variant_id, status_id, created_date, batch_id)
        VALUES (%1, %2, NOW(), %3)
      ", [
        1 => [$deviceId, 'Integer'],
        2 => [$status, 'String'],
        3 => [$batchId, 'Integer'],
      ]);
    }

    return TRUE;
  }

  /**
   * Suspend device.
   *
   * @param int $variantId
   * @param string $reason
   * @return bool
   */
  public static function suspendDevice($variantId, $reason = '') {
    $params = [
      'id' => $variantId,
      'is_suspended' => 1,
      'status' => 'suspended',
    ];

    $variant = self::create($params);

    if ($variant) {
      // Create changelog entry
      CRM_Core_DAO::executeQuery("
        INSERT INTO civicrm_inventory_product_changelog
        (product_variant_id, status_id, created_date)
        VALUES (%1, 'SUSPEND', NOW())
      ", [1 => [$variantId, 'Integer']]);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Reactivate suspended device.
   *
   * @param int $variantId
   * @return bool
   */
  public static function reactivateDevice($variantId) {
    $params = [
      'id' => $variantId,
      'is_suspended' => 0,
      'status' => 'active',
    ];

    $variant = self::create($params);

    if ($variant) {
      // Create changelog entry
      CRM_Core_DAO::executeQuery("
        INSERT INTO civicrm_inventory_product_changelog
        (product_variant_id, status_id, created_date)
        VALUES (%1, 'REACTIVATE', NOW())
      ", [1 => [$variantId, 'Integer']]);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Get devices needing replacement.
   *
   * @return array
   */
  public static function getDevicesNeedingReplacement() {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        c.display_name,
        m.status_id as membership_status
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      LEFT JOIN civicrm_contact c ON pv.contact_id = c.id
      LEFT JOIN civicrm_membership m ON pv.membership_id = m.id
      WHERE (pv.is_problem = 1 OR pv.status = 'defective')
      AND pv.is_active = 1
      ORDER BY pv.updated_at DESC
    ";

    $dao = CRM_Core_DAO::executeQuery($query);

    $devices = [];
    while ($dao->fetch()) {
      $devices[] = [
        'id' => $dao->id,
        'unique_id' => $dao->product_variant_unique_id,
        'product_label' => $dao->product_label,
        'contact_name' => $dao->display_name,
        'phone_number' => $dao->product_variant_phone_number,
        'status' => $dao->status,
        'membership_status' => $dao->membership_status,
        'updated_at' => $dao->updated_at,
      ];
    }

    return $devices;
  }

  /**
   * Create replacement device entry.
   *
   * @param int $oldDeviceId
   * @param int $newDeviceId
   * @param int $contactId
   * @param bool $isWarranty
   * @param string $source
   * @return bool
   */
  public static function createReplacement($oldDeviceId, $newDeviceId, $contactId, $isWarranty = FALSE, $source = 'manual') {
    // Create replacement record
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_inventory_product_variant_replacement
      (contact_id, old_product_id, new_product_id, created_at, is_warranty, source)
      VALUES (%1, %2, %3, NOW(), %4, %5)
    ", [
      1 => [$contactId, 'Integer'],
      2 => [$oldDeviceId, 'Integer'],
      3 => [$newDeviceId, 'Integer'],
      4 => [$isWarranty ? 1 : 0, 'Integer'],
      5 => [$source, 'String'],
    ]);

    // Update old device
    self::create([
      'id' => $oldDeviceId,
      'is_replaced' => 1,
      'replaced_date' => date('Y-m-d H:i:s'),
      'status' => 'replaced',
    ]);

    // Update new device
    $oldDevice = new CRM_Inventory_DAO_InventoryProductVariant();
    $oldDevice->id = $oldDeviceId;
    $oldDevice->find(TRUE);

    self::create([
      'id' => $newDeviceId,
      'contact_id' => $contactId,
      'membership_id' => $oldDevice->membership_id,
      'replaced_product_id' => $oldDeviceId,
      'is_replaced' => 0,
      'status' => 'active',
      'warranty_start_date' => date('Y-m-d H:i:s'),
      'warranty_end_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
    ]);

    return TRUE;
  }

  /**
   * Get expired warranties.
   *
   * @param int $days Number of days to look ahead
   * @return array
   */
  public static function getExpiringWarranties($days = 30) {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        c.display_name,
        DATEDIFF(pv.warranty_end_date, NOW()) as days_until_expiry
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      LEFT JOIN civicrm_contact c ON pv.contact_id = c.id
      WHERE pv.warranty_end_date IS NOT NULL
      AND pv.warranty_end_date >= NOW()
      AND pv.warranty_end_date <= DATE_ADD(NOW(), INTERVAL %1 DAY)
      AND pv.is_active = 1
      ORDER BY pv.warranty_end_date ASC
    ";

    $params = [1 => [$days, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $warranties = [];
    while ($dao->fetch()) {
      $warranties[] = [
        'id' => $dao->id,
        'unique_id' => $dao->product_variant_unique_id,
        'product_label' => $dao->product_label,
        'contact_name' => $dao->display_name,
        'warranty_end_date' => $dao->warranty_end_date,
        'days_until_expiry' => $dao->days_until_expiry,
      ];
    }

    return $warranties;
  }

}
