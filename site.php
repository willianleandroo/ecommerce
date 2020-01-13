<!-- ROUTES DO SITE -->
<?php

use \Hcode\Page;

// ROUTE INDEX SITE
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});










?>