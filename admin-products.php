<!-- ROTAS DOS PRODUCTS-ADMIN -->
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

// ROUTE PARA VIEW QUE LISTA OS PRODUTOS
$app->get("/admin/products", function() {

	User::verifyLogin();

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"	=>	$products
	]);
});

// ROUTE PARA VIEW DE CADASTRAR PRODUTOS
$app->get("/admin/products/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");
});

// ROUTE QUE RECEBE OS DADOS DO FORM DE CADASTRAR PRODUTOS
$app->post("/admin/products/create", function() {

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");
	exit;

});

// ROUTE PARA A VIEW DE EDITAR PRODUTO
$app->get("/admin/products/:idproduct", function($idproduct) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", [
		'product'	=>	$product->getValues()
	]);

});

// ROUTE PARA SALVAR A EDIÇÃO DO PRODUTO
$app->post("/admin/products/:idproduct", function($idproduct) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;

});

//ROUTE PARA EXCLUIR PRODUTO
$app->get("/admin/products/:idproduct/delete", function($idproduct) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /admin/products");
	exit;

});


?>