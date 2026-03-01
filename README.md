# wp-oidc-login

This is a fairly barebones plugin to support login to WordPress via an
OIDC provider like [CloudFlare Access](https://www.cloudflare.com/zero-trust/products/access/).

It is inspired/based upon the [Auth0 Wordpress Plugin](https://github.com/auth0/wordpress).

## Caveats

There may be other sharp edges with this plugin; it is "good enough"
for our purposes right now, but we are happy to accept contributions
to fix bugs you come accross (as well as some enhancements at our
discretion).

Again, this extension is very barebones. Proceed at your own risk!

## Installation

1. Go to Plugins > Add Plugin in the WordPress admin sidebar
2. Click the "Upload Plugin" button
3. Browse to the `WP_OIDC_Login_x.y.z.zip` file
4. Follow the steps, and then activate the plugin

## Configuration

1. Go to `OIDC Login` in the WordPress admin sidebar
2. Fill in the groups that should be automatically mapped to each WordPress role,
with each one on its own line
3. Set the OIDC options for your provider (this will differ, so follow whatever generic OIDC
service documentation they have)
4. Click "Save Changes"
5. Select "Enable" at the top of the page and click "Save Changes" again

## Overriding SSO

There is an "Override Token" option in the plugin configuration. If set, you can use it to
fall back to the default WordPress login form. This is done by going to
`/wp-login.php?overrideToken=<value_in_config>`. This should be treated as a shared secret.
If this option is not set, it is not possible to override SSO.

## Development

This repository comes with a devcontainer configuration. It will
automatically set up what you need to develop this plugin.
_Note:_ you need to re-setup WordPress every time the dev setup is
launched to avoid shared data issues. Make a note of any config
options so they can be re-applied easily!

Before committing changes, make sure to run `composer style:fix` to
automatically fix style issues.

### Building a version

First, you will almost certainly need to increase the PHP memory limit.
This can be done by editing the `memory_limit` in `php.ini`; `512M` worked
as of writing. The INI file will usually be under `/etc/php` or
`/usr/local/etc/php`; the latter is used in the dev container, and you
will probably need to make a new file called `php.ini` at that path.
Verify this by running `php --ini`.

Second, determine a new version. This plugin should follow [semantic
versioning](https://semantic-versioning.org/). Then do the following
to automatically make a usable zip file.

1. Update `version` in `composer.json` to the new version
2. Change the comment in `wp-oidc-login.php` to use the same new version
3. Run `build.sh` and supply the new version.
