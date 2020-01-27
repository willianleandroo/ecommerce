<!-- ROUTES DO SITE -->
<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;



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

	if (isset($_GET['zipcode'])){

		$_GET['zipcode'] = $cart->getdeszipcode();
	}

	if (isset($_GET['zipcode'])) {

		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();

		$cart->getCalculateTotal();
	}

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'		=>	$cart->getValues(),
		'address'	=>	$address->getValues(),
		'products'	=>	$cart->getProducts(),
		'error'		=>	Address::getMsgError()
	]);

});

//ROUTE DE CHECKOUT/COMPRAR/FINALIZAR O PEDIDO COM OS CAMPOS DEFINIDOS PELO USUÁRIO
$app->post("/checkout", function() {

	User::verifyLogin(false);

	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){

		Address::setMsgError("Informe o CEP.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){

		Address::setMsgError("Informe o endereço.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){

		Address::setMsgError("Informe o bairro.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descity']) || $_POST['descity'] === ''){

		Address::setMsgError("Informe a Cidade.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === ''){

		Address::setMsgError("Informe o estado.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descountry']) || $_POST['descountry'] === ''){

		Address::setMsgError("Informe o País.");
		header("Location: /checkout");
		exit;
	}

	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		'idcart'	=>	$cart->getidcart(),
		'idaddress'	=>	$address->getidaddress(),
		'iduser'	=>	$user->getiduser(),
		'idstatus'	=>	OrderStatus::EM_ABERTO,
		'vltotal'	=>	$cart->getvltotal()
	]);

	$order->save();

	header("Location: /order/".$order->getidorder());
	exit;

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


// ROUTES DO "MEU PERFIL"
$app->get("/profile", function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'			=>	$user->getValues(),
		'profileMsg'	=>	User::getSuccess(),
		'profileError'	=>	User::getError()
	]);

});

// ROUTE DE EDITAR DADOS DO USUÁRIO
$app->post("/profile", function() {


	User::verifyLogin(false);



	if (!isset($_POST['desperson']) || $_POST['desperson'] === '')  {

		User::setError("Preencha o seu Nome.");
		header("Location: /profile");
		exit;
	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '')  {

		User::setError("Preencha o seu Email.");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] != $user->getdesemail()) {

		if (User::checkLoginExist($_POST['desemail'])) {

			User::setError("Este email já está em uso por outro usuário");
			header("Location: /profile");
			exit;
		}
	}
	

	
	// MANTENDO O ACESSO DE NÃO ADMIN, CASO O USUÁRIO DESCUBRA ESSE PARAMETRO E TENTE ALTERÁ-LO
	$_POST['inadmin'] = $user->getinadmin();

	// MANTENDO A SENHA JÁ USADADA
	$_POST['despassword'] = $user->getdespassword();

	// MANTENDO O EMAIL JÁ UTILZIADO
	$_POST['desemail'] = $user->getdesemail();

	$user->setData($_POST);

	$user->update();

	User::setSuccess("Dados alterados com sucesso");

	header("Location: /profile");
	exit;

});


// ROUTE DA VIEW DE FINALIZAR PEDIDO
$app->get("/order/:idorder", function($idorder) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'	=>	$order->getValues()
	]);

});

//ROUTE DE PAGAMENTO DO BOLOTE
$app->get("/boleto/:idorder", function($idorder) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);

	$valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " - " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdescity(). " - " . $order->getdesstate() . " - " . $order->getdescountry() . " - " . "CEP: " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");

});


// ROUTE DA VIEW DO PEDIDOS POR USER
$app->get("/profile/orders", function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'	=>	$user->getOrder()
	]);

});

// ROUTE DE VIEW DOS DETALHES DO PEDIDO
$app->get("/profile/orders/:idorder", function($idorder) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();
	$cart->get((int)$order->getidcart());
	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'order'		=>	$order->getValues(),
		'cart'		=>	$cart->getValues(),
		'products'	=>	$cart->getProducts()
	]);

});




?>