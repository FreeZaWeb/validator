# Validator

Quite simple, but at the same time a simple validation class. Can validate incoming POST/GET/custom array data

### Installation

```bash
composer require freezaweb/validator
```

### Simple useage

```php
require('vendor/autoload.php');

// Demo data
$_POST['name'] =            'Alex';
$_POST['age']  =            30;
$_POST['float_val'] =       1.6;
$_POST['email'] =           'correctmail@gmail.com';
$_POST['phone'] =           '8(916) 765-43 -21';
$_POST['custom_phone'] =    '8(916) 123-45 -67';
$_POST['date'] =            '2022-01-15';
$_POST['date_custom'] =     '2022-01-15';

$rules = [
    'name' => ['req', 'str', ['min', 3], ['min', 12]],
    'age' => ['require', 'int', ['min', 1], ['min', 3]],
    'float_val' => ['req', 'float'],
    'email' => ['req', 'email'],
    'phone' => ['req', 'phone'], // default convert string to russian format mobile phone (79996663300)
    'custom_phone' => ['req', ['phone', 'custom_phone_format_fn_name']],
    'date' => ['req', 'date'], // default check format Y-m-d H:i:s
    'date_custom' => ['req', ['date', 'd.m.Y']], // custom date format
];

$fields = validator::ALL_POST($rules);
// if incoming POST

if($fields->POST()){

    if($fields->errors){
        // fields has errors
        var_dump($fields->errors);
    }else{
        var_dump($fields->get_fields());
        // do something
    }

}

```

### Basic validator functions

```php 
$array_for_validation = []; // or $_POST or $_GET

$rules = [
    'name'  => ['req', 'str', ['min', 3], ['min', 12]],
    'age'   => ['require', 'int'],
    'email' => ['req', 'email'],
    'date'  => ['req', 'date'],
];

$fields = validator::ALL_POST($rules); // $_POST
//OR
$fields = validator::ALL_GET($rules); // $_GET
//OR
$fields = validator::CUSTOM($array_for_validation, $rules);


if($fields->POST()){ // if $_POST request OR $fields->GET()

    //replace if incorrect field after validate ($key => $value) and clear error stack history for this field
    $fields->on_error_replace([
        'date' => new DateTime(),
    ]);

    if($fields->errors){ // check array of errors
        var_dump($fields->errors);
    }else{

        $fields->add_field('role', 'user');
        $fields->delete_field('age');

        $fields->add_fields([
            'key_1' => 'val_1',
            'key_2' => 'val_2',
        ]);

        $array_of_fields = $fields->get_fields();
        /*
        $array_of_fields = [
            'name'  => 'value',
            'email' => 'value',
            'date' =>  'DateTime Obj',
            'role' =>  'user',
            'key_1' =>  'val_1',
            'key_2' =>  'val_2',
        ];
        */

        // OR use validator object
        echo $fields->name .PHP_EOL;
        echo $fields->email .PHP_EOL;
        echo $fields->date .PHP_EOL;
        echo $fields->role .PHP_EOL;
        echo $fields->key_1 .PHP_EOL;
        echo $fields->key_2 .PHP_EOL;

    }
}
```

### Field Types

**bool** - Boolien value type (if incorrect field return null)

```php 
$data = [
    'bool1' => true,        // return boolean true
    'bool2' => 'true',      // return boolean true
    'bool3' => 1,           // return boolean true
    'bool4' => '1',         // return boolean true
    'bool5' => false,       // return boolean false
    'bool6' => 'false',     // return boolean false
    'bool7' => 0,           // return boolean false
    'bool8' => '0',         // return boolean false
    'bool9' => 'null',      // error validation => return null
    'bool10' => null,       // return boolean false
];

$rules = [
    'bool1' => ['bool'],
    'bool2' => ['bool'],
    'bool3' => ['bool'],
    'bool4' => ['bool'],
    'bool5' => ['bool'],
    'bool6' => ['bool'],
    'bool7' => ['bool'],
    'bool8' => ['bool'],
    'bool9' => ['bool'],
    'bool10' => ['bool'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**str** - String value type
```php
$data = [
    'str1' => '   Text Data  () ! - _ = & # @ \' " ` /0',
];

$rules = [
    'str1' => ['str'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors); //null
var_dump($fields->get_fields()); // Text Data  () ! - _ = &amp; # @ ' &quot; ` /0
```

**int** - Integer value type (if incorrect field return integer 0)
```php 
$data = [
    'int1' => 1,       // return integer 1
    'int2' => '1',     // return integer 1
    'int3' => 0,       // return integer 0
    'int4' => '0',     // return integer 0
    'int5' => null,    // return integer 0
    'int6' => 'null',  // error validation => return integer 0
];

$rules = [
    'int1' => ['int'],
    'int2' => ['int'],
    'int3' => ['int'],
    'int4' => ['int'],
    'int5' => ['int'],
    'int6' => ['int'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**float** - Float value type (if incorrect field return boolean false)
```php 
$data = [
    'float1' => 1.10,     // return float 1.1
    'float2' => '1.10',   // return float 1.1
    'float3' => '1,20',   // return float 1.2
    'float4' => 1,        // return float 1.0
    'float5' => 0,        // return float 0.0
    'float6' => null,     // error validation => return false
    'float7' => 'null',   // error validation => return false
    'float8' => null,     // error validation => return false
];

$rules = [
    'float1' => ['float'],
    'float2' => ['float'],
    'float3' => ['float'],
    'float4' => ['float'],
    'float5' => ['float'],
    'float6' => ['float'],
    'float7' => ['float'],
    'float8' => ['float'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**email** - String value type (if incorrect field return boolean false)
```php 
$data = [
    'email1' => 'emailadres@gmail.com',  // return string emailadres@gmail.com
    'email2' => 'emailadres@test.test',  // return string emailadres@gmail.com
    'email3' => 'emailadres@gmail',      // error validation => return false
];

$rules = [
    'email1' => ['email'],
    'email2' => ['email'],
    'email3' => ['email'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**phone** - String value type (if incorrect field return boolean false)
```php
$data = [
    'phone1' => '8 ( 999 ) 888 -77- 66 ',       // return string 79998887766 //Default Russian mobile format
    'phone2' => '+7 ( 666 ) 777 -88- 99 ',      // return string 76667778899 //Default Russian mobile format
    'phone3' => 79998887766,                    // return string 79998887766 //Default Russian mobile format
    'phone4' => '+1 97 4896-12-48-489 (354)',   // return string 19748961248489354

    'phone5' => '+7(999)888-77-66',             // return ?string '+7(999)888-77-66'
];

$rules = [
    'phone1' => ['phone'],
    'phone2' => ['phone'],
    'phone3' => ['phone'],
    'phone4' => ['phone'],

    'phone5' => [['phone', function($raw_input){
        // do something with raw data
        return $raw_input;
    }]],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**url** - String value type (if incorrect field return boolean false)
```php 
$data = [
    'url1' => 'http://example.com',     //return string http://example.com
    'url2' => 'https://example.com',    //return string https://example.com
    'url3' => 'ws://example.com',       //return string ws://example.com
    'url4' => 'example.com',            //return boolean false
    'url5' => 'facebook',               //return boolean false
];

$rules = [
    'url1' => ['url'],
    'url2' => ['url'],
    'url3' => ['url'],
    'url4' => ['url'],
    'url5' => ['url'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```
**domain** - String value type (if incorrect field return boolean false)
```php 
$data = [
    'url1' => 'example.com',            //return string example.com
    'url2' => 'facebook',               //return string facebook
    'url3' => 'http://example.com',     //return boolean false
    'url4' => 'https://example.com',    //return boolean false
    'url5' => 'ws://example.com',       //return boolean false
];

$rules = [
    'url1' => ['domain'],
    'url2' => ['domain'],
    'url3' => ['domain'],
    'url4' => ['domain'],
    'url5' => ['domain'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**ip** - String value type (if incorrect field return boolean false)
```php 
$data = [
    'ip1' => '8.8.8.8',                                          //return string '8.8.8.8'
    'ip2' => '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',          //return string '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d'
];

$rules = [
    'ip1' => ['ip'],
    'ip2' => ['ip'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```
**mac** - String value type (if incorrect field return boolean false)
```php 
$data = [
    'mac_address' => '00:00:5e:00:53:af',   //return string '00:00:5e:00:53:af'
];

$rules = [
    'mac_address' => ['mac'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```
**date** - String value type or DateTime object (if incorrect field return raw string)
```php 
$data = [
    'date1' => '2022-01-15 00:00:00', // return string '2022-01-15 00:00:00'
    'date2' => '2022-01-15 00:00:00', // return DateTime object
    'date3' => '2022-01-15',          // error validation => return string '2022-01-15'
    'date4' => '2022-01-15',          // return string '2022-01-15'
    'date5' => '2022-01-15 00:00:00', // error validation => return string '2022-01-15 00:00:00'
    'date6' => '2022-01-15 00:00:00', // error validation => return string '2022-01-15 00:00:00'
];

$rules = [
    'date1' => ['date'],
    'date2' => ['date', 'get_obj'],
    'date3' => ['date'],
    'date4' => [['date', 'Y-m-d']],
    'date5' => [['date', 'Y-m-d'], 'get_obj'],
    'date6' => [['date', 'Y-m-d'], 'get_obj'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**time** - String value type or DateTime object (if incorrect field return raw string)
```php 
$data = [
    'time1' => '00:00:00', // return string '00:00:00' //default format H:i:s or H:i
    'time2' => '00:00',    // return string '00:00:00' //default format H:i:s or H:i
    'time3' => '00:00',    // return DateTime object (this day)
    'time4' => '00:00:0',  // error validation => return string '00:00:0'
];

$rules = [
    'time1' => ['time'],
    'time2' => ['time'],
    'time3' => ['time', 'get_obj'],
    'time4' => ['time'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**enum** - Check exist in array. Return any of type( String || Integer || Float )

```php 
$data = [
    'enum1' => 'green',        // return String 'green'
    'enum2' => 1,              // return Integer 1
    'enum3' => 1.3,            // return float 1.3
    'enum4' => 'yellow',       // validator error => return string "yellow"
    'enum5' => 5,              // validator error => return integer 5
    'enum6' => 2.1,            // validator error => return float 2.1
];

$rules = [
    'enum1' => [['enum', ['red','green','blue']]],
    'enum2' => [['enum', [1, 2, 3]]],
    'enum3' => [['enum', [1.1, 1.2, 1.3]]],
    'enum4' => [['enum', ['red','green','blue']]],
    'enum5' => [['enum', [1, 2, 3]]],
    'enum6' => [['enum', [1.1, 1.2, 1.3]]],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**arr** - Checks if it is an array or JSON, and checks its contents. return mixed PHP array.

```php 
$data = [
    'arr1' => ['item 1', 'item 2', 'item 3'],         // return array
    'arr2' => ['red', 'green', 'blue'],               // return array of strings
    'arr3' => [1, 2, 3],                              // return array of integers
    'arr4' => [1.1, 1.2, 1.3],                        // return array of floats
    'arr5' => [true, true, false, true],              // return array of booleans
    'arr6' => ['color' => ['red','green','blue']],    // return array
    'arr7' => '{"color":["red","green","blue"]}',     // return array
];

$rules = [
    'arr1' => ['arr'],
    'arr2' => [['arr', 'str']],
    'arr3' => [['arr', 'int']],
    'arr4' => [['arr', 'float']],
    'arr5' => [['arr', 'bool']],
    'arr6' => ['arr'],
    'arr7' => ['arr'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```
##Additional validate helpers

**required** - required data (not null)
```php 
$data = [
    'string1' => 'Some Text',
    'string2' => 'Some Text 2',
];

$rules = [
    'string1' => ['require','str'],     //required input (not null)
    'string2' => ['req','str'],         //required input (not null)
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**min** / **max** / **range** - check lenght of chars or array items count 
```php 
$data = [
    'string' => 'Some Text',
    'number' => 150,
    'arr1' => ['red', 'green', 'blue'],
    'arr2' => ['red', 'green', 'blue'],
    'arr3' => ['red', 'green', 'blue'],
];

$rules = [
    'string' => ['str', ['min', 4], ['max', 10]],     //min 4 chars max 10
    'number' => ['int', ['range', 3, 5]],             //min 4 chars max 10
    'arr1' => ['arr', ['min', 3]],                    //array count items min 3
    'arr2' => ['arr', ['max', 3]],                    //array count items max 3
    'arr3' => ['arr', ['range', 1, 3]],               //array count items  min 1 max 3
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**get_obj** - Get DateTime object of field Date or Time
```php 
$data = [
    'date' => '2022-01-15 00:00:00',  // return DateTime object
    'time' => '00:00',                // return DateTime object (this day)
];

$rules = [
    'date' => ['date', 'get_obj'],
    'time' => ['time', 'get_obj'],
];

$fields = validator::CUSTOM($data, $rules);

var_dump($fields->errors);
var_dump($fields->get_fields());
```

**checked** - Checkbox input field validation (WARNING! NOT CORRECT WORK)
```php 
$rules = [
    'checkbox1' => ['checked'],
    'checkbox2' => ['req','checked'],
];

$fields = validator::ALL_POST($rules);

if($fields->POST()){
    var_dump($fields->errors);
    var_dump($fields->get_fields());
}
```