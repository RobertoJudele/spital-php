<?php
require_once 'app/middleware/Auth.php';

class ExternalDataController
{
    // Import medicamente din API extern (exemplu: OpenFDA)
    public static function importMedications()
    {
        Auth::requireRole('admin');

        // Parsare date din API extern
        $apiUrl = 'https://api.fda.gov/drug/label.json?limit=50';
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        global $pdo;
        $imported = 0;

        foreach ($data['results'] ?? [] as $drug) {
            $of = $drug['openfda'] ?? [];

            // brand → generic → substance → titlu → indicații; dacă nu există, sar peste
            $name = !empty($of['brand_name'][0])
                ? trim($of['brand_name'][0])
                : (!empty($of['generic_name'][0])
                    ? trim($of['generic_name'][0])
                    : (!empty($of['substance_name'][0])
                        ? trim($of['substance_name'][0])
                        : (!empty($drug['spl_product_data_elements'][0])
                            ? trim($drug['spl_product_data_elements'][0])
                            : (!empty($drug['indications_and_usage'][0])
                                ? mb_substr(
                                    trim($drug['indications_and_usage'][0]),
                                    0,
                                    60,
                                )
                                : ''))));

            if ($name === '' || strcasecmp($name, 'unknown') === 0) {
                continue; // nu insera fără nume util
            }

            $description =
                $drug['description'][0] ??
                ($drug['indications_and_usage'][0] ?? '');

            $stmt = $pdo->prepare(
                "INSERT IGNORE INTO medications (name, description, source) VALUES (:name, :desc, 'FDA_API')",
            );
            if (
                $stmt->execute([
                    'name' => mb_substr($name, 0, 255),
                    'desc' => mb_substr($description, 0, 1000),
                ])
            ) {
                $imported++;
            }
        }

        $_SESSION[
            'msg'
        ] = "Imported {$imported} medications from external source.";
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/admin/dashboard");
    }
}
