<?php

define('DB_HOST', getenv('MYSQL_HOST') ?: 'mysql');
define('DB_PORT', getenv('MYSQL_PORT') ?: '3306');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'vtiger');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: 'root');
define('DB_ROOT', getenv('MYSQL_ROOT_PASSWORD') ?: 'root');

date_default_timezone_set('America/Los_Angeles');

echo DB_HOST.' '.DB_PORT.' '.DB_NAME.' '.DB_USER.' '.DB_PASS.' '.DB_ROOT."\n";

require_once __DIR__.'/vendor/autoload.php';

use Javanile\HttpRobot\HttpRobot;

echo "[vtiger] setup wizard start...\n";
$robot = new HttpRobot([
    'base_uri' => 'http://localhost/',
    'cookies'  => true,
]);

// Get session token
$vtrftk = $robot->get('index.php?module=Install&view=Index&mode=Step4', '__vtrftk');
echo "[vtiger] form token: {$vtrftk}\n";

// Submit installation params
$values = $robot->post(
    'index.php',
    [
        '__vtrftk' => $vtrftk,
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step5',
        'db_type' => 'mysqli',
        'db_hostname' => DB_HOST,
        'db_username' => DB_USER,
        'db_password' => DB_PASS,
        'db_name' => DB_NAME,
        'db_root_username' => '',
        'db_root_password' => '',
        'currency_name' => 'USA, Dollars',
        'admin' => 'admin',
        'password' => 'admin',
        'retype_password' => 'admin',
        'firstname' => '',
        'lastname' => 'Administrator',
        'admin_email' => 'vtiger@localhost.lan',
        'dateformat' => 'dd-mm-yyyy',
        'timezone' => 'America/Los_Angeles',
    ],
    ['__vtrftk', 'auth_key']
);
echo "[vtiger] form token: '{$values['__vtrftk']}', auth token: '{$values['auth_key']}'\n";

// Confirm installation
$values = $robot->post(
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step6',
    ],
    ['__vtrftk', 'auth_key']
);
echo "[vtiger] form token: '{$values['__vtrftk']}', auth token: '{$values['auth_key']}'\n";

// Select industry sector
$values = $robot->post(
    'index.php',
    [
        '__vtrftk' => $values['__vtrftk'],
        'auth_key' => $values['auth_key'],
        'module' => 'Install',
        'view' => 'Index',
        'mode' => 'Step7',
        'industry' => 'Accounting',
    ],
    ['__vtrftk']
);

// First login
$values = $robot->post(
    'index.php?module=Users&action=Login',
    [
        '__vtrftk' => $values['__vtrftk'],
        'username' => 'admin',
        'password' => 'admin',
    ],
    ['__vtrftk']
);

// Setup crm modules
$values = $robot->post(
    'index.php?module=Users&action=SystemSetupSave',
    [
        '__vtrftk' => $values['__vtrftk'],
        'packages[Tools]' => 'on',
        'packages[Sales]' => '',
        'packages[Marketing]' => '',
        'packages[Support]' => '',
        'packages[Inventory]' => '',
        'packages[Project]' => '',
    ],
    ['__vtrftk']
);

// Save user settings
$values = $robot->post(
    'index.php?module=Users&action=UserSetupSave',
    [
        '__vtrftk' => $values['__vtrftk'],
        'currency_name' => 'Euro',
        'lang_name' => 'it_it',
        'time_zone' => 'Europe/Amsterdam',
        'date_format' => 'dd-mm-yyyy',
    ],
    ['__vtrftk']
);