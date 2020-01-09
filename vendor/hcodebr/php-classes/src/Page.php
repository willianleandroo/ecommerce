<?php


// NAMESPACE
namespace Hcode;

// DECLARANDO O USO DA CLASS DO RainTPL ATRAVÉS DO NAMESPACE DELA
use Rain\Tpl;


class Page
{
	public $tpl;
	public $options = [];
	public $defaults = [
				"header" => true,
				"footer" => true,
				"data" 	 =>	[]
	];

	public function __construct($opts = array(), $tpl_dir = "/views/")
	{

		// $this->defaults["data"]["session"] = $_SESSION;

		$this->options = array_merge($this->defaults, $opts);

		$config = array(
					"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
					"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
					"debug"         => false // set to false to improve the speed
				   );

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		// REALIZANDO O IF PARA VER SE A PESSOA DESABILITOU O "header" OU NÃO
		if ($this->options["header"] === true) {

			$this->tpl->draw("header");
		}
		

	}



	private function setData($data = array())
	{
		foreach ($data as $key => $value)
		{

			$this->tpl->assign($key, $value);

		}

	}



	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		$this->setData($data);


		return $this->tpl->draw($name, $returnHTML);
	}



	public function __destruct()
	{
		// REALIZANDO O IF PARA VER SE A PESSOA DESABILITOU O "footer" OU NÃO
		if ($this->options["footer"] === true) {

			$this->tpl->draw("footer");
		}
		
	}



}





?>