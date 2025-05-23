<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../..');
    $dotenv->load();
} catch (Exception $e) { // Catch general Exception for Dotenv
    error_log("Could not load .env file in queue-title.php: " . $e->getMessage());
    echo "<div class='card red white-text'><div class='card-content'><p>Kesalahan konfigurasi.</p></div></div>";
    exit;
}

use App\Config\Database;
use App\Utils\Helpers;

header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$dbInstance = Database::getInstance();
$db = $dbInstance->getConnection();

$pasienNameToDisplay = '';
$playBell = false;

$sqlAntriApotek = "SELECT no_rawat, status FROM antriapotek2 LIMIT 1";
$stmtAntri = $db->prepare($sqlAntriApotek);
$stmtAntri->execute();
$dataAntri = $stmtAntri->fetch(PDO::FETCH_ASSOC);

if ($dataAntri) {
    $noRawat = $dataAntri['no_rawat']; // Store in variable for clarity
    $sqlPasien = "SELECT pasien.nm_pasien 
                  FROM reg_periksa 
                  INNER JOIN pasien ON reg_periksa.no_rkm_medis=pasien.no_rkm_medis 
                  WHERE reg_periksa.no_rawat = :no_rawat";
    $stmtPasien = $db->prepare($sqlPasien);
    $stmtPasien->bindParam(':no_rawat', $noRawat);
    $stmtPasien->execute();
    $pasienData = $stmtPasien->fetch(PDO::FETCH_ASSOC);
    if ($pasienData) {
        $pasienNameToDisplay = Helpers::e($pasienData['nm_pasien']);
    }

    if ($dataAntri['status'] == "1") {
        $playBell = true;
        $updateSql = "UPDATE antriapotek2 SET status='0' WHERE no_rawat = :no_rawat_update"; // Use different placeholder name
        $stmtUpdate = $db->prepare($updateSql);
        $stmtUpdate->bindParam(':no_rawat_update', $noRawat);
        $stmtUpdate->execute();
    }
}

?>
<div class="row">
    <div class="col s12" id="header-instansi">
        <div class="card biru-rspi white-text">
            <div class="card-content" style="padding: 10px 24px;"> <!-- Reduced padding slightly -->
                <h5 style="margin:0; font-size: 28px; line-height: 1.2;"> <!-- Adjusted font size and line height -->
                    <table border='0' width='100%'>
                        <tr border='0'>
                            <td style='font-weight:bold;'>
                                Panggilan Validasi Resep  : 
                                <?php echo $pasienNameToDisplay; ?>
                                <?php if ($playBell): ?>
                                    <audio autoplay='true' src='assets/bell.wav' type='audio/wav'>Browser Anda tidak mendukung elemen audio.</audio>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </h5>
            </div>
        </div>
    </div>
</div>