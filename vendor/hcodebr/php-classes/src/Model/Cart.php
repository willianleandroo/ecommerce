<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;



class Cart extends Model{

	//NOME DA SESSÃO PARA GUARDAR O ID DO CARRINHO NA SESSÃO
	const SESSION = "Cart";

	public function getFromSession()
	{

		$cart = new Cart();


		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
			
			if ($_SESSION['User']['iduser'] != $_SESSION[Cart::SESSION]['iduser']){

			
				$cart->getFromUserID($_SESSION['User']['iduser']);
				// $this->setData($_SESSION['User']);
				// $t = $this->getiduser();
				// echo $t;
				// exit;
			}else {
				$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			}
			

		}else {

			$cart->getFromSessionID();

			if((int)$cart->getidcart() > 0) {

				$data = [
					'dessessionid'	=>	session_id()
				];

				if (User::checkLogin(false)) {

					$user = User::getFromSession();
					
					$data['iduser'] = $user->getiduser();
				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

				
			}

		}

		return $cart;
	}

	public function getFromUserID(int $iduser)
	{


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'	=>	session_id()
		]);

		//PEGA A ÚLTIMA POSIÇÃO DO ARRAY
		$endResult = end($results);
		

		if(count($results) > 0 && $endResult['iduser'] != $iduser && $endResult['iduser'] != NULL){
		
			$this->setData($results[0]);

			$this->setData($_SESSION['User']);

			$sql->query("
				INSERT INTO tb_carts 
				(dessessionid, iduser, deszipcode, vlfreight, nrdays)
        		VALUES
        		(:dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
        			':dessessionid'	=>	$this->getdessessionid(),
					':iduser'		=>	$this->getiduser(),
					':deszipcode'	=>	$this->getdeszipcode(),
					':vlfreight'	=>	$this->getvlfreight(),
					':nrdays'		=>	$this->getnrdays()
        		]);

		
		}else if(count($results) > 0 && $endResult['iduser'] == NULL) {

			$this->setData($results[0]);

			$this->setData($_SESSION['User']);

			$this->save();
		}

	}

	public function setToSession()
	{

		$_SESSION[Cart::SESSION] = $this->getValues();
		
	}

	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'	=>	$idcart
		]);

		if(count($results) > 0){

			$this->setData($results[0]);
		}

	}


	public function getFromSessionID()
	{


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'	=>	session_id()
		]);
		
		if(count($results) > 0){

			$this->setData($results[0]);
		}

	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'		=>	$this->getidcart(),
			':dessessionid'	=>	$this->getdessessionid(),
			':iduser'		=>	$this->getiduser(),
			':deszipcode'	=>	$this->getdeszipcode(),
			':vlfreight'	=>	$this->getvlfreight(),
			':nrdays'		=>	$this->getnrdays()
		]);

		$this->setData($results[0]);

	}

	
}








?>