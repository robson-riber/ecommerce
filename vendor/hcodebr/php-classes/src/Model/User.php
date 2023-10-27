<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

	const SESSION = "User";

	public static function login($login, $password)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN" =>	$login));

		if (count($results) === 0)
		{
			#usa \ para encontrar a Exception principal
			throw new \Exception("usuário não encontrado ou senha inválida!");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			#usa \ para encontrar a Exception principal
			throw new \Exception("usuário não encontrado ou senha inválida!");
		}
	}


	public static function verifyLogin($inadmin = true)
	{
		// se não estive definida - criada 
		// OU se for false
		// e se o valor do campo iduser mão for maior que 0
		// e se o usuário é administrador
		if ( !isset($_SESSION[User::SESSION]) 
			 || !$_SESSION[User::SESSION] 
			 || !(int)$_SESSION[User::SESSION]["iduser"] > 0 
			 || (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin 
			) 
		{ 
			header("Location: /admin/login");
			exitr;
		}
	}


	public static function logout()
	{
		//limpa a variável SESSION
		$_SESSION[User::SESSION] = NULL;
	}



}

?>