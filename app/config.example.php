<?php

declare(strict_types=1);

/**
 * SimpleServerStat configuration.
 *
 * Copy this file to `config.php` and adjust the values. `config.php` is
 * gitignored so your settings never end up in version control.
 */

return [
    /*
     * Optional shared-secret token. When set to a non-empty string the API
     * (loader.php) requires the token via the `token` query parameter or the
     * `X-Auth-Token` header. Leave empty to keep the dashboard open.
     *
     * This is a basic safeguard only. For real privacy also protect index.php
     * at the web-server level (HTTP basic auth / IP allowlist).
     */
    'access_token' => '',
];
