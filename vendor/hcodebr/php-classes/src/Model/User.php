<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;



class User extends Model{

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";

	// ERROS
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";

	//SUCESS
	const SUCCESS = "UserSuccess";

	public static function getFromSession()
	{
		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);
			$user->get($_SESSION[User::SESSION]['iduser']);
		
		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//NÃO ESTÁ LOGADO
			return false;

		} else {

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				//É UM ADMIN E ESTÁ LOGADO
				return true;

			} else if ($inadmin === false) {

				return true;

			} else {

				return false;
			}


		}
	}


	public static function login ($login, $password)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN" => $login
		));


		if (count($results) === 0)
		{	

			// CONTRA BARRA POIS AS EXCEÇÕES FICAM NA RAIZ DO PROJETO DO PHP E NÃO CRIAMOS A NOSSA NESSE DIR
			throw new \Exception("Usuário inexistente ou sena inválida.", 1);
		}

		// ATRIBUINDO O PRIMEIRO USUÁRIO ECONTRADO NA QUERY RESULTS
		$data = $results[0];

		// VERIFICANDO A SENHA DIGITADA PELO USUÁRIO E VALIDANDO COM CRIPTOGRAFIA/HASH COM A UNÇÃO password_verify
		if (password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();
			
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
			
		} else {
			// CONTRA BARRA POIS AS EXCEÇÕES FICAM NA RAIZ DO PROJETO DO PHP E NÃO CRIAMOS A NOSSA NESSE DIR
			throw new \Exception("Usuário inexistente ou sena inválida.", 1);
		}


	}


	// MÉTODO Q VERIFICA SE ESTÁ LOGADO OU NÃO
	public static function verifyLogin($inadmin = true)
	{
		if (!User::checkLogin($inadmin)) {
			
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		}
	}


	// LOGOUT
	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}


	// MÉTODO PARA LISTAR TODOS OS USUÁRIOS DO DB
	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}


	// SALVAR DADOS NO BANCO/CRIAR USER
	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"	=>	utf8_decode($this->getdesperson()),
			":deslogin"		=>	$this->getdeslogin(),
			":despassword"	=>	User::getPasswordHash($this->getdespassword()),
			":desemail"		=>	$this->getdesemail(),
			":nrphone"		=>	$this->getnrphone(),
			":inadmin"		=>	$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	// BUSCA DADOS DO USUÁRIO NO BANCO COM O ID PASSADO POR PARAMETRO
	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"	=>	$iduser
		));

		$data = $results[0];

		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data);
	}

	// MÉTODO DE ATUALIZAR USUÁRIO
	public function update()
	{
		$sql = new Sql();


		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"		=>	$this->getiduser(),
			":desperson"	=>	utf8_decode($this->getdesperson()),
			":deslogin"		=>	$this->getdeslogin(),
			// ":despassword"	=>	User::getPasswordHash($this->getdespassword()),
			":despassword"	=>	$this->getdespassword(),
			":desemail"		=>	$this->getdesemail(),
			":nrphone"		=>	$this->getnrphone(),
			":inadmin"		=>	$this->getinadmin()
		));

		$this->setData($results[0]);

	}


	// DELETAR USER
	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"	=>	$this->getiduser()
		));
	}


	// REDIFNIR SENHA RECEBENDO O E-MAIL DO FROM
	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :EMAIL ", array(
				":EMAIL" => $email
			));


		if(count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha");
			
		}
		else
		{
			$data = $results[0];
		

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"	=>	$data['iduser'],
				":desip"	=>	$_SERVER['REMOTE_ADDR']
			));

			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha");
			}
			else
			{

				$dataRecovery = $results2[0];

				// GERANDO UM CÓDIGO CRIPTOGRAFADO QUE SERÁ ENVIADO EM UM LINK POR EMAIL COM openssl_encrypt
				//openssl_encrypt(data, method, password)
				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				// LINK QUE SERÁ ENVIADO POR EMAIL
				if ($inadmin === true) {
					$link = "http://www.willecommerce.com.br/admin/forgot/reset?code=$code";
				} else {
					$link = "http://www.willecommerce.com.br/forgot/reset?code=$code";
				}
						

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Will Ecommerce", "forgot", array(
					"name"	=> $data['desperson'],
					"link"	=>	$link
				));

				$mailer->send();

				
				return $link;



			}


		}


	}


	public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a 
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", 
			array(
				":idrecovery"	=>	$idrecovery
			));

		if(count($results) === 0)
		{
			throw new Exception("Não foi possível recuperar a senha");
			
		}
		else
		{

			return $results[0];
		}


	}

	// MÉTODO QUE SETA DATA Q O CÓDIGO DE REDEFINIR SENHA FOI USADO 
	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"	=>	$idrecovery
		));

	}


	//FUNÇÃO QUE ATUALIZA A SENHA DO USUÁRIO
	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"	=>	$password,
			":iduser"	=>	$this->getiduser()
		));

	}

	// ERROS -- ERROS -- ERROS -- ERROS -- ERROS -- ERROS -- ERROS -- ERROS -- ERROS -- 
	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR] ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;
	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;
	}

	
	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER] ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;
	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;
	}

	//VERIFICA SE JÁ TEM UM USUÁRIO COM O MESMO EMAIL CADASTRADO NO BANCO
	public static function checkLoginExist($login)
	{
		
		$sql = new Sql();

		$results = $sql->select("SELECT deslogin FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'	=> $login
		]);

		// IF resumido
		return (count($results) > 0);

	}

	// CRIPTOGRAFIA DA SENHA AO CRIAR USUÁRIO
	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'	=>	12
		]);

	}

	// MENSAGENS NÃO NECESSARIAMENTE DE ERROS
	public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess()
	{

		$msg = isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS] ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;
	}

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;
	}

	public function getOrder()
	{
		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			':iduser'	=>	$this->getiduser()
		]);

		return $results;
	}



}








?>