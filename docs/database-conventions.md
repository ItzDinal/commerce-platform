\# Commerce Platform — Database Conventions



\## Purpose



These conventions define the database standards for the reusable Commerce Platform.



They apply to Commerce Platform domain tables and models.



Framework/infrastructure tables provided by Laravel may follow Laravel's own conventions.



\---



\## 1. Table Naming



Tables use plural snake\_case names.



Examples:



\- users

\- products

\- product\_variants

\- product\_attributes

\- product\_attribute\_values

\- cart\_items

\- inventory\_movements



Avoid:



\- Product

\- ProductCategory

\- productCategories

\- orderItems



\---



\## 2. Primary Keys



Commerce Platform domain entities use ULIDs as primary keys.



Example:



```php

$table->ulid('id')->primary();
