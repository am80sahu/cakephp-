<?php
//define BOOLEAN constants
$rate='0,1,3,5,12,18,28';
define('BOOL_TRUE', 1);
define('BOOL_FALSE', 0);
define('GST_RATE_ARRAY',$rate );


//define SITE related constants
define('SITE_NAME', 'Track Property');
define('URL_EXTENSION', 'html');
@define('ENCRYPTIONKEY', Configure::read('Security.salt'));
@define('SITE_LINK', Configure::read('URL'));
@define('HOME_PAGE_URL', '/');
@define('ROOT_URL', '');
@define('OPEING_DATE', '2017.04.01');

@define('PAGINATION_LIMIT', 50);
@define('PAGINATION_LIMIT_1', 50);
@define('DEFAULT_CREDIT_PERIOD', 30);


//define EMAIL related constants

define('CREATE_CLIENT_EMAIL_TEMPLATE_ID', 1);


//define user ROLE related constants
define('ADMIN_ROLE_ID', 1);
define('CO_ADMIN_ROLE_ID', 2);
define('MANAGER_ROLE_ID', 3);
define('OPERATOR_ROLE_ID', 4);
define('CUSTOMER_ROLE_ID', 5);
define('OFFICE_ROLE_ID', 6);
define('GODOWN_ROLE_ID', 7);
define('SHOP_ROLE_ID', 8);
define('EMPLOYEE_ROLE_ID',9);
define('ACCOUNTANT_ROLE_ID',10);


//define location related constants
define('LOCATION_SHOP', 1);
define('LOCATION_GODOWN', 2);

//define po type constants
define('DISTRIBUTOR', 1);
define('PUBLISHER', 2);

//define physical cash received type constants
define('CASH_RECEIVED', 1);
define('CASH_RETURN', 2);

//define order status
define('ORDER_STATUS_ORDERED', 0);
define('ORDER_STATUS_GRN', 1);
define('ORDER_STATUS_PURCHASE', 2);
define('ORDER_STATUS_GOODRECEIVED', 3);




// Constants for payment made for what
define('PURCHASE_PAYMENT', 1);
define('SALE_PAYMENT', 2);
define('EXPENSE_PAYMENT', 3);
define('SALE_RETURN_PAYMENT', 4);
define('PURCHASE_RETURN_PAYMENT', 5);
define('ADVANCE_PAYMENT', 6);

//define user ROLE related constants
define('INDIA_COUNTRY_ID', 1);
define('MAHARASHTRA_STATE_ID', 20);


//define user PERMISSION related constants
define('CREATE_PERMISSION_ID', 1);
define('READ_PERMISSION_ID', 2);
define('UPDATE_PERMISSION_ID', 3);
define('DELETE_PERMISSION_ID', 4);

//define upload type (image or video)
define('UPLOADTYPE_IMAGE', 0);
define('UPLOADTYPE_VIDEO', 1);

//define date type formats
 define('DBDATEFORMAT','Y-m-d');
 define('DBDATETIMEFORMAT','Y-m-d H:i:s');
 define('DATEFORMAT','d-m-Y');  
 define('DATETIMEFORMAT','d-m-Y h:i A'); 
 define('FANCYDATEFORMAT','jS M, y');  
 define('FANCYDATETIMEFORMAT','jS M, y-h:i A');  
 define('DBTIMEFORMAT','H:i:s');
 define('NUMERICMONTH','m');
 
//define Facebook App
define('FACEBOOK_APP','728208050599184');

//define Notice Alert Types
define('ALERT_TYPE_EMAIL', 1);
define('ALERT_TYPE_SMS', 2);



define('PROPERTY_AGRI_LAND', 1);
define('PROPERTY_NON_AGRI_LAND', 2);

// Constants for property package limit
define('FREE_PACKAGE_ID',1);
define('PACKAGE_TYPE_LIMITED',1);
define('PACKAGE_TYPE_UNLIMITED',2);

// Constants for payment for
define('PAID_FOR_PACKAGE',1);
define('PAID_FOR_ONE_PROPERTY',2);

// Constants for payment type
define('PAYMENT_TYPE_CASH',1);
define('PAYMENT_TYPE_CHEQUE',2);
define('PAYMENT_TYPE_ONLINE',3);
define('PAYMENT_TYPE_VOUCHER',4);

define('DELETE_NOTIFY_BEFORE','15');

// Constants related to stock transactions  
define('ST_PURCHASE','Purchase');
define('ST_SALE','Sale');
define('ST_PURCHASE_RETURN','Purchase Return');
define('ST_SALES_RETURN','Sales Return');

// Constants related to stock transactions  
define('PURCHASE_ENTRY',1);
define('PURCHASE_RETURN_ENTRY',2);
define('SALES_ENTRY',3);
define('SALES_RETURN_ENTRY',4);

// Constants related to purchase sale update  
define('PURCHASE',1);
define('PURCHASE_RETURN',2);
define('SALES',3);
define('SALES_RETURN',4);
define('STOCK_UPDATE',5);
define('RETURN_TO_GODOWN',6);
define('TRANSFER_DISPATCH',7);
define('TRANSFER_RECEIVED',8);
define('RETURN_SHOP_TO_GODOWN',9);
define('CREDIT_SALE_RETURN',10);
define('STOCK_DEATH',11);
define('PURCHSE_DELETE',12);
define('SALE_DELETE',13);
define('CREDIT_NOTE_DELETE',14);
define('DEBIT_NOTE_DELETE',15);
define('ADD_OPEING_STOCK',16);

//define('TRANSFER_RECEIVED',8);
// Constants related to Voucher Type
define('PAYMENT',1);
define('CONTRA',2);
define('RECEIPT',3);
define('GENERAL',4);
define('ADVANCE',5);
define('PURCHASE_VOUCHER',6);
define('SALE_VOUCHER',7);
define('DEBIT_NOTE_VOUCHER',8);
define('CREDIT_NOTE_VOUCHER',9);

//Constants related to grooup
define('PURCHASE_GROUP',42);
define('DIRECT_EXP_GROUP',19);
define('INDIRECT_EXP_GROUP',26);
define('INDIRECT_INCOME_GROUP',35);



//Constants related to membership
define('MEMBER_FOR_MONTH',1);
define('MEMBER_FOR_YEAR',2);

// cash received related constant
define('CASH_RECEIVED_SALE',0);
define('CASH_RECEIVED_EXCHANGE',1);
define('CASH_RECEIVED_CONTRA',2);

// report period related constant
define('DATE_WISE',1);
define('MONTH_WISE',2);
define('YEAR_WISE',3);
define('BILL_WISE',4);

//Constants related to orders distribution
define('ORDER_DIST_ENTERED',1);
define('ORDER_DIST_DISPATCHED',2);
define('ORDER_DIST_RECIEVED',3);



// Designation Related Constants
define('EXECUTIVE_DESIG',12);
// Transfer request Status related constants
define('REQUEST_CREATED',0);
define('REQUEST_SEND',1);
define('REQUEST_DISPATCH',2);
define('REQUEST_RECEIVED',3);
// Category related constants
define('BOOKS_CATEGORY',1);
define('MAGZINE_CATEGORY',36);

// Purchase Return Status related constants
define('RETURN_RECIEVED',0);
define('RETURN_SEND',1);
define('RETURN_CREATED',2);

//Boffice Assign Task Type Releated Constant
define('DAILY',1);
define('WEEKLY',2);
define('MONTHLY',3);
define('YEARLY',4);

define('DAY_EXTEND',2);
define('EXTEND_APPROVED',3);

//Sales Order Status Releated Constant
define('SO_RECEIVED',0);
define('SO_DISPATCH',1);
define('SO_BILL',2);
define('SO_CANCEL',3);

// Discount level related constant
define('DISCOUNT_LEVEL_RETAILER',3);

// ledger type related constant
define('LEDGER_TYPE_LEDGER',0);
define('GROUP_BANK_ACCOUNT_ID',15);
define('GROUP_SUNDRY_CREDITOR_ID',25);
define('GROUP_SUNDRY_DEBTOR_ID',26);
define('LEDGER_TYPE_DEBTOR',4);

// gst code state wise related constant
define('MH_GST_CODE',27);
//Gst type related constant
define('CGST_SGST',0);
define('IGST',1);

// Unit Type related constant
define('MULTIPLE',0);
define('SINGLE',1);

//0 % gst related constant
define('NILL_RATED',1);
define('EXAMPTED',2);


// Bill Print  Type related constant
define('A4',1);
define('DOT_MATRIX',2);

// Unit related constant
define('FTS',1);
define('INC',2);
define('GMS',3);
define('KGS',4);
define('QTL',5);
define('TON',6);
define('PCS',8);
define('DOZ',9);
define('CMS',10);
define('MTR',11);

// Ledger Relation constance

define('PURCHASE_EXP',1);

// Other Expances gst type constance

define('NONCONSIDER_GST',0);
define('CONSIDER_GST',1);

// Type Related Constant
define('GOODS_TYPE',0);
define('SERVICES_TYPE',1);

// Type Related Constant
define('DEALER_TYPE_REGULAR',0);
define('DEALER_TYPE_COMPOSITION',1);

//Ledger is debit or credit
define('LEDGER_IS_DEBIT',1);
define('LEDGER_IS_CREDIT',2);

//Voucher Details Reporting time
define('REPORTING_VOUCHER',0);
define('REPORTING_PURCHASE',2);
define('REPORTING_SALE',3);
define('REPORTING_DEBIT_NOTE',4);
define('REPORTING_CREDIT_NOTE',5);

//Constants related to default ledger
define('CASH_LEDGER',1);
define('SALE_SGST_LEDGER',2);
define('SALE_CGST_LEDGER',3);
define('SALE_IGST_LEDGER',4);
define('PURCHASE_SGST_LEDGER',5);
define('PURCHASE_CGST_LEDGER',6);
define('PURCHASE_IGST_LEDGER',7);
define('ROUNDUP_LEDGER',8);
define('PURCHASE_LEDGER',9);
define('SALE_LEDGER',10);
define('PURCHASE_CESS_LEDGER',11);
define('SALE_CESS_LEDGER',12);