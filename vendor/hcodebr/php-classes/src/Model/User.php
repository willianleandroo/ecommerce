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
			":desperson"	=>	$this->getdesperson(),
			":deslogin"		=>	$this->getdeslogin(),
			":despassword"	=>	$this->getdespassword(),
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

		$this->setData($results[0]);
	}

	// MÉTODO DE ATUALIZAR USUÁRIO
	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"		=>	$this->getiduser(),
			":desperson"	=>	$this->getdesperson(),
			":deslogin"		=>	$this->getdeslogin(),
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


}








?>