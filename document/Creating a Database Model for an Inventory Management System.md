**Creating a Database Model for an Inventory Management System**

[https://github.com/Skvare/com.skvare.inventory](https://github.com/Skvare/com.skvare.inventory)

**Product & ProductVariant**

**Products** are the starting point for designing our system. Each industry or business line will have different product attributes (e.g. clothes have material, size, and color, while cars have color, trim level, engine type, etc.).

We can group product attributes into two subsets: generic attributes and storage attributes. Let’s start with the generic attributes for the **Product entity**: civicrm\_inventory\_product

    ProductID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER If you want to learn more about primary identifiers and additional identifiers (which become unique keys), read the article What Is a Primary Key?.  
    ProductCode: Besides the ProductID, products are usually identified by an internal code (also called an SKU or Stock Keeping Unit). This code consists of letters and numbers that identify characteristics about each product, such as manufacturer, brand, style, color, and size. This is also an additional identifier. We will use a VARCHAR(100) datatype for this attribute.  
    ExternalCode: Any Extra code for product.  
    ProductName: The product’s n We will use a VARCHAR(100) datatype.  
    ProductDescription: A more detailed description of the product. We will use a VARCHAR(2000) datatype.  
    ProductBrand: The product’s brand. We will use a VARCHAR(100) datatype.  
    ProductNote: Product Notes to show more information about the product.  
    ProductCategory: The product’s category. We will use a VARCHAR(100) datatype.  
    IsProductDisabled: If you want to stop showing inventory, enable this flag.  
    IsProductDiscontinued: if you want to stop selling products, enable this flag.  
    Image: File path of product images.  
      
**ProductVariant: civicrm\_inventory\_product\_variant**  
    ProductVariantID: This will be a unique ID number and the primary identifier of the entity.  
    ProductID : FK to product table.  
    ListedPrice: Product price.  
    CurrentPrice: Current Selling Price  
    ProductVariantDetail: Details about variant.   
    IsProductVariantDisabled: If you want to stop showing inventory, enable this flag.  
    IsProductVariantDiscontinued: if you want to stop selling products, enable this flag.  
    PackedWeight: Product’s weight, including packaging. This may be required to define storage location. We will use the DECIMAL(10,2)  
    PackedHeight: Product’s height, including packaging. This may be required to define storage location. We will use the DECIMAL(10,2)  
    PackedWidth: Product’s width, including packaging. This may be required to define storage location. We will use the DECIMAL(10,2)  
    PackedDepth: Product’s depth, including packaging. This may be required to define storage location. We will use the DECIMAL(10,2)  
    ThumbnailImage: Thumbnail image to variant product.  
    Image: Image to Variant product.  
    UnitofMeasure: A unit of measure (UoM) is defined as the standard units of measurements used when accounting for stock, and expressing them in quantities. Feet, pounds, and gallons are all examples of units of measure.  
product\_variant\_unique\_id:Product Variant Unique ID  
product\_variant\_battery:Product Battery  
product\_variant\_speed:Product Speed (Device speed)  
tether:Tether  
powerbank:Power Bank  
batteryless:Battery Less  
network\_4g:Network 4g  
network\_5g:Network 5g  
has\_sim:Has SIM  
has\_device:Has Device  
warranty\_type\_id:Warranty Type  
product\_variant\_status:Product Status  
replaced\_product\_id:Replaced Product ID

	Custom Fields:  
		MembershipID : Fk to civicrm\_membership  
		Warranty Start Date  
		Warranty End Date  
		Phone Number linked to Device  
		Is Suspended?  
 		Is Replacement?  
		Old Device Primary ID of the same table.  
**Premiums : This will be part of the Product table itself.**

**Warehouse: civicrm\_inventory\_warehouse**

This entity represents the actual storage area inside a Location. It has the following basic attributes:

    WarehouseID: This will be a unique ID number and primary identifier (later the surrogate primary key) of the entity. We will use an INTEGER datatype.  
    WarehouseName: The name of the w We will use a VARCHAR(100) datatype.  
    address\_id : FK to address table.  
    IsRefrigerated: This attribute indicates if the warehouse has refrigeration. We will use a BOOLEAN data type.  
    Size: Warehouse Available Size  
    Unused Size: Warehouse Unused Size

**Inventory: civicrm\_inventory**

This entity represents the relationship between products and warehouses. Each product may exist in several Warehouses, and each warehouse may contain many different products. Besides the relationship, we need to store additional data (like the quantity of that product available), so we are going to create an entity that represents this relationship. The basic attributes are:

    InventoryID: This will be a unique ID number and the primary identifier (later the surrogate primary key) of the entity. We will use an INTEGER datatype.  
    ProductVariantCode: This is the FK of the Option ValueTable. Same Code is present in civicrm\_inventory\_product\_variant and civicrm\_inventory table.  
    WarehouseID : where product is kept.  
    QuantityAvailable: The quantity on hand for that We will use an INTEGER datatype.  
    MinimumStockLevel: The minimum number of units required to ensure no shortages occur at this warehouse. We will use an INTEGER  
    MaximumStockLevel: The maximum number of units desired in stock, i.e. to avoid overstocking. We will use an INTEGER  
    ReorderPoint: When the number of product units reaches this level, a purchase order must be generated. This threshold is somewhere between the minimum and maximum levels and should take into account the time between sending a purchase order and the new products arrival to avoid getting under the MinimumStockLevel. We will use an INTEGER  
    RowID: This is related to the inside warehouse to locate products.  
    Shelf: Use to locate the item in the warehouse.

Note: Each Inventory is related to a Warehouse and a Product.

**Supplier : civicrm\_inventory\_supplier**

Organizations purchase products from suppliers, so we need to store some basic information about these suppliers. We will focus only on those attributes required for inventory management:

SupplierID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER datatype.  
SupplierName: The Supplier’s We will use a VARCHAR(100) datatype.  
SupplierAddressID: address is stored in civicrm\_address table.  
ContactID : Contact is stored in civicrm\_contact table with subtype as supplier

**PurchaseOrder & PurchaseOrderDetail (Purchase order from Supplier):**

When companies purchase products from a Supplier, they include information about the places (warehouses) where the products will be stored and quantities that need to be delivered. This information is stored in the following two entities.

**PurchaseOrder : civicrm\_inventory\_purchase\_order**

    OrderID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER datatype.  
    OrderDate: This is the date when the order was generated.  
    contactID: FK to civicrm\_contact table.  
    SupplierID: Fk to supplier table.

**PurchaseOrderDetail : civicrm\_inventory\_purchase\_order\_detail**

    OrderDetailID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER datatype.  
    OrderID : FK to order table.  
    ProductVariantID : FK to product Variant table.  
    SupplierID: Fk to supplier table.  
    WarehouseID: FK to warehouse table.  
    OrderQuantity: The amount of a specific product ordered for a specific warehouse, We will use an INTEGER datatype.  
    ExpectedDate: The date when the products should arrive at the warehouse, We will use a DATE datatype.  
    ActualDate: The date when the products were received at warehouse, We will use a DATE datatype.

Note: Each Order is related to a Supplier and may include several OrderDetails. Each of them represents the expected quantity of a Product in a Warehouse.

**Sales & SalesDetail**

Once we sell products to a customer, the inventory management system generates a sales request. It may include different products from different warehouses, depending on products’ availability and warehouses’ proximity to the customer’s address. This information is stored in the following two entities.  
**Sales :  civicrm\_inventory\_sales**

    SaleID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER datatype.  
    ContactID: FK to civicrm contact table.  
    SalesDate: This is the date when the sale was made and the sale request was generated.  
    StatusID : Sales Current status  
    ShipmentID: Fk to Shipment table  
    isFulFilled: Is fulfilled  
    needShipment: Does this sale need shipment? : o  
    hasAssignment: Has Assignment? : Product assignment by admin  
    isTrackingSent: Is Tracking Sent?

**SalesDetail : civicrm\_inventory\_sales\_detail**

    SalesDetailID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER datatype.  
    SalesID: FK to sales table.  
    ProductVariantID: FK to product Variant table.  
    WarehouseID: FK to warehouse table.  
    ProductQuantity: The amount of a specific product to be delivered from a specific warehouse, We will use an INTEGER datatype.  
    ~~ProductUniqueID: Product Unique ID if present.~~ (Moved to product variable table)  
    PurchasePrice: Price at product is purchased.  
    ProductTitle: Title of product  
    ProductSubTitle  
    ProductType   
    AdditionalDetails (mostly for shirts or other free items).  
    MemberhipID: Is membership ID associated with any product  
    ContributionID: Contribution ID linked with order.

Note: Each Sales request is related to a Customer and may include several SalesDetails. Each of them represents the expected quantity of a Product to be sent from each Warehouse.

**Order : NA**

**Donations? : NA**

**Taxes : NA**

**Shipping / Shipments Labels: civicrm\_inventory\_shipment**  
PrimaryID : auto increment  
ShipmentCreatedBy: contactID  
CreatedDate : Shipment Created date  
	Modified By Contact ID: Shipment Modified Contact ID.  
    	UpdatedDate: Shipment Update Date  
	ShipmentDate: Shipment Date  
	IsShipped? : Is Shipment Shipped ?  
	IsFinished: Is Shipmented Completed?

**Shipment Labels: civicrm\_inventory\_shipment\_labels**  
	Primary ID : Auto increment  
	CreatedDate: Created Date  
UpdateDate  
SaleID : FK civicrm\_inventory\_sales  
IsValid  
IsPaid  
HasError  
Provider : Shipment Provider  
Amount : Shipping Label Cost  
Currency  
ResourceID: This is   
TrackingID : Mostly Tracking URL provided by shippo or other..  
ShipmentDetails:  
PurchaseDetails:	

**Referrals : civicrm\_inventory\_referrals**  
	PrimaryID : auto increment  
	CreatedID : contactID  
	ConsumerID: Redeem ContactID  
	CreatedDate  
	StartDate  
	EndDate (expiry date)  
	Referral Code: Code redeemed by consumer.

**Change Logs : civicrm\_inventory\_product\_changelog**  
	PrimaryID : auto increment  
	ModifiedBy : Contact ID  
	ProductID: FK to product variant table  
	BatchID:   
	CreatedDate: Change log date.  
	Status: UPDATE,REACTIVATE,TERMINATE,SUSPEND  
	

**Transfer: civicrm\_inventory\_warehouse\_transfer**  
There are situations when some products need to be transferred from one warehouse to another. This kind of operation is registered as a Transfer with the following attributes:

    TransferID: This will be a unique ID number and the primary identifier of the entity. We will use an INTEGER datatype.  
    LotID : Custom ID for batch  
    ProductVariantID : FK to product Variant table.  
    FromWarehouseID: FK to warehouse table.  
    ToWarehouseID: FK to warehouse table.  
    FromContactID: FK to contact  
    ToContactID: FK to contact.  
    CreatedDate: Date when entry is created.  
    StatusID: Status ID of entry  
    StatusDate: Daten when Status is updated.  
    FromStockQuantity: Stock Quantity Sent.  
    ToStockQuantity: Stock Quantity Received.

**Relationships Between Entities**

**Supplier – Order**  
Each Order in our system is assigned to a Supplier, but not all Suppliers may have orders. We need to establish a 1:N (one-to-many) relationship between the two tables, with N being 0, 1, or more.

**Order – OrderDetail**  
This is a classic 1:N relationship between two entities, with N being 1 or more since there cannot be orders without at least one OrderDetail.

**Customer – Sales**  
Each Sales in our system is assigned to a Customer, but not all customers may have sales. We need to establish a 1:N relationship between the two tables where N is 0, 1, or more.

**Sales – SalesDetail**  
Another example of a classic 1:N relationship. Each Sales must have at least one SalesDetail, and each detail belongs to one and only one Sale.

**Location – Warehouse**  
Each Location can have one or more Warehouses, so we need to define this as a 1:N relationship. Both sides are mandatory, since it is illogical to have a Location without a Warehouse and vice versa.

**ProductVariant – OrderDetail**  
This is a 1:N relationship, where each OrderDetail must have an associated Product and each Product may be included in 0, 1, or many Orders.

**ProductVariant – SalesDetail**  
This is a 1:N relationship, where each SalesDetail must have an associated Product and each Product may be included in 0, 1, or many SalesDetails.

**Warehouse – OrderDetail**  
Another 1:N relationship, where each OrderDetail is associated with a Warehouse and each Warehouse can have 0, 1, or many OrderDetails.

**Warehouse – SalesDetail**  
Another 1:N relationship, where each SalesDetails is associated with a Warehouse, and each Warehouse can have 0, 1, or many SalesDetails.

**ProductVariant – Inventory**  
This is a 1:N relationship, since each ProductVariant may have stock in 0, 1 or many warehouses, represented here as Inventory. We need to remember that Inventory is an intermediate entity created to resolve a many-to-many relationship between ProductVariant and Warehouses.

**Warehouse – Inventory**  
This is a 1:N relationship, since each Warehouse may store 0, 1, or many products (represented here as Inventory).

**ProductVariant – Transfer**  
This is another 1:N relationship, since each Product may appear in 0, 1, or many transfers, and each Transfer consists of one and only one Product.

**Warehouse – Transfer**  
This is a tricky relationship, since there are actually two relationships between these two entities. Each Transfer is related to:  
    A “source” Warehouse. This is the warehouse where the products were originally  
    A “destination” Warehouse. This is the warehouse where the products are being transferred.