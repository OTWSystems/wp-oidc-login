<?php declare(strict_types=1);

namespace OTWSystems\WpOidcLogin\Actions;

use OTWSystems\WpOidcLogin\Utilities\{Render, Sanitize};

use function str_contains;
use function str_replace;

final class Configuration extends Base
{
    /**
     * @var string
     */
    public const CONST_SECTION_PREFIX = 'wp_oidc_login';

    /**
     * @var string
     */
    public const CONST_PAGE_GENERAL = 'wp_oidc_login_configuration';

    /**
     * @var array<string, array<int, int|string>|string>
     */
    protected array $registry = [
        'admin_init' => 'onSetup',
        'admin_menu' => 'onMenu',
        'wp_oidc_login_ui_configuration' => 'renderConfiguration'
    ];

    public function onSetup(): void
    {
        /** @var array<string, array<string, array<string, array<string, array<string, array<int, string>|array<string, string>|string>>|array<string, array<string, array<int, string>|array<string, string>|string>|array<string, string|string[]>>|array<string, array<string, array<string, string>|string>>|array<string, array<string, array<string, string>|string|string[]>>|array<string, array<string, string>>|string>>|array<string, array<string, array<string, array<string, array<string, string>|string>>|array<string, array<string, array<string, string>|string|string[]>>|array<string, array<string, string>>|string>>|array<string, array<string, array<string, array<string, array<string, string>|string|string[]>|array<string, string>>|string>>|string> */
        $sections = [
            'state' => [
                'title' => '',
                'description' => '',
                'options' => [
                    'enable' => [
                        'title' => 'Enable Authentication',
                        'type' => 'boolean',
                        'enabled' => 'isPluginReady',
                        'description' => ['getOptionDescription', 'enable'],
                        'select' => [
                            'false' => 'Disabled',
                            'true' => 'Enabled',
                        ]
                    ]
                ]
            ],
            'accounts' => [
                'title' => 'WordPress Users Management',
                'description' => '',
                'options' => []
            ]
        ];

        /** @var array<string> */
        foreach ($this->getRoleOptions() as $roleId => $roleName) {
            $sections['accounts']['options']["mapping_$roleId"] = [
                'title' => "$roleName Mapping",
                'type' => 'textarea',
                'enabled' => 'isPluginReady',
                'sanitizer' => 'string',
                'description' => ['getOptionDescription', "{$roleId} mapping"]
            ];
        }

        /** @var array<mixed> $section */
        foreach ($sections as $sectionId => $section) {
            $sectionId = self::CONST_SECTION_PREFIX . '_' . $sectionId;
            $sectionType = (isset($section['type']) && is_string($section['type'])) ? $section['type'] : 'array';
            $sectionCallback = [
                $this,
                'onUpdate' . str_replace(' ', '', ucwords(str_replace(['wp_oidc_login_', '_'], ' ', $sectionId))),
            ];

            /** @var callable $sectionCallback */
            register_setting(
                option_group: self::CONST_PAGE_GENERAL,
                option_name: $sectionId,
                args: [
                    'type' => $sectionType,
                    'sanitize_callback' => $sectionCallback,
                    'show_in_rest' => false,
                ],
            );

            add_settings_section(
                id: $sectionId,
                title: $section['title'],
                callback: static function () use ($section): void {
                    echo $section['description'] ?? '';
                },
                page: self::CONST_PAGE_GENERAL,
            );

            /** @var array<string, mixed> $optionValues */
            $optionValues = get_option($sectionId, []);
            $options = (isset($section['options']) && is_array($section['options'])) ? $section['options'] : [];

            /** @var array<string, array{title: string, type: string, description?: array<string>|string, placeholder?: array<string>|string, select?: array<mixed>|string, disabled?: bool|string, enabled?: bool|string}> $options */
            foreach ($options as $optionId => $option) {
                $elementId = uniqid();
                $optionType = $option['type'];
                $optionValue = $optionValues[$optionId] ?? null;
                $optionName = $sectionId . '[' . $optionId . ']';
                $optionDescription = $option['description'] ?? '';
                $optionPlaceholder = $option['placeholder'] ?? '';
                $optionSelections = $option['select'] ?? null;
                $optionDisabled = $option['disabled'] ?? null;
                $optionEnabled = $option['enabled'] ?? null;

                if (is_array($optionDescription)) {
                    $callback = [$this, $optionDescription[0]];

                    /** @var callable $callback */
                    $optionDescription = $callback(...array_slice($optionDescription, 1));
                }

                if (is_array($optionPlaceholder)) {
                    $callback = [$this, $optionPlaceholder[0]];

                    /** @var callable $callback */
                    $optionPlaceholder = $callback(...array_slice($optionPlaceholder, 1));
                }

                if (is_string($optionDisabled)) {
                    $callback = [$this, $optionDisabled];
                    /** @var callable $callback */
                    $optionDisabled = (true === $callback());
                }

                if (is_string($optionEnabled)) {
                    $callback = [$this, $optionEnabled];
                    /** @var callable $callback */
                    $optionDisabled = (false === $callback());
                }

                if (is_string($optionSelections)) {
                    $callback = [$this, $optionSelections];
                    /** @var callable $callback */
                    $optionSelections = $callback() ?? [];
                }

                if (null !== $optionValue && str_contains($optionId, 'mapping')) {
                    $optionValue = implode("\n", explode(' ', $optionValue));
                }

                /**
                 * @var string                      $optionDescription
                 * @var string                      $optionPlaceholder
                 * @var null|array<bool|int|string> $optionSelections
                 * @var bool|int|string             $optionValue
                 */
                add_settings_field(
                    id: $elementId,
                    title: $option['title'],
                    callback: static function () use (
                        $elementId,
                        $optionName,
                        $optionType,
                        $optionDescription,
                        $optionPlaceholder,
                        $optionValue,
                        $optionSelections,
                        $optionDisabled
                    ): void {
                        Render::option(
                            element: $elementId,
                            name: $optionName,
                            type: $optionType,
                            description: $optionDescription,
                            placeholder: $optionPlaceholder,
                            value: $optionValue,
                            select: $optionSelections,
                            disabled: $optionDisabled,
                        );
                    },
                    page: self::CONST_PAGE_GENERAL,
                    section: $sectionId,
                    args: [
                        'label_for' => $elementId,
                        'description' => $option['description'] ?? '',
                    ],
                );
            }
        }
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateAccounts(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [];

        foreach ($this->getRoleOptions() as $roleId => $roleName) {
            $mappings = Sanitize::textarea((string) ($input["mapping_$roleId"] ?? '')) ?? '';
            $mappings = explode("\n", $mappings);
            $sanitized["mapping_$roleId"] = trim(implode(' ', Sanitize::arrayUnique($mappings)));
        }

        return array_filter($sanitized, static fn($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateState(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'enable' => Sanitize::boolean((string) ($input['enable'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn($value): bool => '' !== $value);
    }

    public function onMenu(): void
    {
        add_menu_page(
            page_title: 'OIDC Login - Options',
            menu_title: 'OIDC Login',
            capability: 'manage_options',
            menu_slug: 'oidc-login',
            callback: static function (): void {
                do_action('wp_oidc_login_ui_configuration');
            },
            icon_url: 'dashicons-admin-network',
            position: $this->getPriority('MENU_POSITION', 70, 'WP_OIDC_LOGIN_ADMIN'),
        );
    }

    public function renderConfiguration(): void
    {
        Render::pageBegin('WP OIDC Login - Options');

        settings_fields(self::CONST_PAGE_GENERAL);
        do_settings_sections(self::CONST_PAGE_GENERAL);
        submit_button();

        Render::pageEnd();
    }

    private function getOptionDescription(string $context): string
    {
        if (!$this->isPluginReady()) {
            return 'Plugin requires configuration.';
        }

        if ('enable' === $context) {
            return 'Manage WordPress authentication with OIDC.';
        }

        if (str_contains($context, 'mapping')) {
            /** @var string */
            $roleId = str_replace($context, ' mapping', '');
            return "A list of groups to map to the $roleId role, each on its own line.";
        }
    }

    /**
     * Returns an array of role tags (as strings) identifying all available role options.
     *
     * @return mixed[]
     */
    private function getRoleOptions(): array
    {
        $roles = get_editable_roles();
        $response = [];

        foreach ($roles as $roleId => $role) {
            $response[$roleId] = (string) $role['name'];
        }

        /** @var string[] $response */
        return $response;
    }
}
