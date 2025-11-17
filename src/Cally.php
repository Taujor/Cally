<?php 

declare(strict_types=1);
namespace Taujor\Cally;

use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Thrown when attempting to modify the container after it has been frozen.
 */
class FrozenRegistryException extends LogicException implements ContainerExceptionInterface {}

/**
 * Thrown when attempting to retrieve a key that does not exist in the container.
 */
class KeyNotFoundException extends LogicException implements NotFoundExceptionInterface {}

/**
 * Thrown when attempting to register a key that already exists.
 */
class KeyAlreadyExistsException extends LogicException implements ContainerExceptionInterface {}

/**
 * A minimal dependency injection container implementing PSR-11.
 * Provides simple storage for factories, lazy services, singletons, and values.
 */
class Cally implements ContainerInterface {

    /**
     * Internal storage for registered callables.
     *
     * @var array<string, callable>
     */
    private array $registry = [];

    /**
     * Whether the container can (false) or cannot (true) accept new registrations.
     *
     * @var bool
     */
    private bool $frozen = false;

    /**
     * Registers a callable service or factory under the given key.
     *
     * @param string   $key   Identifier of the entry.
     * @param callable $value Callable that returns the value to be stored.
     *
     * @return void
     *
     * @throws FrozenRegistryException   If the container is frozen.
     * @throws KeyAlreadyExistsException If the key already exists.
     */
    public function set(string $key, callable $value): void {
        if($this->frozen) throw new FrozenRegistryException("Cally: Container registry is frozen and cannot be modified.");
        if($this->has($key)) throw new KeyAlreadyExistsException("Cally: Unable to set key '$key' because it already exists.");
        $this->registry[$key] = $value;
    }
    
    /**
     * Registers a lazily-instantiated service under the given key.
     * The service is created only once upon first retrieval.
     *
     * @param string   $key     Identifier of the entry.
     * @param callable $factory Factory callable that creates the service.
     *
     * @return void
     */
    public function lazy(string $key, callable $factory): void {
        $this->set($key, function () use ($factory) {
            static $instance = null;
            return $instance ?? ($instance = $factory());
        });
    }

    /**
     * Registers an already-instantiated singleton service.
     *
     * @param string $key       Identifier of the entry.
     * @param object $instance  The singleton object to store.
     *
     * @return void
     */
    public function singleton(string $key, object $instance): void {
        $this->set($key, fn() => $instance);
    }
    
    /**
     * Registers a factory callable that produces a new value on each retrieval.
     *
     * @param string   $key     Identifier of the entry.
     * @param callable $factory The factory callable.
     *
     * @return void
     */
    public function factory(string $key, callable $factory): void {
        $this->set($key, $factory);
    }
    
    /**
     * Registers a simple immutable value.
     *
     * @param string $key   Identifier of the entry.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function value(string $key, mixed $value): void {
        $this->set($key, fn() => $value);
    }

    /**
     * Retrieves the value or service associated with a key.
     *
     * @param string $key Identifier of the entry to retrieve.
     *
     * @return mixed The result of invoking the stored callable.
     *
     * @throws KeyNotFoundException If the key does not exist.
     */
    public function get(string $key): mixed {
        if(!$this->has($key)) throw new KeyNotFoundException("Cally: Unable to get key '$key' because it does not exist.");
        return $this->registry[$key]();
    }

    /**
     * Checks whether a key exists in the container.
     *
     * @param string $key Identifier to check.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool {
        return array_key_exists($key, $this->registry);
    }
    
    /**
     * Freezes the container registry, preventing further modifications.
     *
     * @return void
     */
    public function freeze(): void {
        $this->frozen = true;
    }
    
    /**
     * Returns whether the container is frozen.
     *
     * @return bool True if frozen, false otherwise.
     */
    public function isFrozen(): bool {
        return $this->frozen;
    }
}
