<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin', function() {
    
    //User::verifyLogin();

    $page = new PageAdmin();
	$page->setTpl("index"); //arquivo html

});


$app->get('/admin/login', function() {
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	
	$page->setTpl("login"); //arquivo html
});


$app->post('/admin/login', function(){

	// metodo statico
	User::login($_POST['login'], $_POST['password']);

	header("Location: /admin");
	exit;
});


$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});


$app->get('/admin/forgot', function() {
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	
	$page->setTpl("forgot"); //arquivo html
});


$app->post('/admin/forgot', function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	
	$page->setTpl("forgot-sent"); //arquivo html

});


$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	
	// carregando a página forgot-reset e passando os campos que estão nele
	$page->setTpl("forgot-reset", array("name"=> $user["desperson"], "code" => $_GET["code"])); //arquivo html

});


$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	//echo "variável user \n";
	//var_dump($user);
	//echo "------------------------------";

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT,["cost" => 12]);

	//echo "senha com Hash: \n";
	//var_dump($password);
	//exit();

	$user->setPassword($password);

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	
	$page->setTpl("forgot-reset-success");

});





?>