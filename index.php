<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;



$app = new Slim();

$app->config('debug', true);

// ROUTE INDEX SITE
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

// ROUTE INDEX ADMIN
$app->get('/admin', function() {
    
    // SÓ ACESSA SE ESTIVER LOGADO
    User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

// ROUTE TELA DE LOGIN
// ROUTE INDEX ADMIN
$app->get('/admin/login', function() {
    
    // PASSANDO AS OPÇÕES PARA O MÉTODO CONSTRUTOR, PARA DESARTIVARMOS O USO DO "header" e do "footer" POIS NESSE CASO ELES FICAM DIRETO NO ARQUIVO DA PÁG DE LOGIN, POIS SERÁ DIFERENTE DAS OUTRAS PÁGIANAS
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("login");

});

// ROUTE DE AUTENTICAÇÃO DO LOGIN
$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");

	exit;

});

// ROUTE DE LOGOUT
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;
});



$app->run();

 ?>