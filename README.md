# SimpleServerStat

Simple, dependency-light server dashboard that shows live **CPU** and **RAM**
usage (plus basic host info) in the browser. Backend is plain PHP, frontend is
a single HTML page driven by [Chart.js](https://www.chartjs.org/).

## Features

- Live CPU and RAM chart, refreshed every 2 seconds.
- **Non-blocking** metric collection: CPU usage is computed from a cached
  `/proc/stat` snapshot and memory from `/proc/meminfo` — no slow `top`,
  `mpstat` or `iostat` sampling per request.
- Graceful fallback to load-average when `/proc` is unavailable.
- Optional shared-secret token to gate the JSON API.
- No build step and no server-side framework required.

## Requirements

- PHP 7.4+ served by any web server (Apache, Nginx + PHP-FPM, or `php -S`).
- Linux for full CPU/RAM metrics (reads `/proc/stat`, `/proc/meminfo`,
  `/proc/cpuinfo`). On other platforms the page still loads but live metrics
  may be unavailable.

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

## License

No license file is currently included. Add a `LICENSE` of your choice before
distributing.
