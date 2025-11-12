<?php declare(strict_types=1);

namespace OTWSystems\WpOidcLogin;

use OTWSystems\WpOidcLogin\Actions\{Configuration as ConfigurationActions};

final class Plugin
{
    /**
     * @var array<class-string<Actions>>
     */
    private const ACTIONS = [ConfigurationActions::class];

    /**
     * @var array<class-string<Filters>>
     */
    private const FILTERS = [];

    /**
     * @var mixed[]
     */
    private array $registry = [];

    public function __construct() {}

    /**
     * Returns a singleton instance of Hooks configured for working with actions.
     */
    public function actions(): Hooks
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new Hooks(Hooks::CONST_ACTION_HOOK);
        }

        return $instance;
    }

    public function getClassInstance(string $class): mixed
    {
        if (!array_key_exists($class, $this->registry)) {
            $this->registry[$class] = new $class($this);
        }

        return $this->registry[$class];
    }

    /**
     * Returns true if the plugin has been enabled.
     */
    public function isEnabled(): bool
    {
        return 'true' === $this->getOptionString('state', 'enable');
    }

    /**
     * Returns true if the plugin has a minimum viable configuration.
     */
    public function isReady(): bool
    {
        return true;
    }

    /**
     * Main plugin functionality.
     */
    public function run(): self
    {
        foreach (self::FILTERS as $filter) {
            $callback = [$this->getClassInstance($filter), 'register'];

            /** @var callable $callback */
            $callback();
        }

        foreach (self::ACTIONS as $action) {
            $callback = [$this->getClassInstance($action), 'register'];

            /** @var callable $callback */
            $callback();
        }

        return $this;
    }
}
