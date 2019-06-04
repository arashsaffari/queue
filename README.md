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
3. Redis `//TODO: will be added`

**I look forward to every help, further development and recommendation**

# Installing

## install this package

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
---

## Configuration
Run the following command from the command prompt, and it will copy queue migration (`20190526184519_queue_tasks.php`) and config (`Queue.php`) into your application
**NOTE:If you do not want to use a codeigniter persistor, simply confirm the Publish Config file and type in Publish queue migration: `n`**
## 1. install this package
php spark migrate:latest
---

## Configuration


## Use it
