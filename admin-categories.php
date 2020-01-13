<!-- ROTAS DAS CATEGORIAS -->
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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






?>