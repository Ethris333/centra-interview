<?php
use KanbanBoard\Authentication;
use KanbanBoard\Github;
use KanbanBoard\Utilities;
use KanbanBoard\Application;

require '../classes/KanbanBoard/Github.php';
require '../classes/Utilities.php';
require '../classes/KanbanBoard/Authentication.php';

error_reporting(-1);
ini_set('display_errors', 'On');

Utilities::setEnvironmentVariables();

$repositories = explode('|', Utilities::env('GH_REPOSITORIES'));
$authentication = new Authentication();
$token = $authentication->login();
$github = new Github($token, Utilities::env('GH_ACCOUNT'));
$board = new Application($github, $repositories, [Application::WAITING]);
$data = $board->board();

$m = new Mustache_Engine([
	'loader' => new Mustache_Loader_FilesystemLoader('../views'),
]);

echo $m->render('index', ['milestones' => $data]);
