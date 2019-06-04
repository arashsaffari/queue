# Queue Jobs for Codeigniter 4

Queue Jobs module for CodeIgniter 4 with CLI
Persistors:
1. Codeigniter
    * MySQLi
    * PDO
    * PostgreSQL
    * Oracle
    * ODBC
    * ....
2. PDO (Does not use a codeigniter modeling system and is self-contained)
3. Redis //TODO: will be added

**I look forward to every help, further development and recommendation**

# Installing

## 1. install this package

```shell
$ composer require codeigniterextqueue
```

or 

```shell
$ composer require codeigniterext/queue:dev-master
```

Now you can use the following commands from the command prompt

```shell
$ php spark queue:delete
$ php spark queue:deleteall
$ php spark queue:forget
$ php spark queue:forgetall
$ php spark queue:publish
$ php spark queue:resetall
$ php spark queue:retry
$ php spark queue:run
$ php spark queue:work
```

## 2. install this package
php spark migrate:latest
---

## Configuration


## Use it
