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
    <title>GLOBUS.studio - Test Area stat</title>
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA=="
        crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('back.webp');
            background-size: cover;
            background-attachment: fixed;
            color: white;
        }
        html, body {
            height: 100%;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
        }
        h1, footer {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        .canvas-container {
            padding-top: 3rem;
        }
        @media (max-width: 768px) {
            .canvas-container {
                padding-top: 1rem;
            }
            .info-text, .info-list {
                text-align: center;
                padding: 0 1rem;
            }
            .info-list {
                padding-bottom: 2rem;
            }
        }
        footer {
            background: rgba(0, 0, 0, 0.5);
            padding: 0.5rem 0;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-light bg-transparent">
    <div class="container">
        <a class="navbar-brand" href="#"><img src="logo.png" alt="GLOBUS.studio test area stat" height="64" width="64"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-white" href="https://globus.studio"><b>GLOBUS.studio</b></a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container canvas-container flex-grow-1">
    <div class="row">
        <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
            <canvas id="myChart"></canvas>
        </div>
        <div class="col-lg-6 col-md-12 info-text">
            <h1>TEST AREA info</h1>
            <p>The content on this page is exclusively for the use of qualified technical staff. If you lack technical expertise, please refrain from applying any information found here without seeking guidance from a qualified professional first.</p>
            <ul class="info-list">
                <li id="osData"></li>
                <li id="opgss">GLOBUS.studio SimpleServerStat</li>
                <li id="phpVer"></li>
                <li id="cpuCount"></li>
            </ul>
        </div>
    </div>
</div>

<footer class="mt-auto text-center">
    <p><b>GLOBUS.studio</b> - Success in persistence!</p>
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
    var cacheBust = '&_=' + Date.now();

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
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    fill: false,
                    tension: 0.2
                },
                {
                    label: 'RAM',
                    data: [],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: false,
                    tension: 0.2
                }
            ]
        },
        options: {
            animation: false,
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#fff' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255, 255, 255, 0.2)' }
                },
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255, 255, 255, 0.2)' }
                }
            }
        }
    });

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
            pushPoint(label, cpu, ram);
        })
        .catch(function (err) {
            console.error('Error fetching data:', err);
        });
    }

    function updateOnLoad() {
        fetch('loader.php?action=general' + tokenQuery + cacheBust, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            var cpuEl = document.getElementById('cpuCount');
            var phpEl = document.getElementById('phpVer');
            var osEl = document.getElementById('osData');
            if (cpuEl) cpuEl.textContent = data.cpu_count || '';
            if (phpEl) phpEl.textContent = data.php_ver || '';
            if (osEl) osEl.textContent = data.os_data || '';
        })
        .catch(function (err) {
            console.error('Error fetching initial data:', err);
        });
    }

    updateOnLoad();
    setInterval(updateChart, 2000);
})();
</script>
</body>
</html>
