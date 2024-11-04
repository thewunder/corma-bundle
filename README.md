# Corma Symfony Bundle

This package integrates the [Corma ORM](https://github.com/thewunder/corma) 
into Symfony, and makes it available as a service.

Install using:

```shell
composer require thewunder/corma-bundle
```

Configure your database connection in config/packages/corma.yaml

```yaml
corma:
    database:
        driver: pdo_mysql
        host: database
        port: 3306
        database: symfony
        user: symfony
        password: symfony
```

And then it will be available for auto-wiring in your classes, assuming you have symfony flex enabled.

```php
use Corma\ObjectMapper;

class MyClass
{
    public function __construct(private readonly ObjectMapper $orm)
    {
    }
    
// ...

```
