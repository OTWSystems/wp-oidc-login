<?php declare(strict_types=1);

namespace OTWSystems\WpOidcLogin\Actions;

final class Authentication extends Base
{
    /**
     * @var array<string, array<int, int|string>|string>
     */
    protected array $registry = [
        'login_form_login' => 'onLogin',
        'login_form_logout' => 'onLogout'
    ];

    public function onLogin(): void
    {
        if (!$this->getPlugin()->isEnabled()) {
            return;
        }

        if (!$this->getPlugin()->isReady()) {
            return;
        }

        // Don't allow caching of this route
        nocache_headers();

        if (!$this->getSdk()->authenticate()) {
            wp_safe_redirect('/');
            exit;
        }

        /**
         * @var object{
         *  name: ?string,
         *  email: ?string,
         *  groups: ?array<string>
         * }
         */
        $userDetails = $this->getSdk()->requestUserInfo();
        $userName = $userDetails->name;
        $email = $userDetails->email;
        $userGroups = $userDetails->groups;
        if (null === $userName || null === $email || null === $userGroups) {
            wp_safe_redirect('/');
            exit;
        }

        $wpUser = $this->resolveIdentity($email, $userName, $userGroups);
        if ($wpUser instanceof \WP_User) {
            wp_set_current_user($wpUser->ID);
            wp_set_auth_cookie($wpUser->ID, true);
            do_action('wp_login', $wpUser->user_login, $wpUser);
            wp_safe_redirect('/');
            exit;
        }

        wp_safe_redirect('/');
        exit;
    }

    public function onLogout(): never
    {
        wp_logout();
        wp_safe_redirect('/');
        exit;
    }

    private function resolveIdentity(string $email, string $username, array $userGroups): ?\WP_User
    {
        $email = sanitize_email(filter_var($email, FILTER_SANITIZE_EMAIL));
        $user = get_user_by('email', $email);

        if (is_bool($user)) {
            // User not found, create one
            $user = wp_create_user($username, wp_generate_password(random_int(12, 123), true, true), $email);
            if (!$user instanceof \WP_Error) {
                $user = get_user_by('ID', $user);
            }
        }

        if ($user instanceof \WP_User) {
            $currentRoles = (array) $user->roles;
            $desiredRoles = $this->getRoles($userGroups);
            foreach ($currentRoles as $currentRole) {
                if (!in_array($currentRole, $desiredRoles)) {
                    $user->remove_role($currentRole);
                }
            }
            foreach ($desiredRoles as $desiredRole) {
                $user->add_role($desiredRole);
            }
            wp_update_user($user);
            return $user;
        }

        return null;
    }

    private function getRoles(array $userGroups): array
    {
        /** @var array<string, string> */
        $roleMappings = get_option('wp_oidc_login_accounts', []);
        $roleToGroups = [];

        foreach ($roleMappings as $mappingKey => $groups) {
            $roleId = str_replace('mapping_', '', $mappingKey);
            $roleToGroups[$roleId] = explode("\n", $groups);
        }

        /** @var array<string> */
        $matchingRoles = [];
        foreach ($roleToGroups as $roleId => $groups) {
            if (count(array_intersect($groups, $userGroups)) > 0) {
                $matchingRoles[] = $roleId;
            }
        }
        return $matchingRoles;
    }
}
