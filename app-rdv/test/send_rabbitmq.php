<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connexion simple à RabbitMQ
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'toubi', 'toubi');
$channel = $connection->channel();

// Définir l'exchange 
$exchange = 'rdv_events';
$routing_key = 'rdv.created.patient';

// Message 
$data = 'Test message depuis PHP';
$msg = new AMQPMessage($data);

// Publier le message
$channel->basic_publish($msg, $exchange, $routing_key);

echo "Message envoyé !\n";

$channel->close();
$connection->close();
