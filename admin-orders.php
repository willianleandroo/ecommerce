<?php
	
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

// ROUTE DA VIEW DE EDITAR O STATUS
$app->get("/admin/orders/:idorder/status", function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'			=>	$order->getValues(),
		'status'		=>	OrderStatus::listAll(),
		'msgError'		=>	Order::getError(),
		'msgSuccess'	=>	Order::getSuccess()
	]);
});

// ROUTE DE ALTERAR O STATUS DO PEDIDO
$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {

		Order::setError("Favor informar um status válidio.");
		header("Location: /admin/orders/" .$idorder. "/status");
		exit;

	}
	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("O status foi atualizado com sucesso.");
	header("Location: /admin/orders/" .$idorder. "/status");
	exit;

});


// ROUTE PARA EXCLUIR UM PEDIDO
$app->get("/admin/orders/:idorder/delete", function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /admin/orders");
	exit;

});

// ROUTE PARA VIEW DE DETALHES DO PEDIDO
$app->get("/admin/orders/:idorder", function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);
	
	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'		=>	$order->getValues(),
		'products'	=>	$cart->getProducts()

	]);

});


// ROUTE PARA ACESSAR A VIEW
$app->get("/admin/orders", function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : '';
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {
		
		$pagination = Order::getPageSearch($search, $page, 2);

	} else {

		// TRAZENDO TODOS OS USUÁRIOS CADASTRADOS NO BANCO PARA PREENCHER NA TELA
		$pagination = Order::getPage($page, 2);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'	=>	'/admin/orders?'.http_build_query([
				'page'		=>	$x + 1,
				'search'	=>	$search
			]),
			'text'	=>	$x + 1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("orders", [
		'orders'	=>	$pagination['data'],
		'search'	=>	$search,
		'pages'		=>	$pages
	]);

});



















?>