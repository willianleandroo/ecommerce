<?php 
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;



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


// ROUTE PARA DO FORM PARA REDEFINIR SENHA
$app->get("/admin/forgot", function () {

	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot");

});

// ROUTE RECEBE E-MAIL PARA RECUPERAR A SENHA VIA post DO FORM
$app->post("/admin/forgot", function () {

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});


// ROTA DA PÁGINA DE EMIAL DE RECUPERAÇÃO DE SENHA ENVIADO
$app->get("/admin/forgot/sent", function () {

	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot-sent");

});

// ROUTE DO LINK DE REDEFINIÇÃO DE SENHA ENVIADA POR EMAIL COM O PARAMETRO DO CÓDIGO ENVIADO
$app->get("/admin/forgot/reset", function () {

	//PEGANDO O CÓDIGO QUE ESTÁ NA URL DO LINK ENVIADO POR EMAIL E CARREGANDO DADOS DO USUÁRIO CORRESPONDENTE AO CÓDIGO
	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot-reset", array(
		"name"	=>	$user['desperson'],
		"code"	=>	$_GET['code']
	));

});

//ROUTE QUE REALIZA A TROCA DE SENHA, Q FOI PREENCHIDA NO FORM DE TROCAR A SENHA
$app->post("/admin/forgot/reset", function () {

	//PEGANDO O CÓDIGO QUE ESTÁ NO FORM DE REDEFINIR SENHA VIA POST E HIDDEN E CARREGANDO DADOS DO USUÁRIO CORRESPONDENTE AO CÓDIGO
	$forgotData = User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgotData['idrecovery']);

	//CARREGANDO oOS DADOS DO USUÁRIO
	$user = new User();

	$user->get((int)$forgotData['iduser']);

	// CRIPTROGRAFANDO A SENHA
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT, [
		"cost"	=>	12
	]);

	// PASSANDO A SENHA RECEBIDA NO FORM POR PARAMETRO PARA A FUNÇÃO DE SETAR PASSWORD
	$user->setPassword($password);


	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot-reset-success");

});

// ROUTE PARA ACESSAR O TPL DE CATEGORIAS
$app->get("/admin/categories/", function() {

	User::verifyLogin();
	
	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"	=>	$categories
	]);

});

// ROUTE DO FORM PARA CADASTRAR CATEGORIAS
$app->get("/admin/categories/create", function() {

	User::verifyLogin();
	
	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

// ROUTE PARA CADASTRAR A CATEGORIA, VIA POST
$app->post("/admin/categories/create", function() {
	
	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

	header("Location: /admin/categories/");
	exit;
});

// ROUTE PARA EXCLUIR UMA CATEGORIA
$app->get("/admin/categories/:idcategory/delete", function ($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories/");
	exit;
});

// ROUTE PARA EXIBIR A VIEW COM O FORM DE EDITAR A CATEGORIA
$app->get("/admin/categories/:idcategory", function ($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	
	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'	=>	$category->getValues()
	]);

});

// ROUTE PARA EDITAR UMA CATEGORIA
$app->post("/admin/categories/:idcategory", function ($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories/");
	exit;
});

// ROUTE PARA FILTRO DE CATEGORIA
$app->get("/categories/:idcategory", function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"	=>	$category->getValues(),
		"products"	=>	[]
	]);

});



$app->run();

 ?>