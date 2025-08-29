<?php
$routes = [
    'spital/patients/index' => ['PatientController', 'index'],
    'spital/patients/show' => ['PatientController', 'show'],
    'spital/patients/create' => ['PatientController', 'create'],
    'spital/patients/edit' => ['PatientController', 'edit'],
    'spital/patients/delete' => ['PatientController', 'delete'],
    'spital/doctors/index' => ['DoctorController', 'index'],
    'spital/doctors/show' => ['DoctorController', 'show'],
    'spital/doctors/create' => ['DoctorController', 'create'],
    'spital/doctors/edit' => ['DoctorController', 'edit'],
    'spital/doctors/delete' => ['DoctorController', 'delete'],
    'spital/auth/auth' => ['AuthController', 'auth'],
    'spital/auth/login' => ['AuthController', 'auth'],
    'spital/admin/dashboard' => ['AdminController', 'dashboard'],
    'spital/auth/logout' => ['AuthController', 'logout'],
    'spital/patients/dashboard' => ['PatientController', 'dashboard'],
    'spital/appointments/request' => ['AppointmentsController', 'request'],
    'spital/appointments/show' => ['AppointmentsController', 'show'],
    'spital/appointments/approve' => ['AppointmentsController', 'approve'],
    'spital/appointments/reject' => ['AppointmentsController', 'reject'],
    'spital/medical-records/show' => ['MedicalRecordController', 'show'],
    'spital/consultations/show' => ['ConsultationController', 'show'],
    'spital/prescriptions/show' => ['PrescriptionController', 'show'],
    'spital/doctor/dashboard' => ['DoctorController', 'dashboard'],
    'spital/doctor/create-medrec' => [
        'DoctorController',
        'createMedicalRecord',
    ],
    'spital/doctor/create-consult' => [
        'DoctorController',
        'createConsultation',
    ],
    'spital/doctor/create-prescription' => [
        'DoctorController',
        'createPrescription',
    ],
    'spital/doctor/create-admission' => ['DoctorController', 'createAdmission'],
    'spital/admin/create-room' => ['AdminController', 'createRoom'],
    'spital/admin/import-medications' => [
        'ExternalDataController',
        'importMedications',
    ],
    'spital/admin/import-patients' => ['ExportController', 'importPatients'],
    'spital/admin/export-patients' => ['ExportController', 'exportPatients'],
    'spital/doctor/export-report' => [
        'ExportController',
        'exportMedicalReport',
    ],
    'spital/stats/dashboard' => ['StatsController', 'dashboard'],
    'spital/stats/data' => ['StatsController', 'data'],
];

class Router
{
    private $uri;

    public function __construct()
    {
        // Use query param if present to avoid PATH_INFO issues
        if (!empty($_GET['r'])) {
            $this->uri = trim($_GET['r'], '/');
            return;
        }

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // e.g. /spital-php
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // strip base folder
        if ($base && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        $path = ltrim($path, '/');

        // strip index.php prefix if present
        if (strpos($path, 'index.php/') === 0) {
            $path = substr($path, strlen('index.php/'));
        } elseif ($path === 'index.php') {
            $path = '';
        }

        $this->uri = trim($path, '/'); // e.g. spital/patients/create
    }

    public function direct()
    {
        global $routes;
        if (!isset($routes[$this->uri])) {
            require_once __DIR__ . '/../app/views/404.php';
            return;
        }

        [$controller, $method] = $routes[$this->uri];
        $controllerPath =
            __DIR__ . '/../app/controllers/' . $controller . '.php';
        if (!file_exists($controllerPath)) {
            require_once __DIR__ . '/../app/views/404.php';
            return;
        }
        require_once $controllerPath;

        if (!method_exists($controller, $method)) {
            require_once __DIR__ . '/../app/views/404.php';
            return;
        }
        call_user_func([$controller, $method]);
    }
}
