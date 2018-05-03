<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$settings['displayErrorDetails'] = true;
$settings['addContentLengthHeader'] = false;

$settings['db']['host'] = '127.0.0.1';
$settings['db']['user'] = 'root';
$settings['db']['pass'] = '';
$settings['db']['dbname'] = 'restapi';

$app = new \Slim\App(['settings' => $settings]);

$container = $app->getContainer();

$container['db'] = function ($c) {
	$db = $c['settings']['db'];
	$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=utf8', $db['user'], $db['pass']);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
};

$app->get('/customer', function (Request $request, Response $response, array $args) {
	$sth = $this->db->prepare("SELECT `id`, `first_name`, `last_name` FROM `customer`");
	$sth->execute();
	$customers = $sth->fetchAll(PDO::FETCH_ASSOC);

	return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($customers));
});

$app->get('/customer/{id}', function (Request $request, Response $response, array $args) {
	$sth = $this->db->prepare("SELECT `id`, `first_name`, `last_name` FROM `customer` WHERE `id` = ?");
	$sth->execute([$args['id']]);
	$customers = $sth->fetchAll(PDO::FETCH_ASSOC);

	return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($customers));
});

$app->post('/customer', function (Request $request, Response $response) {

	$bodyContent = $request->getBody()->getContents();

	$data = json_decode($bodyContent, true);

	$sth = $this->db->prepare("INSERT INTO `customer` (`first_name`, `last_name`, `email`) VALUES(?, ?, ?)");
	$sth->execute([$data['first_name'], $data['last_name'], $data['email']]);

	$customer_id = $this->db->lastInsertId();
	
	// TODO ADD To Rabit MQ

	return $response->withStatus(201)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode(["id" => $customer_id]));
});

$app->run();
