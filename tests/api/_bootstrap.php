<?php

$settings = parse_ini_file(__DIR__ . '/../config/config.ini', true, INI_SCANNER_TYPED);

$pdo = new PDO('mysql:host=' . $settings['db']['server'] . ';dbname=' . $settings['db']['database_name'] . ';charset=' . $settings['db']['charset'], $settings['db']['username'], $settings['db']['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->query('DROP TABLE IF EXISTS `customer`');
$pdo->query(file_get_contents(__DIR__ . '/../../sql/db.sql'));
$pdo->query(file_get_contents(__DIR__ . '/../_data/dump.sql'));