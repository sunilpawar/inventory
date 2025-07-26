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
 * Form controller class for Product add/edit
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Inventory_Form_Product extends CRM_Core_Form {

  /**
   * Product ID for edit mode
   *
   * @var int
   */
  protected $_id;

  /**
   * Current action being performed
   *
   * @var int
   */
  protected $_action;

  /**
   * Product data
   *
   * @var array
   */
  protected $_product;

  public function preProcess() {
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    if ($this->_id) {
      $this->_product = civicrm_api3('InventoryProduct', 'getsingle', ['id' => $this->_id]);
    }

    // Check permissions
    if (!CRM_Core_Permission::check('edit inventory')) {
      CRM_Core_Error::statusBounce(E::ts('You do not have permission to edit inventory.'));
    }

    $this->assign('action', $this->_action);
    $this->assign('id', $this->_id);
  }

  public function buildQuickForm() {
    // Set page title
    if ($this->_action == CRM_Core_Action::UPDATE) {
      CRM_Utils_System::setTitle(E::ts('Edit Product: %1', [1 => $this->_product['label']]));
    } else {
      CRM_Utils_System::setTitle(E::ts('Add Product'));
    }

    // Basic Information
    $this->addFormElements();

    // Product Features
    $this->addProductFeatures();

    // Inventory Management
    $this->addInventoryFields();

    // Premium Settings
    $this->addPremiumFields();

    // Product Images
    $this->addImageFields();

    // Buttons
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ],
    ]);

    // Export form elements to template
    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  /**
   * Add basic form elements
   */
  private function addFormElements() {
    // Required fields
    $this->add('text', 'label', E::ts('Product Name'), ['size' => 50], TRUE);
    $this->add('text', 'product_code', E::ts('Product Code (SKU)'), ['size' => 30], TRUE);

    // Category selection
    $categories = CRM_Inventory_BAO_InventoryProduct::getProductCategoriesWithCounts();
    $categoryOptions = ['' => E::ts('- select -')];
    foreach ($categories as $id => $category) {
      $categoryOptions[$id] = $category['title'];
    }
    $this->add('select', 'product_category_id', E::ts('Category'), $categoryOptions, TRUE, ['class' => 'crm-select2']);

    // Optional fields
    $this->add('text', 'external_code', E::ts('External Code'), ['size' => 30]);
    $this->add('textarea', 'product_description', E::ts('Description'), ['rows' => 4, 'cols' => 50]);
    $this->add('text', 'product_brand', E::ts('Brand'), ['size' => 30]);
    $this->add('textarea', 'product_note', E::ts('Notes'), ['rows' => 3, 'cols' => 50]);

    // Pricing
    $this->add('text', 'listed_price', E::ts('Listed Price'), ['size' => 15], TRUE);
    $this->add('text', 'current_price', E::ts('Current Price'), ['size' => 15], TRUE);

    // Physical dimensions
    $this->add('text', 'packed_weight', E::ts('Weight'), ['size' => 10]);
    $this->add('text', 'packed_height', E::ts('Height'), ['size' => 10]);
    $this->add('text', 'packed_width', E::ts('Width'), ['size' => 10]);
    $this->add('text', 'packed_depth', E::ts('Depth'), ['size' => 10]);

    // Unit of measure
    $this->add('text', 'uom', E::ts('Unit of Measure'), ['size' => 20]);
  }

  /**
   * Add product feature checkboxes
   */
  private function addProductFeatures() {
    $features = [
      'antenna' => E::ts('Has Antenna'),
      'tether' => E::ts('Has Tether'),
      'powerbank' => E::ts('Has Powerbank'),
      'batteryless' => E::ts('Is Batteryless'),
      'network_4g' => E::ts('4G Network'),
      'network_5g' => E::ts('5G Network'),
      'has_sim' => E::ts('Has SIM'),
      'has_device' => E::ts('Has Device'),
    ];

    foreach ($features as $field => $label) {
      $this->add('checkbox', $field, $label);
    }

    // Technical specifications
    $this->add('text', 'product_variant_battery', E::ts('Battery Life'), ['size' => 20]);
    $this->add('text', 'product_variant_speed', E::ts('Device Speed'), ['size' => 20]);
    $this->add('text', 'screen', E::ts('Screen Size'), ['size' => 20]);
    $this->add('text', 'memory', E::ts('Memory'), ['size' => 20]);
    $this->add('text', 'color', E::ts('Color'), ['size' => 20]);
  }

  /**
   * Add inventory management fields
   */
  private function addInventoryFields() {
    // Warehouse selection
    $warehouses = civicrm_api3('InventoryWarehouse', 'get', [
      'return' => ['id', 'name'],
      'options' => ['limit' => 0],
    ]);
    $warehouseOptions = ['' => E::ts('- select -')];
    foreach ($warehouses['values'] as $warehouse) {
      $warehouseOptions[$warehouse['id']] = $warehouse['name'];
    }
    $this->add('select', 'warehouse_id', E::ts('Primary Warehouse'), $warehouseOptions, FALSE, ['class' => 'crm-select2']);

    // Stock levels
    $this->add('text', 'quantity_available', E::ts('Quantity Available'), ['size' => 10]);
    $this->add('text', 'minimum_quantity_stock_level', E::ts('Minimum Stock Level'), ['size' => 10]);
    $this->add('text', 'maximum_quantity_stock_level', E::ts('Maximum Stock Level'), ['size' => 10]);
    $this->add('text', 'reorder_point', E::ts('Reorder Point'), ['size' => 10]);

    // Location in warehouse
    $this->add('text', 'row', E::ts('Row'), ['size' => 10]);
    $this->add('text', 'shelf', E::ts('Shelf'), ['size' => 10]);

    // Product type
    $this->add('checkbox', 'is_serialize', E::ts('Is Serialized Product'));
    $this->add('text', 'weight', E::ts('Display Weight'), ['size' => 5]);
  }

  /**
   * Add premium/membership related fields
   */
  private function addPremiumFields() {
    $this->add('checkbox', 'premium_is_optional', E::ts('Premium is Optional'));
    $this->add('checkbox', 'premium_needs_address', E::ts('Premium Needs Address'));
    $this->add('text', 'premium_shirt_count', E::ts('Premium Shirt Count'), ['size' => 5]);
    $this->add('text', 'premium_device_count', E::ts('Premium Device Count'), ['size' => 5]);
  }

  /**
   * Add image upload fields
   */
  private function addImageFields() {
    $this->add('file', 'image_actual', E::ts('Product Image'));
    $this->add('file', 'image_thumbnail', E::ts('Thumbnail Image'));

    if (!empty($this->_product['image_actual'])) {
      $this->assign('current_image_actual', $this->_product['image_actual']);
    }
    if (!empty($this->_product['image_thumbnail'])) {
      $this->assign('current_image_thumbnail', $this->_product['image_thumbnail']);
    }
  }

  public function setDefaultValues() {
    $defaults = [];

    if ($this->_id) {
      $defaults = $this->_product;

      // Convert checkboxes
      $checkboxFields = [
        'antenna', 'tether', 'powerbank', 'batteryless',
        'network_4g', 'network_5g', 'has_sim', 'has_device',
        'premium_is_optional', 'premium_needs_address', 'is_serialize'
      ];

      foreach ($checkboxFields as $field) {
        if (!empty($defaults[$field])) {
          $defaults[$field] = 1;
        }
      }
    } else {
      // Set defaults for new product
      $defaults['is_serialize'] = 1;
      $defaults['weight'] = 1;
      $defaults['listed_price'] = 0;
      $defaults['current_price'] = 0;
    }

    return $defaults;
  }

  public function addRules() {
    $this->addFormRule(['CRM_Inventory_Form_Product', 'formRule'], $this);
  }

  /**
   * Global form rule.
   *
   * @param array $fields
   *   The input form values.
   * @param array $files
   *   The uploaded files if any.
   * @param CRM_Core_Form $form
   *   The form object.
   *
   * @return bool|array
   *   true if no errors, else array of errors
   */
  public static function formRule($fields, $files, $form) {
    $errors = [];

    // Validate product code uniqueness
    if (!empty($fields['product_code'])) {
      $duplicate = civicrm_api3('InventoryProduct', 'getcount', [
        'product_code' => $fields['product_code'],
        'id' => ['!=' => $form->_id],
      ]);

      if ($duplicate > 0) {
        $errors['product_code'] = E::ts('Product code already exists. Please choose a different code.');
      }
    }

    // Validate pricing
    if (!empty($fields['listed_price']) && !is_numeric($fields['listed_price'])) {
      $errors['listed_price'] = E::ts('Please enter a valid price.');
    }
    if (!empty($fields['current_price']) && !is_numeric($fields['current_price'])) {
      $errors['current_price'] = E::ts('Please enter a valid price.');
    }

    // Validate stock levels
    if (!empty($fields['minimum_quantity_stock_level']) && !empty($fields['maximum_quantity_stock_level'])) {
      if ($fields['minimum_quantity_stock_level'] >= $fields['maximum_quantity_stock_level']) {
        $errors['maximum_quantity_stock_level'] = E::ts('Maximum stock level must be greater than minimum stock level.');
      }
    }

    if (!empty($fields['reorder_point']) && !empty($fields['minimum_quantity_stock_level'])) {
      if ($fields['reorder_point'] < $fields['minimum_quantity_stock_level']) {
        $errors['reorder_point'] = E::ts('Reorder point should be at least equal to minimum stock level.');
      }
    }

    return empty($errors) ? TRUE : $errors;
  }

  public function postProcess() {
    $values = $this->exportValues();

    // Prepare data for API
    $params = [
      'label' => $values['label'],
      'product_code' => $values['product_code'],
      'product_category_id' => $values['product_category_id'],
      'listed_price' => $values['listed_price'],
      'current_price' => $values['current_price'],
    ];

    // Add optional fields if present
    $optionalFields = [
      'external_code', 'product_description', 'product_brand', 'product_note',
      'packed_weight', 'packed_height', 'packed_width', 'packed_depth',
      'product_variant_battery', 'product_variant_speed', 'screen', 'memory', 'color',
      'warehouse_id', 'quantity_available', 'minimum_quantity_stock_level',
      'maximum_quantity_stock_level', 'reorder_point', 'row', 'shelf', 'weight',
      'premium_shirt_count', 'premium_device_count', 'uom'
    ];

    foreach ($optionalFields as $field) {
      if (!empty($values[$field])) {
        $params[$field] = $values[$field];
      }
    }

    // Handle checkboxes
    $checkboxFields = [
      'antenna', 'tether', 'powerbank', 'batteryless',
      'network_4g', 'network_5g', 'has_sim', 'has_device',
      'premium_is_optional', 'premium_needs_address', 'is_serialize'
    ];

    foreach ($checkboxFields as $field) {
      $params[$field] = !empty($values[$field]) ? 1 : 0;
    }

    // Handle file uploads
    $this->handleFileUploads($params);

    // Set defaults
    $params['is_active'] = 1;
    $params['is_discontinued'] = 0;

    try {
      if ($this->_id) {
        $params['id'] = $this->_id;
        $result = civicrm_api3('InventoryProduct', 'create', $params);
        CRM_Core_Session::setStatus(E::ts('Product updated successfully.'), E::ts('Success'), 'success');
      } else {
        $result = civicrm_api3('InventoryProduct', 'create', $params);
        CRM_Core_Session::setStatus(E::ts('Product created successfully.'), E::ts('Success'), 'success');
      }

      // Redirect to product list or view
      $url = CRM_Utils_System::url('civicrm/inventory/products', 'reset=1');
      CRM_Utils_System::redirect($url);
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), E::ts('Error'), 'error');
    }
  }

  /**
   * Handle file uploads
   *
   * @param array $params
   */
  private function handleFileUploads(&$params) {
    $uploadDir = CRM_Core_Config::singleton()->customFileUploadDir;

    foreach (['image_actual', 'image_thumbnail'] as $field) {
      if (!empty($_FILES[$field]['name'])) {
        $fileName = CRM_Utils_File::makeFileName($_FILES[$field]['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadPath)) {
          $params[$field] = $fileName;
        }
      }
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      $elementNames[] = $element->getName();
    }
    return $elementNames;
  }

}
