<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

define('ROOT_DIR', '/restapi/');
//define('ROOT_DIR', __DIR__ . '/../../');
define('API_DIR', ROOT_DIR . '/api/');

require_once API_DIR . 'vendor/autoload.php';

$settings = parse_ini_file(ROOT_DIR . 'config/config.ini', true, INI_SCANNER_TYPED);

$app = new \Slim\App(['settings' => $settings]);

$container = $app->getContainer();

$container['db'] = function ($c) {
	return new Medoo\Medoo($c['settings']['db']);
};

require API_DIR . 'include/CustomerModel.php';
require API_DIR . 'include/BadInputException.php';
$container['customerModel'] = function ($c) {
	return new CustomerModel($c->db);
};

$container['rabbitMQ'] = function ($c) {
	$rabbitMQ = $c['settings']['rabbitMQ'];

	return new AMQPStreamConnection($rabbitMQ['host'], $rabbitMQ['port'], $rabbitMQ['user'], $rabbitMQ['pass']);
};

$app->get('/customer', function (Request $request, Response $response, array $args) {
	$customers = $this->customerModel->getAll();

	return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($customers));
});

$app->get('/customer/{id}', function (Request $request, Response $response, array $args) {
	$customer = $this->customerModel->get($args['id']);

	if($customer === []) {
		return $response->withStatus(404)
				->withHeader('Content-Type', 'application/json')
				->write(json_encode(['message' => 'Customer not found.']));
	}

	return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($customer));
});

$app->post('/customer', function (Request $request, Response $response) {

	$bodyContent = $request->getBody()->getContents();

	$data = json_decode($bodyContent, true);

	try {
		$customer_id = $this->customerModel->insert($data);
	} catch(BadInputException $exc) {
		return $response->withStatus(400)
				->withHeader('Content-Type', 'application/json')
				->write(json_encode(['message' => $exc->getInputMessage()]));
	}

	$connection = $this->rabbitMQ;
	$channel = $connection->channel();
	$channel->queue_declare('worker', false, true, false, false);

	$msg = new AMQPMessage(json_encode(['action' => 'registration_email', 'data' => ['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name']]]), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
	$channel->basic_publish($msg, '', 'hello');

	$channel->close();
	$connection->close();

	return $response->withStatus(201)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode(["id" => $customer_id]));
});

$app->put('/customer/{id}', function (Request $request, Response $response, array $args) {
	if($this->customerModel->exist($args['id']) === false) {
		return $response->withStatus(404)
				->withHeader('Content-Type', 'application/json')
				->write(json_encode(['message' => 'Customer not found.']));
	}

	$bodyContent = $request->getBody()->getContents();

	$data = json_decode($bodyContent, true);

	$updated = $this->customerModel->update($args['id'], $data);

	return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode(['message' => $updated ? 'Customer updated.' : 'Nothing change.']));
});

$app->delete('/customer/{id}', function (Request $request, Response $response, array $args) {
	if($this->customerModel->delete($args['id'])) {
		return $response->withStatus(200)
				->withHeader('Content-Type', 'application/json')
				->write(json_encode(['message' => 'Customer deleted.']));
	} else {
		return $response->withStatus(404)
				->withHeader('Content-Type', 'application/json')
				->write(json_encode(['message' => 'Customer not found.']));
	}
});

$app->run();
