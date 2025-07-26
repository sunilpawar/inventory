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
 * API Wrapper for Membership entity to handle inventory integration
 */
class CRM_Inventory_API_Wrapper_Membership implements API_Wrapper {

  /**
   * Modify API request before it is executed
   *
   * @param array $apiRequest
   * @return array
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Modify API result after execution
   *
   * @param array $apiRequest
   * @param array $result
   * @return array
   */
  public function toApiOutput($apiRequest, $result) {
    // Handle membership creation/update with inventory
    if ($apiRequest['action'] == 'create' && !empty($result['id'])) {
      $this->handleMembershipInventory($apiRequest, $result);
    }

    return $result;
  }

  /**
   * Handle inventory-related actions for membership
   *
   * @param array $apiRequest
   * @param array $result
   */
  private function handleMembershipInventory($apiRequest, $result) {
    $membershipId = $result['id'];
    $params = $apiRequest['params'];

    // Check if this is a new membership
    $isNew = empty($params['id']);

    if ($isNew) {
      // Handle new membership with product assignment
      $this->handleNewMembershipWithInventory($membershipId, $params);
    } else {
      // Handle membership renewal/status change
      $this->handleMembershipStatusChange($membershipId, $params);
    }
  }

  /**
   * Handle new membership with potential product assignment
   *
   * @param int $membershipId
   * @param array $params
   */
  private function handleNewMembershipWithInventory($membershipId, $params) {
    // Check if product assignment is requested
    if (!empty($params['inventory_product_id'])) {
      $productId = $params['inventory_product_id'];
      $contactId = $params['contact_id'];

      $additionalParams = [];
      if (!empty($params['inventory_phone_number'])) {
        $additionalParams['product_variant_phone_number'] = $params['inventory_phone_number'];
      }

      // Assign product to membership
      $variant = CRM_Inventory_BAO_InventoryProductVariant::assignToContact(
        $productId,
        $contactId,
        $membershipId,
        $additionalParams
      );

      if ($variant) {
        // Update product inventory
        CRM_Inventory_BAO_InventoryProduct::updateInventoryAfterSale($productId);

        // Create sales record if contribution is present
        if (!empty($params['contribution_id'])) {
          $this->createSalesRecord($contactId, $membershipId, $params['contribution_id'], $productId);
        }
      }
    }

    // Check for automatic product assignment based on membership type
    $this->checkAutomaticProductAssignment($membershipId, $params);
  }

  /**
   * Handle membership status changes
   *
   * @param int $membershipId
   * @param array $params
   */
  private function handleMembershipStatusChange($membershipId, $params) {
    // Get current membership
    try {
      $membership = civicrm_api3('Membership', 'getsingle', ['id' => $membershipId]);
      $contactId = $membership['contact_id'];

      // Handle status-specific actions
      if (!empty($params['status_id'])) {
        $statusId = $params['status_id'];
        $statusName = CRM_Core_PseudoConstant::getName('CRM_Member_BAO_Membership', 'status_id', $statusId);

        switch (strtolower($statusName)) {
          case 'new':
          case 'current':
            // Reactivate any suspended devices
            CRM_Inventory_Utils_MembershipIntegration::handleMembershipRenewal(
              $membershipId,
              $contactId,
              $params['contribution_id'] ?? NULL
            );
            break;

          case 'cancelled':
          case 'expired':
          case 'deceased':
            // Suspend devices
            CRM_Inventory_Utils_MembershipIntegration::handleMembershipCancellation(
              $membershipId,
              "membership_{$statusName}"
            );
            break;

          case 'pending':
            // No action needed for pending status
            break;
        }
      }
    } catch (CiviCRM_API3_Exception $e) {
      // Log error but don't fail the membership operation
      Civi::log()->warning('Inventory integration error for membership {membership_id}: {message}', [
        'membership_id' => $membershipId,
        'message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Check for automatic product assignment based on membership type configuration
   *
   * @param int $membershipId
   * @param array $params
   */
  private function checkAutomaticProductAssignment($membershipId, $params) {
    // Skip if product already assigned manually
    if (!empty($params['inventory_product_id'])) {
      return;
    }

    $membershipTypeId = $params['membership_type_id'];
    $contactId = $params['contact_id'];

    // Get auto-assignment configuration for this membership type
    $autoProducts = $this->getAutoAssignmentProducts($membershipTypeId);

    foreach ($autoProducts as $productId) {
      // Check if product is available
      $availableVariants = CRM_Inventory_BAO_InventoryProductVariant::getAvailableVariants($productId);

      if (!empty($availableVariants)) {
        // Assign the first available variant
        $variant = CRM_Inventory_BAO_InventoryProductVariant::assignToContact(
          $productId,
          $contactId,
          $membershipId
        );

        if ($variant) {
          // Update inventory
          CRM_Inventory_BAO_InventoryProduct::updateInventoryAfterSale($productId);

          // Create sales record if needed
          if (!empty($params['contribution_id'])) {
            $this->createSalesRecord($contactId, $membershipId, $params['contribution_id'], $productId);
          }

          // Log the automatic assignment
          Civi::log()->info('Automatically assigned product {product_id} to membership {membership_id}', [
            'product_id' => $productId,
            'membership_id' => $membershipId,
            'contact_id' => $contactId,
          ]);

          break; // Only assign one product automatically
        }
      }
    }
  }

  /**
   * Get products configured for auto-assignment for a membership type
   *
   * @param int $membershipTypeId
   * @return array
   */
  private function getAutoAssignmentProducts($membershipTypeId) {
    // This would be configurable via admin interface
    // For now, return empty array - implement based on requirements
    return [];
  }

  /**
   * Create sales record for membership with inventory
   *
   * @param int $contactId
   * @param int $membershipId
   * @param int $contributionId
   * @param int $productId
   */
  private function createSalesRecord($contactId, $membershipId, $contributionId, $productId) {
    try {
      // Get product details
      $product = civicrm_api3('InventoryProduct', 'getsingle', ['id' => $productId]);

      // Create sale
      $sale = CRM_Inventory_BAO_InventorySales::createFromContribution(
        $contributionId,
        $contactId,
        $membershipId,
        [
          [
            'product_id' => $productId,
            'title' => $product['label'],
            'price' => $product['current_price'],
            'quantity' => 1,
            'type' => 'membership_product',
            'contribution_id' => $contributionId,
          ]
        ]
      );

      if ($sale) {
        // Link line item to sale
        $this->linkLineItemToSale($contributionId, $sale->id, $productId);
      }
    } catch (Exception $e) {
      // Log error but don't fail
      Civi::log()->warning('Failed to create sales record for membership inventory: {message}', [
        'message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Link contribution line item to inventory sale
   *
   * @param int $contributionId
   * @param int $saleId
   * @param int $productId
   */
  private function linkLineItemToSale($contributionId, $saleId, $productId) {
    try {
      // Find the line item for this contribution
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $contributionId,
        'options' => ['limit' => 1],
      ]);

      if (!empty($lineItems['values'])) {
        $lineItem = reset($lineItems['values']);

        // Update line item with sale and product info
        civicrm_api3('LineItem', 'create', [
          'id' => $lineItem['id'],
          'sale_id' => $saleId,
          'product_variant_id' => $productId, // This would be the actual variant ID
        ]);
      }
    } catch (Exception $e) {
      // Log error but don't fail
      Civi::log()->warning('Failed to link line item to sale: {message}', [
        'message' => $e->getMessage(),
      ]);
    }
  }

}
