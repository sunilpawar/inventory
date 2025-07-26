**Workflow**. [https://projects.skvare.com/issues/21945](https://projects.skvare.com/issues/21945) 

* **Entity Type : InventoryWarehouse**  
  * We Should have a Warehouse entry (1 or more).  
  * It require address id.  
  * Table   
    * civicrm\_inventory\_warehouse  
* **Entity Type: MembershipType**  
  * Setup Which Membership Type available USA, CAN etc. ([https://projects.skvare.com/issues/22204](https://projects.skvare.com/issues/22204) )  
  * Table  
    * civicrm\_membership\_type (we are extending membership type field)  
* **Entity Type :**   
  * **InventoryProduct**  
  * **InventoryProductVariant**  
  * **InventoryCategory**  
  * **Inventory**  
    * **Create a category first.**  
    * **Create Product.**  
    * **Create Product Variant.**  
    * Import the Product with their Variation.  
      * Table:  
        * Civicrm\_inventory\_product  
        * Civicrm\_inventory\_product\_variant  
        * Civicrm\_inventory  
        * civicrm\_inventory\_category

    


  

* **Entity Type : InventoryProductMembership**  
  * Map Membership Type Product. (we have a countable product).   
  * [https://projects.skvare.com/issues/21589](https://projects.skvare.com/issues/21589)  
  * This table maps the product(device) with membership types ( 1 to N)  
    * Table  
      * Civicrm\_inventory\_product\_membership

* **Entity Type : InventorySales**  
  * Tables:  
    * civicrm\_inventory\_sales   
      * Link with civicrm\_contribution  
        * Link with civicrm\_inventory\_shipment  
        * civicrm\_inventory\_sales\_detail  
        * Link with civicrm\_membership.  
        * Link with civicrm\_inventory\_product\_variant  
        *   
  * Customers can buy one product at a time.  
  * They can buy another product in a separate signup process, products belong to the same/different membership types.  
  * Customers can have multiple memberships of the same types.  
  * In Case of installment we need to have a supported payment processor and its codebase for civicrm.  
  * In case autopay for membership: We have to check the amount for membership renewal, it may not be the same as the customer paying on first time device purchase. [https://projects.skvare.com/issues/22152](https://projects.skvare.com/issues/22152) (auto renewal)  
  * Business logic to get the right amount for each item in sales order based on user actions. [https://projects.skvare.com/issues/22052](https://projects.skvare.com/issues/22052)  (including tax receipt)  
  * Payment processor : [https://projects.skvare.com/issues/22023](https://projects.skvare.com/issues/22023) 

* **Entity Type**   
  * **InventoryShipment**  
  * **InventoryShipmentLabels**  
    * **Shipping**  
      * Devices are scanned and assigned to order in civicrm\_inventory\_sales table.  
      * Shipments are done using batches with 40-50 orders in each batch.  
      * Labels are generated once Shipment is frozen.  
      * Each order gets shipping labels from the shipping service provider. It includes tracking urls too.  
      * Labels are printed in 2x2 or 2x4 size in a pdf file then printed on a sticky page.  
      * Shipping label scanned (bar code) and assigned to order record.  
      * And it sticks on packages.  
      * Recipe is generated with all warranty details present and put in the package. ([warranty](https://projects.skvare.com/issues/22513) )  
      * Once all orders have labels. Change the shipping status. It's ready for shipment.  
      * Once shipment is delivered, using API we can update shipping status to ‘completed\` (need to check number of status required on each step).  
      * Product are assigned to Sale ID  
        * Table:  
          * Civicrm\_inventory\_shipment  
          * Civicrm\_inventory\_shipment\_labels

* **Entity Type : InventoryProductChangeLog**  
  * ([https://projects.skvare.com/issues/22185](https://projects.skvare.com/issues/22185) [https://projects.skvare.com/issues/22438](https://projects.skvare.com/issues/22438) )  
  * If a customer required device replacement.  
  * Fault product is sent.  
  * Membership is expired  
  * Expired Membership renewed.

		Then we prepare the CSV with appropriate status and send it to mobile citizens to activate,de-activate, and suspend service of the device.

* Table  
  * civicrm\_inventory\_product\_changelog  
  * Civicrm\_inventory\_product\_variant