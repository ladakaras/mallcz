<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require API_DIR . 'include/CustomerModel.php';
$container['customerModel'] = function ($c) {
	return new CustomerModel($c->db);
};

$container['rabbitMQ'] = function ($c) {
	$rabbitMQ = $c['settings']['rabbitMQ'];

	return new AMQPStreamConnection($rabbitMQ['host'], $rabbitMQ['port'], $rabbitMQ['user'], $rabbitMQ['pass']);
};

$app->get('/customers', function (Request $request, Response $response, array $args) {
	$customers = $this->customerModel->getAll();

	return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($customers));
});

$app->get('/customers/{id}', function (Request $request, Response $response, array $args) {
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

$app->post('/customers', function (Request $request, Response $response) {

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
	$channel->basic_publish($msg, '', 'worker');

	$channel->close();
	$connection->close();

	return $response->withStatus(201)
			->withHeader('Location', '/customers/' . $customer_id)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($this->customerModel->get($customer_id)));
});

$app->put('/customers/{id}', function (Request $request, Response $response, array $args) {
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
			->write(json_encode($this->customerModel->get($args['id'])));
});

$app->delete('/customers/{id}', function (Request $request, Response $response, array $args) {
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
