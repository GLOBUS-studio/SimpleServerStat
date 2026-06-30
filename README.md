# SimpleServerStat

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4.svg)
[![CI](https://github.com/GLOBUS-studio/SimpleServerStat/actions/workflows/ci.yml/badge.svg)](https://github.com/GLOBUS-studio/SimpleServerStat/actions/workflows/ci.yml)

Simple, dependency-light server dashboard that shows live **CPU** and **RAM**
usage (plus basic host info) in the browser. Backend is plain PHP, frontend is
a single HTML page driven by [Chart.js](https://www.chartjs.org/).

## Features

- Live CPU and RAM chart, refreshed every 2 seconds.
- **Non-blocking** metric collection: no slow `top`, `mpstat` or `iostat`
  sampling. Linux uses `/proc`; Windows uses WMI (`wmic`, with PowerShell CIM
  fallback).
- Cross-platform: accurate data on Linux and Windows; graceful fallback to
  load average when detailed sources are unavailable.
- Optional shared-secret token to gate the JSON API.
- No build step and no server-side framework required.

## Requirements

- PHP 7.4+ served by any web server (Apache, Nginx + PHP-FPM, or `php -S`).
- Linux for the most precise metrics (reads `/proc`). On Windows the page and
  chart work fully via WMI; on other platforms the page loads but live charts
  may be empty.

## Project layout

```
app/
  index.php            Dashboard page (HTML/CSS/JS)
  loader.php           JSON API (?action=system_info | general)
  config.example.php   Configuration template
  back.webp, logo.png  Static assets
  robots.txt
```

## Running

Serve the `app/` directory with PHP, for example:

```bash
php -S 0.0.0.0:8080 -t app
```

Then open <http://localhost:8080/>.

## Configuration

Copy the template and edit it (the real file is gitignored):

```bash
cp app/config.example.php app/config.php
```

Set `access_token` to a non-empty value to require a token on the API. When
set, requests must include it via `?token=...` or the `X-Auth-Token` header;
the dashboard injects it automatically.

> The token is a basic safeguard only. Because `index.php` exposes the token to
> the browser, protect the page itself at the web-server level (HTTP basic auth
> or an IP allowlist) if the data must stay private.

## API

| Endpoint                       | Description                          |
| ------------------------------ | ------------------------------------ |
| `loader.php?action=system_info` | `{ load, memory_usage }` (live)      |
| `loader.php?action=general`     | `{ cpu_count, php_ver, os_data }`    |

## Limitations / Caveats

- On Linux the first poll after server start returns a **load-average**
  approximation; subsequent polls (every 2s) show the true `/proc/stat` delta.
- On Windows `wmic` is preferred; if it is unavailable (Windows 11 24H2 has
  it as an optional feature) the PowerShell CIM fallback is transparently used.
- The built-in PHP server is single-threaded. For production with multiple
  concurrent viewers, serve behind Nginx or Apache.
- The shared-secret token is a basic safeguard only — because `index.php`
  exposes it client-side, protect the whole `app/` directory at the web-server
  level for real privacy.

## Development

```bash
# Start the built-in server locally
php -S 127.0.0.1:8080 -t app

# Lint all PHP files
find app -name '*.php' -print0 | xargs -0 -n1 php -l 2>&1 | grep -v '^No syntax'
```

All PHP files are linted on every push and pull request via GitHub Actions
(see `.github/workflows/ci.yml`).

## Credits

- [Chart.js](https://www.chartjs.org/) — charting library (MIT)
- [Bootstrap](https://getbootstrap.com/) — CSS framework (MIT)

## License

Released under the [MIT License](LICENSE).
