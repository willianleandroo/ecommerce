<!-- ROTAS DAS CATEGORIAS -->
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


// ROUTE PARA ACESSAR O TPL DE CATEGORIAS
$app->get("/admin/categories", function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : '';
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {
		
		$pagination = Category::getPageSearch($search, $page, 2);

	} else {

		// TRAZENDO TODOS OS USUÁRIOS CADASTRADOS NO BANCO PARA PREENCHER NA TELA
		$pagination = Category::getPage($page, 2);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'	=>	'/admin/categories?'.http_build_query([
				'page'		=>	$x + 1,
				'search'	=>	$search
			]),
			'text'	=>	$x + 1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"	=>	$pagination['data'],
		"search"		=>	$search,
		"pages"			=>	$pages
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


// ROUTE PARA ACESSAR VIEW DE RELACIONAMENTO PRODUTOS/CATEGORIAS
$app->get("/admin/categories/:idcategory/products", function ($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		"category"				=>	$category->getValues(),
		"productsRelated"		=>	$category->getProducts(),
		"productsNotRelated"	=>	$category->getProducts(false)
	]);

});

// ROUTE PARA ADICIONAR PRODUTO NA CATEGORIA
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function ($idcategory, $idproduct) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

// ROUTE PARA REMOVER PRODUTO NA CATEGORIA
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function ($idcategory, $idproduct) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});




?>