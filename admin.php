<!-- ROUTES DE ADMIN -->
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;


// ROUTE INDEX ADMIN
$app->get('/admin', function() {
    
    // SÓ ACESSA SE ESTIVER LOGADO
    User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

// ROUTE TELA DE LOGIN
$app->get('/admin/login', function() {
    
    // PASSANDO AS OPÇÕES PARA O MÉTODO CONSTRUTOR, PARA DESARTIVARMOS O USO DO "header" e do "footer" POIS NESSE CASO ELES FICAM DIRETO NO ARQUIVO DA PÁG DE LOGIN, POIS SERÁ DIFERENTE DAS OUTRAS PÁGIANAS
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("login");

});

// ROUTE DE AUTENTICAÇÃO DO LOGIN
$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");

	exit;

});

// ROUTE DE LOGOUT
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;
});



// ROTAS DE ESQUECEU A SENHA
// ROUTE PARA DO FORM PARA REDEFINIR SENHA
$app->get("/admin/forgot", function () {

	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot");

});

// ROUTE RECEBE E-MAIL PARA RECUPERAR A SENHA VIA post DO FORM
$app->post("/admin/forgot", function () {

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});


// ROTA DA PÁGINA DE EMIAL DE RECUPERAÇÃO DE SENHA ENVIADO
$app->get("/admin/forgot/sent", function () {

	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot-sent");

});

// ROUTE DO LINK DE REDEFINIÇÃO DE SENHA ENVIADA POR EMAIL COM O PARAMETRO DO CÓDIGO ENVIADO
$app->get("/admin/forgot/reset", function () {

	//PEGANDO O CÓDIGO QUE ESTÁ NA URL DO LINK ENVIADO POR EMAIL E CARREGANDO DADOS DO USUÁRIO CORRESPONDENTE AO CÓDIGO
	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot-reset", array(
		"name"	=>	$user['desperson'],
		"code"	=>	$_GET['code']
	));

});

//ROUTE QUE REALIZA A TROCA DE SENHA, Q FOI PREENCHIDA NO FORM DE TROCAR A SENHA
$app->post("/admin/forgot/reset", function () {

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


	$page = new PageAdmin([
		"header"	=>	false,
		"footer"	=>	false
	]);

	$page->setTpl("forgot-reset-success");

});






?>