[![No Maintenance Intended](http://unmaintained.tech/badge.svg)](http://unmaintained.tech/)

# DEPRECATED

This is no longer supported.

Please consider migrating to [phpjaster](https://github.com/PHPJasper/phpjasper).

# JasperReports for PHP

Package to generate reports with [JasperReports 6](http://community.jaspersoft.com/project/jasperreports-library) library through [JasperStarter v3](http://jasperstarter.sourceforge.net/) command-line tool.

## Install

```
composer require cossou/jasperphp
```

## Introduction

This package aims to be a solution to compile and process JasperReports (.jrxml & .jasper files).

### Why?

Did you ever had to create a good looking Invoice with a lot of fields for your great web app?

I had to, and the solutions out there were not perfect. Generating *HTML* + *CSS* to make a *PDF*? WTF? That doesn't make any sense! :)

Then I found **JasperReports** the best open source solution for reporting.

### What can I do with this?

Well, everything. JasperReports is a powerful tool for **reporting** and **BI**.

**From their website:**

> The JasperReports Library is the world's most popular open source reporting engine. It is entirely written in Java and it is able to use data coming from any kind of data source and produce pixel-perfect documents that can be viewed, printed or exported in a variety of document formats including HTML, PDF, Excel, OpenOffice and Word.

I recommend using [Jaspersoft Studio](http://community.jaspersoft.com/project/jaspersoft-studio) to build your reports, connect it to your datasource (ex: MySQL), loop thru the results and output it to PDF, XLS, DOC, RTF, ODF, etc.

*Some examples of what you can do:*

* Invoices
* Reports
* Listings

## Examples

### The *Hello World* example.

Go to the examples directory in the root of the repository (`vendor/cossou/jasperphp/examples`).
Open the `hello_world.jrxml` file with iReport or with your favorite text editor and take a look at the source code.

#### Compiling

First we need to compile our `JRXML` file into a `JASPER` binary file. We just have to do this one time.

**Note:** You don't need to do this step if you are using *Jaspersoft Studio*. You can compile directly within the program.

```php
JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/hello_world.jrxml'))->execute();
```

This command will compile the `hello_world.jrxml` source file to a `hello_world.jasper` file.

**Note:** If you are using Laravel 4 run `php artisan tinker` and copy & paste the command above.

#### Processing

Now lets process the report that we compile before:

```php
JasperPHP::process(
	base_path('/vendor/cossou/jasperphp/examples/hello_world.jasper'),
	false,
	array('pdf', 'rtf'),
	array('php_version' => phpversion())
)->execute();
```

Now check the examples folder! :) Great right? You now have 2 files, `hello_world.pdf` and `hello_world.rtf`.

Check the *API* of the  `compile` and `process` functions in the file `src/JasperPHP/JasperPHP.php` file.

#### Listing Parameters

Querying the jasper file to examine parameters available in the given jasper report file:

```php
$output = JasperPHP::list_parameters(
		base_path('/vendor/cossou/jasperphp/examples/hello_world.jasper')
	)->execute();

foreach($output as $parameter_description)
	echo $parameter_description;
```

### Advanced example

We can also specify parameters for connecting to database:

```php
JasperPHP::process(
    base_path('/vendor/cossou/jasperphp/examples/hello_world.jasper'),
    false,
    array('pdf', 'rtf'),
    array('php_version' => phpversion()),
    array(
      'driver' => 'postgres',
      'username' => 'vagrant',
      'host' => 'localhost',
      'database' => 'samples',
      'port' => '5433',
    )
  )->execute();
```

## Requirements

* Java JDK 1.6
* PHP [exec()](http://php.net/manual/function.exec.php) function
* [optional] [Mysql Connector](http://dev.mysql.com/downloads/connector/j/) (if you want to use database)
* [optional] [Jaspersoft Studio](http://community.jaspersoft.com/project/jaspersoft-studio) (to draw and compile your reports)


## Installation

### Java

Check if you already have Java installed:

```
$ java -version
java version "1.6.0_51"
Java(TM) SE Runtime Environment (build 1.6.0_51-b11-457-11M4509)
Java HotSpot(TM) 64-Bit Server VM (build 20.51-b01-457, mixed mode)
```

If you get:

	command not found: java

Then install it with: (Ubuntu/Debian)

	$ sudo apt-get install default-jdk

Now run the `java -version` again and check if the output is ok.

### Composer

Install [Composer](http://getcomposer.org) if you don't have it.

```
composer require cossou/jasperphp
```

Or in your `composer.json` file add:

```javascript
{
    "require": {
		"cossou/jasperphp": "~2",
    }
}
```

And the just run:

	composer update

and thats it.

### Using Laravel 5?

Add `JasperPHP\JasperPHPServiceProvider::class` to config `config/app.php` in service provider

File `config/app.php`

```php
<?php
//...
'providers' => [
    //...
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    //insert jasper service provider here
    JasperPHP\JasperPHPServiceProvider::class
],

```

Uses in Controller by adding `use JasperPHP` after namespace
```php
<?php
namespace App\Http\Controllers;

use JasperPHP; // put here

class SomethingController
{
	//...

    public function generateReport()
    {        
        //jasper ready to call
        JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/hello_world.jrxml'))->execute();
    }
}    
```

Use in Route
```php
use JasperPHP\JasperPHP as JasperPHP;

Route::get('/', function () {

    $jasper = new JasperPHP;

	// Compile a JRXML to Jasper
    $jasper->compile(__DIR__ . '/../../vendor/cossou/jasperphp/examples/hello_world.jrxml')->execute();

	// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $jasper->process(
        __DIR__ . '/../../vendor/cossou/jasperphp/examples/hello_world.jasper',
        false,
        array("pdf", "rtf"),
        array("php_version" => "xxx")
    )->execute();

	// List the parameters from a Jasper file.
    $array = $jasper->list_parameters(
        __DIR__ . '/../../vendor/cossou/jasperphp/examples/hello_world.jasper'
    )->execute();

    return view('welcome');
});
```

### Using Laravel 4?

Add to your `app/config/app.php` providers array:

```php
	'JasperPHP\JasperPHPServiceProvider',
```
Now you will have the `JasperPHP` alias available.

### MySQL

We ship the [MySQL connector](http://dev.mysql.com/downloads/connector/j/) (v5.1.45) in the `/src/JasperStarter/jdbc/` directory.

### PostgreSQL

We ship the [PostgreSQL](https://jdbc.postgresql.org/) (v9.4-1212.jre6) in the `/src/JasperStarter/jdbc/` directory.

Note: Laravel uses `pgsql` driver name instead of `postgres`.

### SQLite

We ship the [SQLite](https://www.sqlite.org/) (version v056, based on SQLite 3.6.14.2) in the `/src/JasperStarter/jdbc/` directory.

```
array(
    'driver' => 'generic',
    'jdbc_driver' => 'org.sqlite.JDBC',
    'jdbc_url' => 'jdbc:sqlite:/database.sqlite'
)
```


### JSON

Source file example:

```json
{
    "result":{
        "id":26,
        "reference":"0051711080021460005",
        "account_id":1,
        "user_id":2,
        "date":"2017-11-08 00:21:46",
        "type":"",
        "gross":138,
        "discount":0,
        "tax":4.08,
        "nett":142.08,
        "details":[
            {"id":26, "line": 1, "product_id": 26 },
        ]
    },
    "options":{
        "category":[
            {"id":3,"name":"Hair care","service":0,"user_id":1, },
        ],
        "default":{
            "id":1,"name":"I Like Hairdressing",
            "description":null,
            "address":null,
            "website":"https:\/\/www.ilikehairdressing.com",
            "contact_number":"+606 601 5889",
            "country":"MY",
            "timezone":"Asia\/Kuala_Lumpur",
            "currency":"MYR",
            "time_format":"24-hours",
            "user_id":1
        }
    }
}
```

Using Laravel:

```php
	public function generateReceipt($id) {

        $datafile = base_path('/storage/jasper/data.json');
        $output = base_path('/storage/jasper/data'); //indicate the name of the output PDF
        JasperPHP::process(
                    base_path('/resources/reports/taxinvoice80.jrxml'),
                    $output,
                    array("pdf"),
                    array("msg"=>"Tax Invoice"),
                    array("driver"=>"json", "json_query" => "data", "data_file" =>  $datafile)  
                )->execute();
     }
```

Some hack to JasperReport datasource is required. You need to indicate datasource expression for each table, list, and subreport.

```xml
	<datasetRun subDataset="invoice_details" uuid="a91cc22b-9a3f-45eb-9b35-244890d35fc7">
            <dataSourceExpression>
	       <![CDATA[((net.sf.jasperreports.engine.data.JsonDataSource)$P{REPORT_DATA_SOURCE}).subDataSource("result.details")]]>
	    </dataSourceExpression>
	</datasetRun>
```

## Performance

Depends on the complexity, amount of data and the resources of your machine (let me know your use case).

I have a report that generates a *Invoice* with a DB connection, images and multiple pages and it takes about **3/4 seconds** to process. I suggest that you use a worker to generate the reports in the background.

## Thanks

Thanks to [Cenote GmbH](http://www.cenote.de/) for the [JasperStarter](http://jasperstarter.sourceforge.net/) tool.

## Questions?

Drop me a line on Twitter [@cossou](https://twitter.com/cossou).

## License

MIT
