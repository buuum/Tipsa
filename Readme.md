Simple php api class with TIPSA
================================

[![Packagist](https://img.shields.io/packagist/v/buuum/tipsa.svg)](https://packagist.org/packages/buuum/tipsa)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg?maxAge=2592000)](#license)

## Install

### System Requirements

You need PHP >= 5.5.0 to use Buuum\Tipsa but the latest stable version of PHP is recommended.

### Composer

Buuum\Tipsa is available on Packagist and can be installed using Composer:

```
composer require buuum/tipsa
```

### Manually

You may use your own autoloader as long as it follows PSR-0 or PSR-4 standards. Just put src directory contents in your vendor directory.


### Documentation

[WebServices PDF](WebServices.pdf)

### Constructor
```php
$tipsa = new Tipsa($agent, $client, $password);
```
### Methods

#### getByReference
```php
try{
    $info = $tipsa->getByReference($referencia);
}catch (Exception $e){
    echo $e->getMessage();
}
```
$info output
```php
array (
  0 => 
  array (
    'date' => '02/24/2017 19:58:29',
    'code_type' => '1',
    'code' => 'Tránsito',
  ),
  1 => 
  array (
    'date' => '03/01/2017 07:29:59',
    'code_type' => '2',
    'code' => 'Reparto',
  ),
  2 => 
  array (
    'date' => '03/01/2017 14:41:15',
    'code_type' => '4',
    'code' => 'Incidencia',
  ),
  3 => 
  array (
    'date' => '03/02/2017 08:26:52',
    'code_type' => '2',
    'code' => 'Reparto',
  ),
  4 => 
  array (
    'date' => '03/02/2017 13:11:18',
    'code_type' => '3',
    'code' => 'Entregado',
  ),
)
```

#### getEnviosByDate
```php
$date = '2017-02-08';
try{
    $info = $tipsa->getEnviosByDate($date);
}catch (Exception $e){
    echo $e->getMessage();
}
```

#### getEstadoEnviosByDate
```php
$date = '2017-02-08';
try{
    $info = $tipsa->getEstadoEnviosByDate($date);
}catch (Exception $e){
    echo $e->getMessage();
}
```

#### getIncidenciasByDate
```php
$date = '2017-02-08';
try{
    $info = $tipsa->getIncidenciasByDate($date);
}catch (Exception $e){
    echo $e->getMessage();
}
```

## LICENSE

The MIT License (MIT)

Copyright (c) 2016

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.