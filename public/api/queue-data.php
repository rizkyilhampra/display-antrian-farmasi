<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../..');
    $dotenv->load();
} catch (Exception $e) {
    error_log("Could not load .env file in queue-data.php: " . $e->getMessage());
    echo "<tr><td colspan='5' class='isi-tabel' style='text-align:center;'>Kesalahan konfigurasi.</td></tr>";
    exit;
}

use App\Config\Database;
use App\Utils\Helpers;

header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$db = Database::getInstance()->getConnection();

$kd_poli_list = array( 'kfr', 'U0002', 'U0004', 'U0005', 'U0008', 'U0009', 'U0011', 'U0012', 'U0015', 'U0016', 'U0018', 'U0019', 'U0023', 'U0027', 'U0033', 'U0035', 'U0036', 'U0037', 'U0038', 'U0039', 'U0040', 'U0042', 'U0043', 'U0044', 'U0046', 'U0047', 'U0048', 'U0049', 'U0050', 'U0051', 'U0052', 'U0054', 'U0055', 'U0057', 'U0059', 'U0060', 'U0061');
$placeholders = implode(',', array_fill(0, count($kd_poli_list), '?'));

$countSql = "SELECT COUNT(*)
             FROM resep_obat
             INNER JOIN reg_periksa ON resep_obat.no_rawat=reg_periksa.no_rawat
             WHERE resep_obat.jam_peresepan <> '00:00:00'
                   AND resep_obat.status = 'ralan'
                   AND reg_periksa.kd_poli IN ($placeholders)
                   AND resep_obat.tgl_peresepan = CURDATE()
                   AND resep_obat.jam_penyerahan = '00:00:00'";
$stmtCount = $db->prepare($countSql);
$stmtCount->execute($kd_poli_list);
$totalRows = (int)$stmtCount->fetchColumn();
$i = $totalRows;

$dataSql = "SELECT
                resep_obat.no_resep,
                pasien.nm_pasien,
                resep_obat.jam_peresepan,
                IF(resep_obat.jam = '00:00:00', '', resep_obat.jam) AS jam_validasi,
                pasien.alamat AS alamat_pasien,
                poliklinik.nm_poli,
                (
                    SELECT COUNT(rdr.no_resep)
                    FROM resep_dokter_racikan rdr
                    WHERE rdr.no_resep = resep_obat.no_resep
                ) > 0 AS is_racikan
            FROM
                resep_obat
            INNER JOIN reg_periksa ON resep_obat.no_rawat = reg_periksa.no_rawat
            JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE
                resep_obat.jam_peresepan <> '00:00:00'
                AND resep_obat.status = 'ralan'
                AND resep_obat.jam_penyerahan = '00:00:00'
                AND reg_periksa.kd_poli IN ($placeholders)
                AND resep_obat.tgl_peresepan = CURDATE()
            ORDER BY resep_obat.no_resep DESC";

$stmtData = $db->prepare($dataSql);
$stmtData->execute($kd_poli_list);

$output = '';
if ($stmtData->rowCount() > 0) {
    while($data = $stmtData->fetch(PDO::FETCH_ASSOC)) {
        $output .= "<tr class='isi-tabel'>";
        $output .= "<td style='width: 7.5%'>" . Helpers::e($i) . "</td>";
        $output .= "<td style='width: 22.5%'>" . Helpers::e($data['nm_pasien']) . "</td>";
        $output .= "<td style='width: 15%'>" . ($data['is_racikan'] ? "Racikan" : "Non Racikan") . "</td>";
        $output .= "<td style='width: 15%'>" . Helpers::e(Helpers::trim_text($data['alamat_pasien'])) . "</td>";
        $statusText = '';
        if (!empty($data['jam_peresepan']) && empty($data['jam_validasi'])) {
            $statusText = 'Resep Masuk';
        } elseif (!empty($data['jam_peresepan']) && !empty($data['jam_validasi'])) {
            $statusText = 'Resep Disiapkan';
        }
        $output .= "<td style='width: 22.5%'>" . Helpers::e($statusText) . "</td>";
        $output .= "</tr>";
        $i--;
    }
} else {
    $output = "<tr><td colspan='5' class='isi-tabel' style='text-align:center;'>Tidak ada antrian saat ini.</td></tr>";
}
echo $output;