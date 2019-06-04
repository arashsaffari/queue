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

```shell
php spark queue:publish
```

>**NOTE: If you do not want to use a codeigniter persistor, simply confirm the Publish Config file and type in Publish queue migration: `n`**

finally go to config file and edit that

---

## Use it

### 1. Codeigniter Persistor

in queue config file you must enter codeigniter as connection:
```php 
$queueConnection = 'codeigniter';
```

Now use the following commands from the command prompt to migrate queue table

>**NOTE: You must have previously entered the command `php spark queue: publish` and type in Publish queue migration: `y`**

```shell
php spark migrate:latest
```
---

### 2. PDO Persistor

in queue config file you must enter codeigniter as connection:
```php 
$queueConnection = 'pdo';
```

Create a table with the following SQL code:

```SQL
 CREATE TABLE IF NOT EXISTS `queue_tasks` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `method_name` VARCHAR(255) NULL,
    `data` TEXT NULL,
    `priority` TINYINT NOT NULL,
    `unique_id` VARCHAR(32) NULL,
    `created_at` DATETIME NOT NULL,
    `is_taken` TINYINT(1) NOT NULL DEFAULT 0,
    `error` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`))
  ENGINE = InnoDB;
```
---

## Add a task in queue

```php
...
use CodeigniterExt\Queue\Queue;
use CodeigniterExt\Queue\Task;
...

$queue = new Queue();

$task = new Task;

$task
    ->setName('App/Controllers/SendMail')
    ->setData(
        array(
            'to'        => 'example@domain.com',
            'from'      => 'your@email.com',
            'subject'   => 'Hi!',
            'text'      => 'It is your faithful Queue!'
        )
    )
    ->setPriority(Task::PRIORITY_NORMAL);

// Queue it
$queue->addTask($task);
```

---

## Run the worker

```shell
php spark queue:work
```

>To keep the queue:work process running permanently in the background, you should use a process monitor such as [Supervisor](http://supervisord.org) to ensure that the queue worker does not stop running.