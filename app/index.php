<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GLOBUS.studio - Test Area stat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
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
            background: rgba(0, 0, 0, 0.5); /* Optional: for better visibility */
            padding: 0.5rem 0;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-transparent">
    <div class="container">
        <a class="navbar-brand"><img src="logo.png" alt="GLOBUS.studio test area stat" height="64" width="64"></a>
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

<div class="container canvas-container">
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

<footer class="text-center">
    <p><b>GLOBUS.studio</b> - Success in persistence!</p>
</footer>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    var dataPointsLoad = [];
    var dataPointsMemory = [];

    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: 'CPU',
                    data: dataPointsLoad,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false
                },
                {
                    label: 'RAM',
                    data: dataPointsMemory,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    fill: false
                }
            ]
        },
        options: {
            animation: false,
            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        unit: 'second',
                        unitStepSize: 2
                    },
                    gridLines: {
                        color: 'rgba(255, 255, 255, 0.2)'
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    },
                    gridLines: {
                        color: 'rgba(255, 255, 255, 0.2)'
                    }
                }]
            }
        }
    });

    function addDataPoint(dataPoints, timestamp, value) {
        dataPoints.push({ x: timestamp, y: value });
        if (dataPoints.length > 35) {
            dataPoints.shift();
        }
    }

    function updateChart() {
        $.getJSON('loader.php?action=system_info', function(data) {
            var timestamp = new Date().getTime();
            addDataPoint(dataPointsLoad, timestamp, data.load);
            addDataPoint(dataPointsMemory, timestamp, data.memory_usage.usage);
            chart.update();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching data: ' + textStatus);
        });
    }

    setInterval(updateChart, 2000);

    function updateOnLoad() {
        $.getJSON('loader.php?action=general', function(data) {
            $('#cpuCount').text(data.cpu_count);
            $('#phpVer').text(data.php_ver);
            $('#osData').text(data.OS_data);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching initial data: ' + textStatus);
        });
    } 
    
    $(document).ready(function() {
        updateOnLoad();
    });        
</script>
</body>
</html>