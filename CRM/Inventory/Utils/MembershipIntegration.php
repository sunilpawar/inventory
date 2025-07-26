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
 * Class CRM_Inventory_Utils_MembershipIntegration
 *
 * Handles integration between inventory and membership systems
 */
class CRM_Inventory_Utils_MembershipIntegration {

  /**
   * Build membership form with product selection.
   *
   * @param CRM_Core_Form $form
   */
  public static function buildMembershipForm(&$form) {
    $membershipTypeId = $form->getVar('_memType');

    if (!$membershipTypeId) {
      return;
    }

    // Get available products for this membership type
    $products = CRM_Inventory_BAO_InventoryProduct::getProductsForMembershipType($membershipTypeId);

    if (empty($products)) {
      return;
    }

    // Add product selection fields
    $productOptions = [];
    foreach ($products as $productId => $product) {
      $available = count(CRM_Inventory_BAO_InventoryProductVariant::getAvailableVariants($productId));
      if ($available > 0) {
        $label = $product['label'] . ' (' . $available . ' available)';
        if ($product['current_price'] > 0) {
          $label .= ' - $' . number_format($product['current_price'], 2);
        }
        $productOptions[$productId] = $label;
      }
    }

    if (!empty($productOptions)) {
      $form->add('select', 'inventory_product_id',
        E::ts('Select Product'),
        ['' => E::ts('- none -')] + $productOptions,
        FALSE,
        ['class' => 'crm-select2']
      );

      $form->add('text', 'inventory_phone_number',
        E::ts('Phone Number'),
        ['maxlength' => 20]
      );

      // Add template resources
      CRM_Core_Region::instance('page-body')->add([
        'template' => 'CRM/Inventory/Form/MembershipProductSelection.tpl',
      ]);
    }
  }

  /**
   * Process membership form submission.
   *
   * @param CRM_Core_Form $form
   */
  public static function postProcessMembership(&$form) {
    $values = $form->exportValues();
    $membershipId = $form->getVar('_id');
    $contactId = $form->getVar('_contactId');

    if (empty($values['inventory_product_id']) || empty($membershipId)) {
      return;
    }

    $productId = $values['inventory_product_id'];
    $phoneNumber = CRM_Utils_Array::value('inventory_phone_number', $values);

    // Assign product to contact/membership
    $additionalParams = [];
    if ($phoneNumber) {
      $additionalParams['product_variant_phone_number'] = $phoneNumber;
    }

    $variant = CRM_Inventory_BAO_InventoryProductVariant::assignToContact(
      $productId,
      $contactId,
      $membershipId,
      $additionalParams
    );

    if ($variant) {
      CRM_Core_Session::setStatus(
        E::ts('Product has been assigned to this membership.'),
        E::ts('Product Assigned'),
        'success'
      );

      // Update product inventory
      CRM_Inventory_BAO_InventoryProduct::updateInventoryAfterSale($productId);
    } else {
      CRM_Core_Session::setStatus(
        E::ts('No available products to assign.'),
        E::ts('Assignment Failed'),
        'error'
      );
    }
  }

  /**
   * Get membership type product configuration.
   *
   * @param int $membershipTypeId
   * @return array
   */
  public static function getMembershipTypeProducts($membershipTypeId) {
    $query = "
      SELECT
        pm.*,
        p.label,
        p.current_price,
        p.is_serialize,
        COUNT(pv.id) as total_variants,
        COUNT(CASE WHEN pv.contact_id IS NULL AND pv.sales_id IS NULL THEN 1 END) as available_variants
      FROM civicrm_inventory_product_membership pm
      INNER JOIN civicrm_inventory_product p ON pm.product_id = p.id
      LEFT JOIN civicrm_inventory_product_variant pv ON p.id = pv.product_id AND pv.is_active = 1
      WHERE pm.membership_type_id = %1
      GROUP BY pm.id
      ORDER BY p.label
    ";

    $params = [1 => [$membershipTypeId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $products = [];
    while ($dao->fetch()) {
      $products[] = [
        'id' => $dao->product_id,
        'label' => $dao->label,
        'current_price' => $dao->current_price,
        'is_serialize' => $dao->is_product_serialize,
        'total_variants' => $dao->total_variants,
        'available_variants' => $dao->available_variants,
      ];
    }

    return $products;
  }

  /**
   * Handle membership renewal with inventory.
   *
   * @param int $membershipId
   * @param int $contactId
   * @param int $contributionId
   */
  public static function handleMembershipRenewal($membershipId, $contactId, $contributionId = NULL) {
    // Get existing devices for this membership
    $query = "
      SELECT id, status, is_suspended
      FROM civicrm_inventory_product_variant
      WHERE membership_id = %1 AND is_active = 1
    ";

    $params = [1 => [$membershipId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $devicesToReactivate = [];
    while ($dao->fetch()) {
      if ($dao->is_suspended || $dao->status == 'suspended') {
        $devicesToReactivate[] = $dao->id;
      }
    }

    // Reactivate suspended devices
    if (!empty($devicesToReactivate)) {
      foreach ($devicesToReactivate as $deviceId) {
        CRM_Inventory_BAO_InventoryProductVariant::reactivateDevice($deviceId);
      }

      CRM_Core_Session::setStatus(
        E::ts('Reactivated %1 suspended device(s).', [1 => count($devicesToReactivate)]),
        E::ts('Devices Reactivated'),
        'success'
      );
    }

    // Create renewal sale record
    CRM_Inventory_BAO_InventorySales::createFromMembershipRenewal($membershipId, $contactId, $contributionId);
  }

  /**
   * Handle membership cancellation/expiration.
   *
   * @param int $membershipId
   * @param string $reason
   */
  public static function handleMembershipCancellation($membershipId, $reason = 'membership_cancelled') {
    // Get active devices for this membership
    $query = "
      SELECT id FROM civicrm_inventory_product_variant
      WHERE membership_id = %1 AND is_active = 1 AND is_suspended = 0
    ";

    $params = [1 => [$membershipId, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $devicesToSuspend = [];
    while ($dao->fetch()) {
      $devicesToSuspend[] = $dao->id;
    }

    // Suspend active devices
    if (!empty($devicesToSuspend)) {
      foreach ($devicesToSuspend as $deviceId) {
        CRM_Inventory_BAO_InventoryProductVariant::suspendDevice($deviceId, $reason);
      }

      CRM_Core_Session::setStatus(
        E::ts('Suspended %1 device(s) due to membership cancellation.', [1 => count($devicesToSuspend)]),
        E::ts('Devices Suspended'),
        'info'
      );
    }
  }

  /**
   * Get inventory summary for membership.
   *
   * @param int $membershipId
   * @return array
   */
  public static function getMembershipInventorySummary($membershipId) {
    $query = "
      SELECT
        pv.*,
        p.label as product_label,
        p.product_code
      FROM civicrm_inventory_product_variant pv
      INNER JOIN civicrm_inventory_product p ON pv.product_id = p.id
      WHERE pv.membership_id = %1
      ORDER BY pv.created_at DESC
    ";

    $params = [1 => [$membershipId, 'Integer']];
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
        'is_active' => $dao->is_active,
        'is_suspended' => $dao->is_suspended,
        'is_problem' => $dao->is_problem,
        'warranty_start_date' => $dao->warranty_start_date,
        'warranty_end_date' => $dao->warranty_end_date,
      ];
    }

    return $inventory;
  }

}
