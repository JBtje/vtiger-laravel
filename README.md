# Vtiger 7.4.0 open source (Laravel 8 Package)

_This package might work with older versions of Vtiger, and possibly with laravel 6 or 7._

BerliCRM (based on Vtiger) has a better documentation, files are added in the [documentation](documentation) folder.

Use the Vtiger webservice (REST) API from within Laravel for the following operations.

- ListTypes
- Create
- Retrieve
- Update
- Delete
- Search
- Query
- Describe

See [Third Party App Integration (REST APIs)](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html)

## Installation, Configuration and Usage

### Installing

1. In order to install the Vtiger package in your Laravel project, just run the composer require command from your
   terminal:

    ```
    composer require "jbtje/vtiger-laravel"
    ```

2. Add ENV variables:
    ```
    VTIGER_URL=https://your-crm-domain.com/webservice.php
    VTIGER_USERNAME=
    VTIGER_KEY=
    VTIGER_PERSISTENT=true
    VTIGER_RETRIES=10
    ```

_**Username** and **Access Key** can be found within Vtiger under **My Preferences**_

3. Optional: Publish the configuration file:

    ```
    php artisan vendor:publish --tag="vtiger"
    ```

### Configuration

You can create a new user in Vtiger for the API to use, or use an existing user.

### Usage

Include the Vtiger package in your controller:

```php
use JBtje\VtigerLaravel\Vtiger;
```

#### ListTypes

List types is a command to provide you with all possible types the Vtiger CRM supports. For each type, you can run
the `describe()` command, to obtain the data structure.

Obtain all the list types:

```php
$vtiger = new Vtiger();
$data = $vtiger->listTypes();
```

<details>
  <summary>For a clean install, this will return: (click to expand)</summary>

```json
  {
    "success": true,
    "result": {
        "types": [
            "Campaigns",
            "Vendors",
            "Faq",
            "Quotes",
            "PurchaseOrder",
            "SalesOrder",
            "Invoice",
            "PriceBooks",
            "Calendar",
            "Leads",
            "Accounts",
            "Contacts",
            "Potentials",
            "Products",
            "Documents",
            "Emails",
            "HelpDesk",
            "Events",
            "Users",
            "PBXManager",
            "ServiceContracts",
            "Services",
            "Assets",
            "ModComments",
            "ProjectMilestone",
            "ProjectTask",
            "Project",
            "SMSNotifier",
            "Groups",
            "Currency",
            "DocumentFolders",
            "CompanyDetails",
            "LineItem",
            "Tax",
            "ProductTaxes"
        ],
        "information": {
            "Campaigns": {
                "isEntity": true,
                "label": "Campaigns",
                "singular": "Campaign"
            },
            "Vendors": {
                "isEntity": true,
                "label": "Vendors",
                "singular": "Vendor"
            },
            "Faq": {
                "isEntity": true,
                "label": "FAQ",
                "singular": "FAQ"
            },
            "Quotes": {
                "isEntity": true,
                "label": "Quotes",
                "singular": "Quote"
            },
            "PurchaseOrder": {
                "isEntity": true,
                "label": "Purchase Orders",
                "singular": "Purchase Order"
            },
            "SalesOrder": {
                "isEntity": true,
                "label": "Sales Orders",
                "singular": "Sales Order"
            },
            "Invoice": {
                "isEntity": true,
                "label": "Invoices",
                "singular": "Invoice"
            },
            "PriceBooks": {
                "isEntity": true,
                "label": "Price Books",
                "singular": "Price Book"
            },
            "Calendar": {
                "isEntity": true,
                "label": "Calendar",
                "singular": "Task"
            },
            "Leads": {
                "isEntity": true,
                "label": "Leads",
                "singular": "Lead"
            },
            "Accounts": {
                "isEntity": true,
                "label": "Organizations",
                "singular": "Organization"
            },
            "Contacts": {
                "isEntity": true,
                "label": "Contacts",
                "singular": "Contact"
            },
            "Potentials": {
                "isEntity": true,
                "label": "Opportunities",
                "singular": "Opportunity"
            },
            "Products": {
                "isEntity": true,
                "label": "Products",
                "singular": "Product"
            },
            "Documents": {
                "isEntity": true,
                "label": "Documents",
                "singular": "Document"
            },
            "Emails": {
                "isEntity": true,
                "label": "Emails",
                "singular": "Email"
            },
            "HelpDesk": {
                "isEntity": true,
                "label": "Tickets",
                "singular": "Ticket"
            },
            "Events": {
                "isEntity": true,
                "label": "Events",
                "singular": "Event"
            },
            "Users": {
                "isEntity": true,
                "label": "Users",
                "singular": "User"
            },
            "PBXManager": {
                "isEntity": true,
                "label": "PBX Manager",
                "singular": "Call Record"
            },
            "ServiceContracts": {
                "isEntity": true,
                "label": "Service Contracts",
                "singular": "Service Contract"
            },
            "Services": {
                "isEntity": true,
                "label": "Services",
                "singular": "Service"
            },
            "Assets": {
                "isEntity": true,
                "label": "Assets",
                "singular": "Asset"
            },
            "ModComments": {
                "isEntity": true,
                "label": "Comments",
                "singular": "Comment"
            },
            "ProjectMilestone": {
                "isEntity": true,
                "label": "Project Milestones",
                "singular": "Project Milestone"
            },
            "ProjectTask": {
                "isEntity": true,
                "label": "Project Tasks",
                "singular": "Project Task"
            },
            "Project": {
                "isEntity": true,
                "label": "Projects",
                "singular": "Project"
            },
            "SMSNotifier": {
                "isEntity": true,
                "label": "SMS Notifier",
                "singular": "SMS Notifier"
            },
            "Groups": {
                "isEntity": false,
                "label": "Groups",
                "singular": "Groups"
            },
            "Currency": {
                "isEntity": false,
                "label": "Currency",
                "singular": "Currency"
            },
            "DocumentFolders": {
                "isEntity": false,
                "label": "DocumentFolders",
                "singular": "DocumentFolders"
            },
            "CompanyDetails": {
                "isEntity": false,
                "label": "CompanyDetails",
                "singular": "CompanyDetails"
            },
            "LineItem": {
                "isEntity": false,
                "label": "LineItem",
                "singular": "LineItem"
            },
            "Tax": {
                "isEntity": false,
                "label": "Tax",
                "singular": "Tax"
            },
            "ProductTaxes": {
                "isEntity": false,
                "label": "ProductTaxes",
                "singular": "ProductTaxes"
            }
        }
    }
}
```

</details>

#### Describe

To obtain the data sctructure of a module in Vtiger, run the describe method with the module name. Module names can be
obtained using `listTypes()`

```php
$vtiger = new Vtiger();
$data = $vtiger->describe( 'Contacts' );
```

<details>
  <summary>Partial result for "Contacts": (click to expand)</summary>

```json
{
    "success": true,
    "result": {
        "label": "Contacts",
        "name": "Contacts",
        "createable": true,
        "updateable": true,
        "deleteable": true,
        "retrieveable": true,
        "fields": [
            {
                "name": "firstname",
                "label": "First Name",
                "mandatory": false,
                "type": {
                    "name": "string"
                },
                "isunique": false,
                "nullable": true,
                "editable": true,
                "default": ""
            },
            {
                "name": "lastname",
                "label": "Last Name",
                "mandatory": true,
                "type": {
                    "name": "string"
                },
                "isunique": false,
                "nullable": false,
                "editable": true,
                "default": ""
            },
            {
                "name": "assigned_user_id",
                "label": "Assigned To",
                "mandatory": true,
                "type": {
                    "name": "owner"
                },
                "isunique": false,
                "nullable": false,
                "editable": true,
                "default": ""
            },
            ...
        ],
        "idPrefix": "12",
        "isEntity": true,
        "allowDuplicates": true,
        "labelFields": "firstname,lastname"
    }
}
```

_Please note the `mandatory` field._
</details>

#### Create

To insert a record into the CRM, first create an array of data to insert. Using `describe()`, you can see which fields
are mandatory.

```php
$vtiger = new Vtiger();
$data = [
    'assigned_user_id' => '4x1',
    ...
];
$data = $vtiger->create( $MODULE_NAME, json_encode( $data ) );
```

#### Retrieve

To retrieve a record from the CRM, you need the id of the record you want to find (i.e. '4x1').

```php
$vtiger = new Vtiger();
$data = $vtiger->retrieve( '4x1' );
```

#### Update

The easiest way to update a record in the CRM is to retrieve the record first.

```php
$vtiger = new Vtiger();
$obj = $vtiger->retrieve( '4x1' );
```

Then update the object:

```php
$obj->result->field_name = 'Your new value';
$data = $vtiger->update( $obj->result );
```

#### Delete

To delete a record from the CRM, you need the id of the record you want to delete (e.g. '4x1').

```php
$vtiger = new Vtiger();
$data = $vtiger->delete( '4x1' );
```

#### Lookup

This function uses the Vtiger Lookup API endpoint to search for a single piece of information within multiple columns of
a Vtiger module. This function is often multitudes faster than the search function.

```php
$dataType = 'phone';
$phoneNumber = '1234567890';
$module = 'Leads';
$columns = ['phone', 'fax']; // Must be an array
    
$vtiger = new Vtiger();
$data = $vtiger->lookup( $dataType, $phoneNumber, $module, $columns );
```

#### Search

This function is a sql query builder wrapped around the query function. Accepts instance of laravels QueryBuilder.

```php
$vtiger = new Vtiger();
$query = DB::table( 'Leads' )->select( 'id', 'firstname', 'lastname' )->where( 'firstname', 'John' );
$data = $vtiger->search( $query );

foreach( $data->result as $result ) {
    // Do something
}
```

By default, the function will quote but not escape your inputs, if you wish for your data to not be quoted, set the 2nd
paramater to false:

```php
$vtiger = new Vtiger();
$data = $vtiger->search( $query, false );
```

Also keep in mind that Vtiger has several limitations on it's sql query capabilities. You can not use conditional
grouping i.e "where (firstname = 'John' AND 'lastname = 'Doe') OR (firstname = 'Jane' AND lastname = 'Smith')" will
fail.

#### Query

To use
the [Query Operation](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html#query-operation)
, you first need to create the SQL query.

```php
$query = "SELECT * FROM ModuleName;";
```

Then run the query...

```php
$vtiger = new Vtiger();
$data = $vtiger->query($query);

foreach( $data->result as $result ) {
    // Do something
}
```

## Contributing

Please report any issue you find in the issues page. Pull requests are more than welcome.

## License

This project is licensed under the MIT licence - see the [LICENSE.md](LICENSE.md) file for details

## Contributors

This package is based upon [Clystnet/Vtiger](https://github.com/Clystnet/Vtiger)

- [Ahmad Syamim](https://www.syamim.ascube.net)
- [Clyde Cox](https://github.com/cjcox17)
- [Christopher Pratt](https://www.clystnet.com)
- [Adam Godfrey](https://github.com/adam-godfrey)

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification.
Contributions of any kind welcome!
