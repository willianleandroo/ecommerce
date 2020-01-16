<!-- ROUTES DO SITE -->
<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

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

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$page = new Page();

	$pages = [];
	for ($i=1; $i <= $pagination['pages'] ; $i++) { 
		
		array_push($pages, [
			'link'	=>	'/categories/'.$category->getidcategory().'?page='.$i,
			'page'	=>	$i
		]);
	}

	$page->setTpl("category", [
		"category"	=>	$category->getValues(),
		"products"	=>	$pagination["data"],
		"pages"		=>	$pages
	]);

});


// ROUTE PARA ACESSAR DETALHES DO PRODUTO
$app->get("/products/:desurl", function($desurl) {

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'		=>	$product->getValues(),
		'categories'	=>	$product->getCategories()
	]);	

});

//ROUTE PARA ACESSAR CARRINHO DE COMPRAS
$app->get("/cart", function() {

	$cart = new Cart();

	$cart->getFromSession();

	$page = new Page();

	$page->setTpl("cart");

});













?>