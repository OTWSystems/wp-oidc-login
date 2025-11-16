<?php declare(strict_types=1);

namespace OTWSystems\WpOidcLogin;

use Jumbojett\OpenIDConnectClient;
use OTWSystems\WpOidcLogin\Actions\{Authentication as AuthenticationActions, Configuration as ConfigurationActions};

final class Plugin
{
    /**
     * @var array<class-string<Actions\Base>>
     */
    private const ACTIONS = [AuthenticationActions::class, ConfigurationActions::class];

    /**
     * @var mixed[]
     */
    private array $registry = [];

    public function __construct(
        private ?OpenIDConnectClient $sdk
    ) {}

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
     * @psalm-param 0|null $default
     *
     * @param string $group
     * @param string $key
     * @param ?int   $default
     * @param string $prefix
     */
    public function getOption(string $group, string $key, ?int $default = null, string $prefix = 'wp_oidc_login_'): mixed
    {
        $options = get_option($prefix . $group, []);

        if (is_array($options) && isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    public function getOptionString(string $group, string $key, string $prefix = 'wp_oidc_login_'): ?string
    {
        $result = $this->getOption($group, $key, null, $prefix);

        if (is_string($result)) {
            return $result;
        }

        return null;
    }

    /**
     * Returns a singleton instance of the OIDC client SDK.
     */
    public function getSdk(): OpenIDConnectClient
    {
        if (null === $this->sdk) {
            $endpoint = $this->getOptionString('client', 'endpoint');
            $clientId = $this->getOptionString('client', 'id');
            $clientSecret = $this->getOptionString('client', 'secret');
            $this->sdk = new OpenIDConnectClient($endpoint, $clientId, $clientSecret);
            $this->sdk->addScope(['email', 'profile', 'groups']);
        }

        return $this->sdk;
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
        return null !== $this->getOptionString('client', 'endpoint') &&
            null !== $this->getOptionString('client', 'id') &&
            null !== $this->getOptionString('client', 'secret');
    }

    /**
     * Main plugin functionality.
     */
    public function run(): self
    {
        foreach (self::ACTIONS as $action) {
            $callback = [$this->getClassInstance($action), 'register'];

            /** @var callable $callback */
            $callback();
        }

        return $this;
    }
}
