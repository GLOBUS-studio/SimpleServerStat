<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GLOBUS.studio - Test Area stat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
  background-image: url('back.webp');
  background-size: cover;
  background-color: black !important;
}
</style>
<style>
        html, body {
            height: 100%;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;            
        }
        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }  
        .canvas-class {
            margin-top: 3rem!important;
            padding-top: 3rem!important;             
        }
        @media (max-width: 992px) {
          .canvas-class {
            margin-top: 3rem!important;
            padding-top: 0rem!important; 
          }
          .mob-hidden {
            display: none;
          }
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
                    <li class="nav-item text-white">
                        <a class="nav-link text-white" href="https://globus.studio"><b>GLOBUS.studio</b></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-center canvas-class" style="/*min-height: calc(100vh - 50px);*/">
        <div class="row">
            <div class="col-lg-6">
                <canvas id="myChart"></canvas>
            </div>
            <div class="col-lg-6 pe-5 ps-5 text-white">
                <h1 class="mob-hidden">TEST AREA info</h1>
                <p class="mob-hidden">This page contains technical information that is intended solely for the use of technical personnel. 
                    If you are not a technical expert, please do not attempt to use or implement any information 
                    from this page without first consulting with an appropriate expert.</p>
<p class="visually-hidden">
Disclaimer:<br>
The information provided on this page is provided "as is" without warranty of any kind, either expressed or implied, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose. The owners and creators of this page do not assume any liability or responsibility for any errors or omissions in the content of this page. The use of this information is at your own risk.
</p>
               <ul>
                    <li id="osData"></li>
                    <li id="opgss">GLOBUS.studio Server Control Panel v2.54b</li>
                    <li id="phpVer"></li>
                    <li id="cpuCount"></li>
               </ul>
           
            </div>
        </div>
    </div>

    <footer class="bg-transparent text-white text-center">
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
        if (dataPoints.length > 60) {
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