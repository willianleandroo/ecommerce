<!-- ROTAS DOS PRODUCTS-ADMIN -->
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

// ROUTE PARA VIEW QUE LISTA OS PRODUTOS
$app->get("/admin/products", function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : '';
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {
		
		$pagination = Product::getPageSearch($search, $page, 2);

	} else {

		// TRAZENDO TODOS OS USUÁRIOS CADASTRADOS NO BANCO PARA PREENCHER NA TELA
		$pagination = Product::getPage($page, 2);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'	=>	'/admin/products?'.http_build_query([
				'page'		=>	$x + 1,
				'search'	=>	$search
			]),
			'text'	=>	$x + 1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"	=>	$pagination['data'],
		"search"		=>	$search,
		"pages"			=>	$pages
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