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

namespace Civi\Api4;

/**
 * InventoryProduct entity.
 *
 * Provided by the Inventory Management extension.
 *
 * @package Civi\Api4
 */
class InventoryProduct extends Generic\DAOEntity {

  /**
   * @param bool $checkPermissions
   * @return Action\InventoryProduct\Create
   */
  public static function create($checkPermissions = TRUE) {
    return (new Action\InventoryProduct\Create(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\InventoryProduct\Save
   */
  public static function save($checkPermissions = TRUE) {
    return (new Action\InventoryProduct\Save(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\InventoryProduct\Update
   */
  public static function update($checkPermissions = TRUE) {
    return (new Action\InventoryProduct\Update(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\InventoryProduct\Delete
   */
  public static function delete($checkPermissions = TRUE) {
    return (new Action\InventoryProduct\Delete(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\InventoryProduct\Replace
   */
  public static function replace($checkPermissions = TRUE) {
    return (new Action\InventoryProduct\Replace(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\InventoryProduct\Get
   */
  public static function get($checkPermissions = TRUE) {
    return (new Action\InventoryProduct\Get(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Generic\BasicGetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function() {
      return [
        [
          'name' => 'id',
          'title' => 'Product ID',
          'data_type' => 'Integer',
          'required' => FALSE,
          'readonly' => TRUE,
        ],
        [
          'name' => 'label',
          'title' => 'Product Name',
          'data_type' => 'String',
          'required' => TRUE,
        ],
        [
          'name' => 'product_code',
          'title' => 'Product Code (SKU)',
          'data_type' => 'String',
          'required' => TRUE,
        ],
        [
          'name' => 'external_code',
          'title' => 'External Code',
          'data_type' => 'String',
          'required' => FALSE,
        ],
        [
          'name' => 'product_description',
          'title' => 'Description',
          'data_type' => 'String',
          'required' => FALSE,
        ],
        [
          'name' => 'listed_price',
          'title' => 'Listed Price',
          'data_type' => 'Money',
          'required' => TRUE,
        ],
        [
          'name' => 'current_price',
          'title' => 'Current Price',
          'data_type' => 'Money',
          'required' => TRUE,
        ],
        [
          'name' => 'product_brand',
          'title' => 'Brand',
          'data_type' => 'String',
          'required' => FALSE,
        ],
        [
          'name' => 'product_category_id',
          'title' => 'Product Category',
          'data_type' => 'Integer',
          'required' => TRUE,
          'fk_entity' => 'InventoryCategory',
        ],
        [
          'name' => 'warehouse_id',
          'title' => 'Warehouse',
          'data_type' => 'Integer',
          'required' => FALSE,
          'fk_entity' => 'InventoryWarehouse',
        ],
        [
          'name' => 'quantity_available',
          'title' => 'Quantity Available',
          'data_type' => 'Integer',
          'required' => FALSE,
        ],
        [
          'name' => 'minimum_quantity_stock_level',
          'title' => 'Minimum Stock Level',
          'data_type' => 'Integer',
          'required' => FALSE,
        ],
        [
          'name' => 'maximum_quantity_stock_level',
          'title' => 'Maximum Stock Level',
          'data_type' => 'Integer',
          'required' => FALSE,
        ],
        [
          'name' => 'reorder_point',
          'title' => 'Reorder Point',
          'data_type' => 'Integer',
          'required' => FALSE,
        ],
        [
          'name' => 'is_serialize',
          'title' => 'Is Serialized',
          'data_type' => 'Boolean',
          'required' => FALSE,
          'default_value' => TRUE,
        ],
        [
          'name' => 'is_discontinued',
          'title' => 'Is Discontinued',
          'data_type' => 'Boolean',
          'required' => FALSE,
          'default_value' => FALSE,
        ],
        [
          'name' => 'is_active',
          'title' => 'Is Active',
          'data_type' => 'Boolean',
          'required' => FALSE,
          'default_value' => TRUE,
        ],
      ];
    }))->setCheckPermissions($checkPermissions);
  }

  /**
   * @return array
   */
  public static function permissions() {
    return [
      'meta' => ['access inventory'],
      'default' => ['access inventory'],
      'get' => ['access inventory'],
      'create' => ['edit inventory'],
      'update' => ['edit inventory'],
      'save' => ['edit inventory'],
      'delete' => ['delete inventory'],
    ];
  }

}
