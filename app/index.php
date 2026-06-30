<?php
$token = '';
$cfgFile = __DIR__ . '/config.php';
if (is_file($cfgFile)) {
    $cfg = require $cfgFile;
    if (is_array($cfg) && isset($cfg['access_token'])) {
        $token = (string) $cfg['access_token'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Live CPU and memory monitoring for GLOBUS.studio infrastructure.">
    <meta name="theme-color" content="#0b1220">
    <title>GLOBUS.studio — Server Status</title>
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA=="
        crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cpu: rgba(255, 99, 132, 1);
            --ram: rgba(54, 162, 235, 1);
        }
        html, body {
            height: 100%;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
        }
        body {
            background: #0b1220 url('back.webp') center / cover fixed no-repeat;
            color: #fff;
        }
        h1, h2, .fw-brand {
            font-weight: 700;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 700;
            color: #fff;
        }
        .panel {
            background: rgba(0, 0, 0, .5);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: .85rem;
            padding: 1.5rem 1.75rem;
        }
        .section-title {
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .78rem;
            font-weight: 600;
            opacity: .65;
            margin-bottom: .85rem;
        }
        .lead-text {
            opacity: .85;
        }
        .chart-wrap {
            position: relative;
            height: 320px;
        }
        .live-readout {
            display: flex;
            gap: .75rem;
            margin-bottom: 1.1rem;
            flex-wrap: wrap;
        }
        .chip {
            background: rgba(255, 255, 255, .08);
            border-radius: 999px;
            padding: .4rem 1rem;
            font-weight: 700;
            font-size: .92rem;
            white-space: nowrap;
        }
        .chip .dot {
            display: inline-block;
            width: .62rem;
            height: .62rem;
            border-radius: 50%;
            margin-right: .5rem;
            vertical-align: middle;
        }
        .dot-cpu { background: var(--cpu); }
        .dot-ram { background: var(--ram); }
        .metrics {
            list-style: none;
            padding: 0;
            margin: 1.25rem 0 0;
        }
        .metrics li {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1rem;
            padding: .6rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }
        .metrics li:last-child {
            border-bottom: 0;
        }
        .metrics .label {
            opacity: .65;
        }
        .metrics .value {
            font-weight: 700;
            text-align: right;
        }
        footer {
            background: rgba(0, 0, 0, .55);
            padding: .85rem 0;
        }
        footer a {
            color: #fff;
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .chart-wrap { height: 260px; }
            .live-readout { justify-content: center; }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg bg-transparent py-3">
    <div class="container">
        <a class="navbar-brand" href="https://globus.studio">
            <img src="logo.png" alt="GLOBUS.studio" height="48" width="48">
            <span>GLOBUS.studio</span>
        </a>
        <a class="btn btn-outline-light btn-sm ms-auto" href="https://globus.studio" rel="noopener">globus.studio</a>
    </div>
</nav>

<main class="container flex-grow-1 py-4">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-7">
            <div class="panel h-100">
                <div class="section-title">CPU &amp; memory load</div>
                <div class="live-readout">
                    <span class="chip"><span class="dot dot-cpu"></span>CPU&nbsp;<span id="cpuNow">—</span></span>
                    <span class="chip"><span class="dot dot-ram"></span>RAM&nbsp;<span id="ramNow">—</span></span>
                </div>
                <div class="chart-wrap">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel h-100">
                <div class="section-title">System</div>
                <h1 class="h3 mb-3">Server status</h1>
                <p class="lead-text mb-0">Live CPU and memory monitoring for GLOBUS.studio infrastructure. Readings refresh automatically every few seconds.</p>
                <ul class="metrics">
                    <li><span class="label">Operating system</span><span class="value" id="osData">—</span></li>
                    <li><span class="label">CPU cores</span><span class="value" id="cpuCount">—</span></li>
                    <li><span class="label">PHP runtime</span><span class="value" id="phpVer">—</span></li>
                    <li><span class="label">Service</span><span class="value">SimpleServerStat</span></li>
                </ul>
            </div>
        </div>
    </div>
</main>

<footer class="mt-auto text-center">
    <div class="container">
        <p class="mb-0">© 2020–2026 <span class="fw-brand">GLOBUS.studio</span> · We build digital products that last · <a href="https://globus.studio" rel="noopener">globus.studio</a></p>
    </div>
</footer>

<script>
    var ACCESS_TOKEN = <?php echo json_encode($token); ?>;
</script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js"
    integrity="sha512-HvOjJrdwNpDbkGJIG2ZNqDlVqMo77qbs4Me4cah0HoDrfhrbA+8SBlZn1KrvAQw7cILLPFJvdwIgphzQmMm+Pw=="
    crossorigin="anonymous">
</script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"
    integrity="sha512-Y51n9mtKTVBh3Jbx5pZSJNDDMyY+yGe77DGtBPzRlgsf/YLCh13kSZ3JmfHGzYFCmOndraf0sQgfM654b7dJ3w=="
    crossorigin="anonymous">
</script>
<script>
(function () {
    'use strict';

    var MAX_POINTS = 35;
    var tokenQuery = ACCESS_TOKEN ? '&token=' + encodeURIComponent(ACCESS_TOKEN) : '';

    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'CPU',
                    data: [],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.12)',
                    fill: true,
                    tension: 0.25,
                    pointRadius: 0,
                    borderWidth: 2
                },
                {
                    label: 'RAM',
                    data: [],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.12)',
                    fill: true,
                    tension: 0.25,
                    pointRadius: 0,
                    borderWidth: 2
                }
            ]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { labels: { color: '#fff', usePointStyle: true } }
            },
            scales: {
                x: {
                    ticks: { color: 'rgba(255, 255, 255, 0.7)', maxRotation: 0, autoSkipPadding: 16 },
                    grid: { color: 'rgba(255, 255, 255, 0.12)' }
                },
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { color: 'rgba(255, 255, 255, 0.7)', callback: function (v) { return v + '%'; } },
                    grid: { color: 'rgba(255, 255, 255, 0.12)' }
                }
            }
        }
    });

    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) { el.textContent = text; }
    }

    function pushPoint(label, cpu, ram) {
        chart.data.labels.push(label);
        chart.data.datasets[0].data.push(cpu);
        chart.data.datasets[1].data.push(ram);

        while (chart.data.labels.length > MAX_POINTS) {
            chart.data.labels.shift();
            chart.data.datasets[0].data.shift();
            chart.data.datasets[1].data.shift();
        }

        chart.update();
    }

    function updateChart() {
        fetch('loader.php?action=system_info' + tokenQuery + '&_=' + Date.now(), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            var label = new Date().toLocaleTimeString();
            var cpu = data.load != null ? data.load : null;
            var ram = (data.memory_usage && data.memory_usage.usage != null) ? data.memory_usage.usage : null;
            setText('cpuNow', cpu != null ? cpu + '%' : 'n/a');
            setText('ramNow', ram != null ? ram + '%' : 'n/a');
            pushPoint(label, cpu, ram);
        })
        .catch(function (err) {
            setText('cpuNow', 'n/a');
            setText('ramNow', 'n/a');
            console.error('Error fetching data:', err);
        });
    }

    function updateOnLoad() {
        fetch('loader.php?action=general' + tokenQuery + '&_=' + Date.now(), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            setText('osData', data.os_data || '—');
            setText('cpuCount', data.cpu_count != null ? String(data.cpu_count) : '—');
            setText('phpVer', data.php_ver || '—');
        })
        .catch(function (err) {
            console.error('Error fetching initial data:', err);
        });
    }

    updateOnLoad();
    updateChart();
    setInterval(updateChart, 2000);
})();
</script>
</body>
</html>
