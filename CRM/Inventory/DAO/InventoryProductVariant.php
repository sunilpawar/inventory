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
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * Database access object for InventoryProductVariant entity.
 */
class CRM_Inventory_DAO_InventoryProductVariant extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_inventory_product_variant';

  /**
   * Icon associated with this entity.
   *
   * @var string
   */
  public static $_icon = 'fa-mobile';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique Inventory Product Variant ID
   *
   * @var int
   */
  public $id;

  /**
   * FK to Product
   *
   * @var int
   */
  public $product_id;

  /**
   * FK to Contact
   *
   * @var int
   */
  public $contact_id;

  /**
   * Phone number linked with device.
   *
   * @var string
   */
  public $product_variant_phone_number;

  /**
   * e.g IMEI (International Mobile Equipment Identity) number .
   *
   * @var string
   */
  public $product_variant_unique_id;

  /**
   * Product Variant details.
   *
   * @var string
   */
  public $product_variant_details;

  /**
   * Thumbnail Image
   *
   * @var string
   */
  public $image_thumbnail;

  /**
   * Actual Image
   *
   * @var string
   */
  public $image_actual;

  /**
   * Status
   *
   * @var string
   */
  public $status;

  /**
   * Optional Product.
   *
   * @var int
   */
  public $replaced_product_id;

  /**
   * Is Replaced
   *
   * @var bool
   */
  public $is_replaced;

  /**
   * Replaced Date
   *
   * @var string
   */
  public $replaced_date;

  /**
   * Membership ID Associated with product.
   *
   * @var int
   */
  public $membership_id;

  /**
   * Added into system on specific order number.
   *
   * @var int
   */
  public $order_number;

  /**
   * Warranty Start Date
   *
   * @var string
   */
  public $warranty_start_date;

  /**
   * Warranty End Date
   *
   * @var string
   */
  public $warranty_end_date;

  /**
   * Expire On
   *
   * @var string
   */
  public $expire_on;

  /**
   * Created At
   *
   * @var string
   */
  public $created_at;

  /**
   * Updated At
   *
   * @var string
   */
  public $updated_at;

  /**
   * Sales ID Associated with sales tables.
   *
   * @var int
   */
  public $sales_id;

  /**
   * Use to locate the item in warehouse
   *
   * @var string
   */
  public $row;

  /**
   * Use to locate the item in warehouse
   *
   * @var string
   */
  public $shelf;

  /**
   * Is Primary
   *
   * @var bool
   */
  public $is_primary;

  /**
   * Is Discontinued
   *
   * @var bool
   */
  public $is_discontinued;

  /**
   * Is Active
   *
   * @var bool
   */
  public $is_active;

  /**
   * Is Suspended
   *
   * @var bool
   */
  public $is_suspended;

  /**
   * Is Problem
   *
   * @var bool
   */
  public $is_problem;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_inventory_product_variant';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Inventory Product Variants') : E::ts('Inventory Product Variant');
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique Inventory Product Variant ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.id',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'product_id' => [
          'name' => 'product_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Product'),
          'description' => E::ts('FK to Product'),
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.product_id',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'FKClassName' => 'CRM_Inventory_DAO_InventoryProduct',
          'html' => [
            'type' => 'EntityRef',
            'label' => E::ts("Product"),
          ],
          'pseudoconstant' => [
            'table' => 'civicrm_inventory_product',
            'keyColumn' => 'id',
            'labelColumn' => 'label',
          ],
          'add' => NULL,
        ],
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Contact'),
          'description' => E::ts('FK to Contact'),
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.contact_id',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'type' => 'EntityRef',
            'label' => E::ts("Contact"),
          ],
          'add' => NULL,
        ],
        'product_variant_unique_id' => [
          'name' => 'product_variant_unique_id',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Unique ID'),
          'description' => E::ts('e.g IMEI (International Mobile Equipment Identity) number .'),
          'maxlength' => 100,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => TRUE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.product_variant_unique_id',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'maxlength' => 100,
            'size' => CRM_Utils_Type::HUGE,
          ],
          'add' => NULL,
        ],
        'product_variant_phone_number' => [
          'name' => 'product_variant_phone_number',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Phone Number'),
          'description' => E::ts('Phone number linked with device.'),
          'maxlength' => 100,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.product_variant_phone_number',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'maxlength' => 100,
            'size' => CRM_Utils_Type::HUGE,
          ],
          'add' => NULL,
        ],
        'status' => [
          'name' => 'status',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Status'),
          'description' => E::ts('Status'),
          'maxlength' => 100,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.status',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'CRM_Inventory_BAO_InventoryProductVariant::getStatusOptions',
          ],
          'add' => NULL,
        ],
        'membership_id' => [
          'name' => 'membership_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Membership'),
          'description' => E::ts('Membership ID Associated with product.'),
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.membership_id',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'FKClassName' => 'CRM_Member_DAO_Membership',
          'html' => [
            'type' => 'EntityRef',
            'label' => E::ts("Membership"),
          ],
          'add' => NULL,
        ],
        'warranty_start_date' => [
          'name' => 'warranty_start_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => E::ts('Warranty Start Date'),
          'description' => E::ts('Warranty Start Date'),
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.warranty_start_date',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'warranty_end_date' => [
          'name' => 'warranty_end_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => E::ts('Warranty End Date'),
          'description' => E::ts('Warranty End Date'),
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.warranty_end_date',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'is_active' => [
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is Active'),
          'description' => E::ts('Is Active'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.is_active',
          'default' => '1',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
          'add' => NULL,
        ],
        'is_suspended' => [
          'name' => 'is_suspended',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is Suspended'),
          'description' => E::ts('Is Suspended'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.is_suspended',
          'default' => '0',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
          'add' => NULL,
        ],
        'is_problem' => [
          'name' => 'is_problem',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is Problem'),
          'description' => E::ts('Is Problem'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product_variant.is_problem',
          'default' => '0',
          'table_name' => 'civicrm_inventory_product_variant',
          'entity' => 'InventoryProductVariant',
          'bao' => 'CRM_Inventory_DAO_InventoryProductVariant',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'inventory_product_variant', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'inventory_product_variant', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'UI_product_variant_unique_id' => [
        'name' => 'UI_product_variant_unique_id',
        'field' => [
          0 => 'product_variant_unique_id',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_inventory_product_variant::1::product_variant_unique_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
