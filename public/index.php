<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    error_log("Could not load .env file: " . $e->getMessage());
    die("Configuration error: .env file not found or not readable. Please ensure it exists in the project root.");
} catch (Exception $e) {
    error_log("Error loading .env file: " . $e->getMessage());
    die("Configuration error loading .env file.");
}


use App\Utils\Helpers;

date_default_timezone_set("Asia/Bangkok");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Use getenv() and provide defaults if the variable is not set or empty
$appName = Helpers::e(getenv('APP_NAME') ? getenv('APP_NAME') : 'Antrian Farmasi');
$hospitalIcon = Helpers::e(getenv('HOSPITAL_ICON') ? getenv('HOSPITAL_ICON') : 'assets/img/rs.png');
$backgroundImage = Helpers::e(getenv('BACKGROUND_IMAGE') ? getenv('BACKGROUND_IMAGE') : 'assets/img/farmasi.jpg');

?>
<!doctype html>
<html lang="id">
<head>
    <title><?php echo $appName; ?></title>
    <link rel="icon" href="<?php echo $hospitalIcon; ?>" type="image/x-icon">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <link type="text/css" rel="stylesheet" href="assets/css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="assets/css/jquery-ui.css"  media="screen,projection"/>
    <link rel="stylesheet" href="assets/css/marquee.css" />
    <link rel="stylesheet" href="assets/css/example.css" />
    <link rel="stylesheet" href="assets/css/ok.css" />
    <style type="text/css">
        .bg::before {
            content: '';
            background-image: url('<?php echo $backgroundImage; ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: scroll;
            position: fixed;
            z-index: -1;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            opacity: 0.15;
            filter:alpha(opacity=15);
        }
        .biru-rspi {
            background: #26a69a;
        }
        .judul-tabel-header{
            font-size: 35px;
        }
        .isi-tabel{
            font-size: 35px;
            font-weight: bold;
            text-shadow: 0px 0px 2px #26a69a;
        }
        td {
            padding: 0px 0px 10px 15px;
        }
        #the-table-container {
            position: relative;
             /* overflow:hidden; /* Uncomment if you use CSS animation for scrolling */
        }
        table{
            width: 100%;
        }
    </style>
</head>
<body class="bg">
    <header>
        <nav class="biru-rspi">
            <div class="nav-wrapper">
                <ul class="center hide-on-med-and-down" id="nv">
                    <li>
                        <a href="./" class="ams hide-on-med-and-down"><i class="material-icons md-36">local_hospital</i> <?php echo $appName; ?></a>
                    </li>
                    <li class="right" style="margin-right: 10px;">
                        <i class="material-icons">perm_contact_calendar</i>
                        <a href="#" class="white-text">
                            <?php echo Helpers::formatIndonesianDate(); ?>
                        </a>
                        <i class="material-icons md-12">query_builder</i>
                        <a href="#" class="white-text" id="jam"></a>
                  </li>
                </ul>
            </div>
        </nav>
    </header>

    <main style="height: 836px">
        <div class="container-fluid" id="judul-container">
            <!-- Content loaded by AJAX from api/queue-title.php -->
        </div>
        <div class="container-fluid" id="the-table-container" style="height: calc(100% - 200px); overflow-y: auto;"> <!-- Adjust height as needed -->
            <table class="default">
                <thead>
                   <tr class='judul-tabel-header'>
                        <td style="width: 7.5%"><b>No Urut</b></td>
                        <td style="width: 22.5%"><b>Nama Pasien</b></td>
                        <td style="width: 15%"><b>Jenis Resep</b></td>
                        <td style="width: 15%"><b>Alamat</b></td>
                        <td style="width: 22.5%"><b>Status</b></td>
                   </tr>
                </thead>
                <tbody id="data-container" style="display: block;">
                <!-- Content loaded by AJAX from api/queue-data.php -->
                </tbody>
            </table>
        </div>
    </main>
    <div id="audio-player"></div>

    <script type="text/javascript" src="assets/js/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="assets/js/materialize.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <script data-pace-options='{ "ajax": false }' src='assets/js/pace.min.js'></script>
    <script type="text/javascript" src="assets/js/marquee.js"></script>

    <script type="text/javascript">
       function updateClock() {
            var e = document.getElementById('jam'),
            d = new Date(), h, m, s;
            h = d.getHours();
            m = d.getMinutes();
            s = d.getSeconds();

            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;

            e.innerHTML = h +':'+ m +':'+ s;
       }

       function loadQueueData() {
            $.ajax({
                url: 'api/queue-data.php',
                method: 'GET',
                success: function(response) {
                    $('#data-container').html(response).fadeIn("slow");
                },
                error: function() {
                    $('#data-container').html('<tr><td colspan="5" class="isi-tabel" style="text-align:center;">Gagal memuat data antrian.</td></tr>');
                }
            });
       }

       function loadQueueTitle() {
            $.ajax({
                url: 'api/queue-title.php',
                method: 'GET',
                success: function(response) {
                    var $response = $(response);
                    var $audioTag = $response.find("audio[autoplay='true']");
                    if ($audioTag.length > 0) {
                        $("#audio-player").empty().append($audioTag.clone());
                    }
                    $('#judul-container').html($response).fadeIn("slow");
                },
                error: function() {
                    $('#judul-container').html('<div class="card red white-text"><div class="card-content"><p>Gagal memuat judul antrian.</p></div></div>');
                }
            });
       }
       
       $(document).ready(function() {
            updateClock();
            setInterval(updateClock, 1000);

            loadQueueData();
            loadQueueTitle();

            // setInterval(loadQueueData, 9000);
            // setInterval(loadQueueTitle, 9000);
       });
    </script>
</body>
</html>