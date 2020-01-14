<!-- ROUTES DO SITE -->
<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

// ROUTE INDEX SITE
$app->get('/', function() {
    
	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'	=>	Product::checkList($products)
	]);

});

// ROUTE PARA FILTRO DE CATEGORIA
$app->get("/categories/:idcategory", function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"	=>	$category->getValues(),
		"products"	=>	Product::checkList($category->getProducts())
	]);

});












?>