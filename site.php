<!-- ROUTES DO SITE -->
<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'		=>	$cart->getValues(),
		'products'	=>	$cart->getProducts(),
		'error'		=>	Cart::getMsgError()
	]);

});

// ROUTE ADD PRODUTO NO CARRINHO
$app->get("/cart/:idproduct/add", function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 

		$cart->addProduct($product);

	}

	
	header("Location: /cart");
	exit;

});


// ROUTE DE REMOVER UMA QUANTIDADE DE UM PRODUTO
$app->get("/cart/:idproduct/minus", function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
	
});

// ROUTE DE REMOVER UM PRODUTO INDEPENDENTE DA QUANTIDADE
$app->get("/cart/:idproduct/remove", function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
	
});

// ROUTE QUE RECEBE O CEP PARA CALCULAR O FRETE
$app->post("/cart/freight", function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});

// ROUTE DE CHECKOUT, SE A PESSOA JÁ ESTIVER LOGADA
$app->get("/checkout", function() {

	// VERIFICANDO ROTA DA ADMINISTRAÇÃO OU NÃO PASSANDO O false POR PARAMETRO
	User::verifyLogin(false);

	$cart = Cart::getFromSession();
	
	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'		=>	$cart->getValues(),
		'address'	=>	$address->getValues()
	]);

});

// ROUTE DA VIEW DE LOGIN DO USUÁRIO/CLIENTE
$app->get("/login", function() {

	$page = new Page();

	$page->setTpl("login", [
		'error'				=>	User::getError(),
		'errorRegister'		=>	User::getErrorRegister(),
		'registerValues'	=>	(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);


});

// ROUTE QUE RECEBE OS DADOS DE LOGIN DE USUÁRIO/CLIENTE
$app->post("/login", function() {

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch (Exception $e) {
		
		User::setError($e->getMessage());
	}

	header("Location: /checkout");
	exit;

});

// ROUTE PARA DESLOGAR
$app->get("/logout", function() {

	User::logout();

	header("Location: /login");
	exit;
});

// ROUTE PARA RECEBER OS DADOS DO FORM DE CADASTRO
$app->post("/register", function() {

	// GUARDANDO OS DADOS Q FORAM DIGITADOS NO FORM DE CADASTRO EM UMA SESSÃO, PARA QUE NÃO SE PERCAM CASO ALGUMA INFORMAÇÃO DO FORM NÃO ESTIVER CORRETA E ASSIM NÃO LIMPAR O CAMPO QUANDO RETORNAR À PÁG DE CADASTRO/LOGIN
	$_SESSION['registerValues'] = $_POST;

	// VALIDAÇÕES DOS DADOS DO FORM
	if (!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha o seu Email.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}

	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Este endereço de email já está sendo utilizado, faça o login.");
		header("Location: /login");
		exit;

	}

	$_SESSION['registerValues'] = NULL;

	$user = new User();

	$user->setData([
		'inadmin'		=>	0,
		'deslogin'		=>	$_POST['email'],
		'desperson'		=>	$_POST['name'],
		'desemail'		=>	$_POST['email'],
		'despassword'	=>	$_POST['password'],
		'nrphone'		=>	$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;
});


// ROTAS DE ESQUECEU A SENHA
// ROUTE PARA DO FORM PARA REDEFINIR SENHA
$app->get("/forgot", function () {

	$page = new Page();

	$page->setTpl("forgot");

});

// ROUTE RECEBE E-MAIL PARA RECUPERAR A SENHA VIA post DO FORM
$app->post("/forgot", function () {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});


// ROTA DA PÁGINA DE EMIAL DE RECUPERAÇÃO DE SENHA ENVIADO
$app->get("/forgot/sent", function () {

	$page = new Page();

	$page->setTpl("forgot-sent");

});

// ROUTE DO LINK DE REDEFINIÇÃO DE SENHA ENVIADA POR EMAIL COM O PARAMETRO DO CÓDIGO ENVIADO
$app->get("/forgot/reset", function () {

	//PEGANDO O CÓDIGO QUE ESTÁ NA URL DO LINK ENVIADO POR EMAIL E CARREGANDO DADOS DO USUÁRIO CORRESPONDENTE AO CÓDIGO
	$user = User::validForgotDecrypt($_GET['code']);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"	=>	$user['desperson'],
		"code"	=>	$_GET['code']
	));

});

//ROUTE QUE REALIZA A TROCA DE SENHA, Q FOI PREENCHIDA NO FORM DE TROCAR A SENHA
$app->post("/forgot/reset", function () {

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


	$page = new Page();

	$page->setTpl("forgot-reset-success");

});
















?>