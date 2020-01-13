<!-- ROTAS DE ADMIN-USER -->
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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





?>