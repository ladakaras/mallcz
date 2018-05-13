<?php

define('ROOT_DIR', __DIR__ . '/../../');
define('API_DIR', ROOT_DIR . 'api/');

require_once API_DIR . 'vendor/autoload.php';

$settings = parse_ini_file(API_DIR . 'config/config.ini', true, INI_SCANNER_TYPED);

$app = new \Slim\App(['settings' => $settings]);

$container = $app->getContainer();

$container['db'] = function ($c) {
	return new Medoo\Medoo($c['settings']['db']);
};

require API_DIR . 'include/BadInputException.php';

require API_DIR . 'routes/customer.php';

$app->run();
