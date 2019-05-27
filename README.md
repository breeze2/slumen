# Slumen

[![Total Downloads](https://img.shields.io/packagist/dt/breeze2/slumen.svg)](https://packagist.org/packages/breeze2/slumen)
[![Latest Stable Version](https://img.shields.io/packagist/v/breeze2/slumen.svg)](https://packagist.org/packages/breeze2/slumen)

> Speed up Lumen with Swoole

## Require
* PHP >= 7.0.0
* Lumen >= 5.5.0
* Swoole >= 1.9.2

## Install

```cmd
$ cd /PATH/TO/LUMEN/PROJECT
$ composer require breeze2/slumen
$ cp vendor/breeze2/slumen/bootstrap/slumen.php ./bootstrap/
```

## Usage

```
$ vendor/bin/slumen start
$ vendor/bin/slumen stop 
$ vendor/bin/slumen restart
$ vendor/bin/slumen status
```

