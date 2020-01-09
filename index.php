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

// ROUTE DA LISTA DE USUÁRIOS
$app->get('/admin/users', function () {

	User::verifyLogin();

	// TRAZENDO TODOS OS USUÁRIOS CADASTRADOS NO BANCO PARA PREENCHER NA TELA
	$users = User::listAll();

	$page = new PageAdmin();

	// PASSANDO O RESULTADO DA FUNÇÃO listAll PARA A view "users"
	$page->setTpl("users", array(
		"users"=>$users
	));

});

// ROUTE DA CRIAÇÃO DE USUÁRIOS
$app->get('/admin/users/create', function () {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});

// ROUTE PARA EXCLUIR UM USUÁRIO
$app->get("/admin/users/:iduser/delete", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});


// ROUTE DE EDITAR USUÁRIOS
$app->get("/admin/users/:iduser", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"	=>	$user->getValues()
	));

});

// ROUTE DE SALVAR A CRIAÇÃO DO USUÁRIO
$app->post("/admin/users/create", function () {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});

// ROUTE DE SALVAR A EDIÇÃO DO USUÁRIO
$app->post("/admin/users/:iduser", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

});







$app->run();

 ?>