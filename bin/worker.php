<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;

require '../vendor/autoload.php';

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('worker', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg) {
	echo " [x] Received ", $msg->body, "\n";

	$json = GuzzleHttp\json_decode($msg->body, true);

	if(is_array($json) && isset($json['action'])) {
		switch($json['action']) {
			case 'registration_email':
				echo " Send registration email\n";

				$name = trim($json['data']['first_name'] . " " . $json['data']['last_name']);

				$to = $name . " <" . $json['data']['email'] . ">";
				$subject = 'Nová registrace';
				$body = 'Dobrý den, ' . $name . '\r\n'
					. 'Registrace vašeho účtu proběhla úspěšně';
				$headers = 'From: restapi@mall.cz' . "\r\n";

				echo ' Send registration email, TO: ' . $to . ', SUBJECT: ' . $subject . ', BODY: ' . $body . ', HEADERS: ' . $headers;

				mail($to, $subject, $body, $headers);

				break;

			default:
				echo " Not supported action `" . $json["action"] . "`.\n";
				break;
		}
	} else {
		echo " Not supported message body.\n";
	}

	$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('hello', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
	$channel->wait();
}