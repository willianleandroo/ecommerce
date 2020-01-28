<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;

function formatPrice($vlprice)
{

	if (!$vlprice > 0) $vlprice = 0;
	
	return number_format($vlprice, 2, ",", ".");

}

function formatDate($date)
{

	return date('d/m/Y',strtotime($date));

}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

}

//FUNÇÕES PARA EXIBIR QTD DE PRODUTOS NO CARRINHO RESUMIDO Q FICA NO CANTO SUPERIOR DIREITO DA TELA
function getCartNrQtd()
{

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];

}

//FUNÇÃO PARA EXIBIR O VALOR TOTAL DOS PRODUTOS NO CARRINHO -  O VALOR DO FRETE
function getCartVlSubtotal()
{

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatPrice($totals['vlprice']);

}



?>