<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;



class User extends Model{

	const SESSION = "User";

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
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {
			header("Location: /admin/login");
			exit;
		}
	}


	// LOGOUT
	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}


}








?>