<?php

namespace Hcode;


class Model {


	private $values = [];

	// MÉTODO MÁGICO QUE É EXECUTADO TODA VEZ QUE ALGUM MÉTODO FOR CHAMADO
	public function __call($name, $args)
	{
		// VERIFICANDO SE É O MÉTODO CHAMADO É UM GET OU
		$method = substr($name, 0, 3);

		// PEGANDO O NOME DA FUNÇÃO DEPOIS DE "get " OU "set"
		$fieldName = substr($name, 3, strlen($name));

		// USANDO SWITCH PARA CASO A FUNÇÃO SEJA get OU set
		switch ($method) 
		{
			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL ;
			break;
			
			case "set":
				$this->values[$fieldName] = $args[0];
			break;
		}


	}


	// MÉTODO Q CRIA SETTERS E ATRIBUI OS VALORES
	public function setData($data = array())
	{

		foreach ($data as $key => $value) {
			// CRIANDO FUNÇÃO DINAMICAMENTE, TEMOS QUE COLOCAR ENTRE CHAVES PARA CONCATENAR E O PHP ENTENDER COMO UM MÉTODO
			 $this->{"set".$key}($value);
		}
	}

	public function getValues()
	{
		return $this->values;
	}



}







?>