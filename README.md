# **Cally — A Lightweight PSR-11 Dependency Injection Container**

Cally is a minimal, elegant, and strict PSR-11–compatible dependency injection container for PHP 8.  
It provides a simple API for defining services, factories, singletons, lazy-loaded objects, and immutable values.

Cally is designed to be:

- **Small** – no dependencies  
- **Predictable** – explicit, no magic  
- **Strict** – throws meaningful exceptions  
- **PSR-11 compliant** – works with existing standards  
- **Fast** – closures only, no reflection  

---

## **Features**

- **PSR-11 `ContainerInterface`** compatible  
- Register **lazy-loaded services**  
- Register **singletons**  
- Register **factories**  
- Register simple **values**  
- **Freeze** the container to prevent modification  
- Meaningful exception hierarchy:
  - `FrozenRegistryException`
  - `KeyAlreadyExistsException`
  - `KeyNotFoundException`

---

## **Installation**

Install via Composer:

```bash
composer require taujor/cally
```

---

## **Quick Start**

### **Create the container**

```php
use Taujor\Cally\Cally;

$container = new Cally();
```

---

## **Registering Services**

### **Singleton**

A single shared instance:

```php
$pdo = new PDO('sqlite::memory:');

$container->singleton('db', $pdo);
```

Usage:

```php
$db = $container->get('db'); // always returns the same instance
```

---

### **Lazy Service**

Instantiated only once on first use:

```php
$container->lazy('config', function () {
    return parse_ini_file('app.ini');
});
```

---

### **Factory**

Produces a new instance every time:

```php
$container->factory('uuid', fn() => bin2hex(random_bytes(16)));

$id1 = $container->get('uuid');
$id2 = $container->get('uuid');

// always different
```

---

### **Value**

Stores a simple immutable value:

```php
$container->value('version', '1.0.0');

echo $container->get('version'); // "1.0.0"
```

---

## **Retrieving Services**

```php
$service = $container->get('key');
```

If the key does not exist:  
`KeyNotFoundException`

---

## **Checking Keys**

```php
if ($container->has('cache')) {
    // ...
}
```

---

## **Freezing the Container**

After freezing the container, no new services can be registered.

```php
$container->freeze();

$container->set('foo', fn() => 'bar'); 
// Throws FrozenRegistryException
```

---

## **Error Handling**

Cally throws clear, meaningful exceptions:

| Exception | Trigger |
|----------|---------|
| `FrozenRegistryException` | Attempt to modify a frozen container |
| `KeyAlreadyExistsException` | Attempt to overwrite a key |
| `KeyNotFoundException` | Attempt to get a missing key |

All exceptions implement PSR-11 interfaces where appropriate.

---

## **Why Cally?**

- No unnecessary abstraction layers  
- Perfect for personal and commercial web applications
- A clean alternative to overly complex DI containers  
- Explicit over magic  
- Predictable and testable  

---

## **License**

MIT license
