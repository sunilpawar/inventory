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
 * Database access object for InventoryProduct entity.
 */
class CRM_Inventory_DAO_InventoryProduct extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_inventory_product';

  /**
   * Icon associated with this entity.
   *
   * @var string
   */
  public static $_icon = 'fa-cube';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique InventoryProduct ID
   *
   * @var int
   */
  public $id;

  /**
   * Product Label
   *
   * @var string
   */
  public $label;

  /**
   * Product Code SKU.
   *
   * @var string
   */
  public $product_code;

  /**
   * External Code
   *
   * @var string
   */
  public $external_code;

  /**
   * Product Description
   *
   * @var string
   */
  public $product_description;

  /**
   * The amount that is shown to the user.
   *
   * @var float
   */
  public $listed_price;

  /**
   * The fair market value of this item.
   *
   * @var float
   */
  public $current_price;

  /**
   * Product Brand
   *
   * @var string
   */
  public $product_brand;

  /**
   * Product details.
   *
   * @var string
   */
  public $product_note;

  /**
   * FK to Category
   *
   * @var int
   */
  public $product_category_id;

  /**
   * File url.
   *
   * @var string
   */
  public $image_actual;

  /**
   * Thumbnail Image
   *
   * @var string
   */
  public $image_thumbnail;

  /**
   * Packed Weight
   *
   * @var float
   */
  public $packed_weight;

  /**
   * Packed Height
   *
   * @var float
   */
  public $packed_height;

  /**
   * Packed Width
   *
   * @var float
   */
  public $packed_width;

  /**
   * Packed Depth
   *
   * @var float
   */
  public $packed_depth;

  /**
   * Battery backup time.
   *
   * @var string
   */
  public $product_variant_battery;

  /**
   * Device Speed.
   *
   * @var string
   */
  public $product_variant_speed;

  /**
   * Has Antenna
   *
   * @var bool
   */
  public $antenna;

  /**
   * Has Tether
   *
   * @var bool
   */
  public $tether;

  /**
   * Has Powerbank
   *
   * @var bool
   */
  public $powerbank;

  /**
   * Is Batteryless
   *
   * @var bool
   */
  public $batteryless;

  /**
   * Has 4G Network
   *
   * @var bool
   */
  public $network_4g;

  /**
   * Has 5G Network
   *
   * @var bool
   */
  public $network_5g;

  /**
   * Has SIM
   *
   * @var bool
   */
  public $has_sim;

  /**
   * Has Device
   *
   * @var bool
   */
  public $has_device;

  /**
   * Warranty Type ID
   *
   * @var int
   */
  public $warranty_type_id;

  /**
   * Feet, pounds, and gallons are all examples of units of measure.
   *
   * @var string
   */
  public $uom;

  /**
   * Screen size details.
   *
   * @var string
   */
  public $screen;

  /**
   * Product Memory size.
   *
   * @var string
   */
  public $memory;

  /**
   * Product Color.
   *
   * @var string
   */
  public $color;

  /**
   * Premium is Optional
   *
   * @var bool
   */
  public $premium_is_optional;

  /**
   * Premium Needs Address
   *
   * @var bool
   */
  public $premium_needs_address;

  /**
   * Premium Shirt Count
   *
   * @var int
   */
  public $premium_shirt_count;

  /**
   * Premium Device Count
   *
   * @var int
   */
  public $premium_device_count;

  /**
   * FK to Warehouse
   *
   * @var int
   */
  public $warehouse_id;

  /**
   * The quantity on hand.
   *
   * @var int
   */
  public $quantity_available;

  /**
   * The minimum number of units required to ensure no shortages occur at this warehouse.
   *
   * @var int
   */
  public $minimum_quantity_stock_level;

  /**
   * The maximum number of units desired in stock, i.e. to avoid overstocking.
   *
   * @var int
   */
  public $maximum_quantity_stock_level;

  /**
   * The minimum number of units required to ensure no shortages occur at this warehouse.
   *
   * @var int
   */
  public $reorder_point;

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
   * Controls display sort order.
   *
   * @var int
   */
  public $weight;

  /**
   * Is Serialized Product
   *
   * @var bool
   */
  public $is_serialize;

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
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_inventory_product';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Inventory Products') : E::ts('Inventory Product');
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
          'description' => E::ts('Unique InventoryProduct ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product.id',
          'table_name' => 'civicrm_inventory_product',
          'entity' => 'InventoryProduct',
          'bao' => 'CRM_Inventory_DAO_InventoryProduct',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'label' => [
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Product Label'),
          'description' => E::ts('Product Label'),
          'required' => TRUE,
          'maxlength' => 100,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => TRUE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product.label',
          'table_name' => 'civicrm_inventory_product',
          'entity' => 'InventoryProduct',
          'bao' => 'CRM_Inventory_DAO_InventoryProduct',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'maxlength' => 100,
            'size' => CRM_Utils_Type::HUGE,
          ],
          'add' => NULL,
        ],
        'product_code' => [
          'name' => 'product_code',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Product Code'),
          'description' => E::ts('Product Code SKU.'),
          'required' => TRUE,
          'maxlength' => 100,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => TRUE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product.product_code',
          'table_name' => 'civicrm_inventory_product',
          'entity' => 'InventoryProduct',
          'bao' => 'CRM_Inventory_DAO_InventoryProduct',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'maxlength' => 100,
            'size' => CRM_Utils_Type::HUGE,
          ],
          'add' => NULL,
        ],
        'current_price' => [
          'name' => 'current_price',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => E::ts('Current Price'),
          'description' => E::ts('The fair market value of this item.'),
          'required' => TRUE,
          'precision' => [
            20,
            2,
          ],
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product.current_price',
          'table_name' => 'civicrm_inventory_product',
          'entity' => 'InventoryProduct',
          'bao' => 'CRM_Inventory_DAO_InventoryProduct',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'product_category_id' => [
          'name' => 'product_category_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Product Category ID'),
          'description' => E::ts('FK to Category'),
          'required' => TRUE,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_inventory_product.product_category_id',
          'table_name' => 'civicrm_inventory_product',
          'entity' => 'InventoryProduct',
          'bao' => 'CRM_Inventory_DAO_InventoryProduct',
          'localizable' => 0,
          'FKClassName' => 'CRM_Inventory_DAO_InventoryCategory',
          'html' => [
            'type' => 'EntityRef',
            'label' => E::ts("Category"),
          ],
          'pseudoconstant' => [
            'table' => 'civicrm_inventory_category',
            'keyColumn' => 'id',
            'labelColumn' => 'title',
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
          'where' => 'civicrm_inventory_product.is_active',
          'default' => '1',
          'table_name' => 'civicrm_inventory_product',
          'entity' => 'InventoryProduct',
          'bao' => 'CRM_Inventory_DAO_InventoryProduct',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'inventory_product', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'inventory_product', $prefix, []);
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
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
