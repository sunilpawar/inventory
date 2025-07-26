<?php

require_once 'inventory.civix.php';
// phpcs:disable
use CRM_Inventory_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function inventory_civicrm_config(&$config): void {
  _inventory_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function inventory_civicrm_install(): void {
  _inventory_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function inventory_civicrm_enable(): void {
  _inventory_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function inventory_civicrm_disable(): void {
  _inventory_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function inventory_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _inventory_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function inventory_civicrm_entityTypes(&$entityTypes): void {
  _inventory_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function inventory_civicrm_navigationMenu(&$menu): void {
  _inventory_civix_insert_navigation_menu($menu, 'Administer', [
    'label' => E::ts('Inventory Management'),
    'name' => 'inventory_admin',
    'url' => 'civicrm/inventory/dashboard',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_insert_navigation_menu($menu, 'inventory_admin', [
    'label' => E::ts('Dashboard'),
    'name' => 'inventory_dashboard',
    'url' => 'civicrm/inventory/dashboard',
    'permission' => 'access inventory',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_insert_navigation_menu($menu, 'inventory_admin', [
    'label' => E::ts('Products'),
    'name' => 'inventory_products',
    'url' => 'civicrm/inventory/products',
    'permission' => 'access inventory',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_insert_navigation_menu($menu, 'inventory_admin', [
    'label' => E::ts('Warehouses'),
    'name' => 'inventory_warehouses',
    'url' => 'civicrm/inventory/warehouses',
    'permission' => 'access inventory',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_insert_navigation_menu($menu, 'inventory_admin', [
    'label' => E::ts('Sales'),
    'name' => 'inventory_sales',
    'url' => 'civicrm/inventory/sales',
    'permission' => 'access inventory',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_insert_navigation_menu($menu, 'inventory_admin', [
    'label' => E::ts('Shipments'),
    'name' => 'inventory_shipments',
    'url' => 'civicrm/inventory/shipments',
    'permission' => 'access inventory',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_insert_navigation_menu($menu, 'inventory_admin', [
    'label' => E::ts('Reports'),
    'name' => 'inventory_reports',
    'url' => 'civicrm/inventory/reports',
    'permission' => 'access inventory',
    'operator' => 'OR',
    'separator' => 0,
  ]);

  _inventory_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission
 */
function inventory_civicrm_permission(&$permissions) {
  $permissions['access inventory'] = [
    'label' => E::ts('Access Inventory Management'),
    'description' => E::ts('Allows access to inventory management features'),
  ];
  $permissions['edit inventory'] = [
    'label' => E::ts('Edit Inventory'),
    'description' => E::ts('Allows editing of inventory items and management'),
  ];
  $permissions['delete inventory'] = [
    'label' => E::ts('Delete Inventory'),
    'description' => E::ts('Allows deletion of inventory items'),
  ];
  $permissions['manage inventory settings'] = [
    'label' => E::ts('Manage Inventory Settings'),
    'description' => E::ts('Allows management of inventory system settings'),
  ];
}

/**
 * Implements hook_civicrm_tabs().
 *
 * Add inventory tab to contact summary page
 */
function inventory_civicrm_tabs(&$tabs, $contactID) {
  $tabs[] = [
    'id' => 'inventory',
    'url' => CRM_Utils_System::url('civicrm/contact/view/inventory', "reset=1&cid={$contactID}"),
    'title' => E::ts('Inventory'),
    'weight' => 300,
    'count' => CRM_Inventory_BAO_InventoryProductVariant::getContactInventoryCount($contactID),
  ];
}

/**
 * Implements hook_civicrm_buildForm().
 */
function inventory_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Member_Form_Membership') {
    // Add inventory product selection to membership form
    CRM_Inventory_Utils_MembershipIntegration::buildMembershipForm($form);
  }

  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    // Add product selection to contribution pages
    CRM_Inventory_Utils_ContributionIntegration::buildContributionForm($form);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function inventory_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Member_Form_Membership') {
    CRM_Inventory_Utils_MembershipIntegration::postProcessMembership($form);
  }

  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    CRM_Inventory_Utils_ContributionIntegration::postProcessContribution($form);
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function inventory_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');

  if ($pageName == 'CRM_Contact_Page_View_Summary') {
    // Add inventory information to contact summary
    $contactID = $page->getVar('_contactId');
    $inventoryData = CRM_Inventory_BAO_InventoryProductVariant::getContactInventorySummary($contactID);
    $page->assign('contactInventory', $inventoryData);
  }
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function inventory_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  // Add API wrappers for inventory integration
  if ($apiRequest['entity'] == 'Membership' && in_array($apiRequest['action'], ['create', 'update'])) {
    $wrappers[] = new CRM_Inventory_API_Wrapper_Membership();
  }

  if ($apiRequest['entity'] == 'Contribution' && in_array($apiRequest['action'], ['create', 'update'])) {
    $wrappers[] = new CRM_Inventory_API_Wrapper_Contribution();
  }
}
