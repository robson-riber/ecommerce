<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";

	//tem que ter 16 caracteres
	const SECRET = "HcodePhp7_Secret";

	public static function getFromSession()
	{
		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser'] > 0 ){
			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}


	public static function checkLogin($inadmin = true)
	{
		// se não estive definida - criada 
		// OU se for false
		// e se o valor do campo iduser mão for maior que 0
		// e se o usuário é administrador
		if(
			!isset($_SESSION[User::SESSION]) 
		 	|| !$_SESSION[User::SESSION] 
			|| !(int)$_SESSION[User::SESSION]["iduser"] > 0 
		){
			//Não está logado
			return false;
		} else {

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true ){

				return true;
			
			} else if ($inadmin === false) {

				return true;

			} else {

				return false;
			}
		}
	}



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
		if (User::checkLogin($inadmin)){		
			header("Location: /admin/login");
			exit;
		}
	}


	public static function logout()
	{
		//limpa a variável SESSION
		$_SESSION[User::SESSION] = NULL;
	}


	public static function listAll()
	{
		$sql = new Sql();

		return	$sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson"); 
	}


	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array(
				":desperson"	=> $this->getdesperson(),
				":deslogin"		=> $this->getdeslogin(),
				":despassword"	=> $this->getdespassword(),
				":desemail"		=> $this->getdesemail(),
				":nrphone"		=> $this->getnrphone(),
				":inadmin"		=> $this->getinadmin()
		));

		$this->setData($results[0]);

	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser" , array(

			":iduser" => $iduser
		));

		$this->setData($results[0]);
	}


	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_update(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array(
				":iduser"		=> $this->getiduser(),
				":desperson"	=> $this->getdesperson(),
				":deslogin"		=> $this->getdeslogin(),
				":despassword"	=> $this->getdespassword(),
				":desemail"		=> $this->getdesemail(),
				":nrphone"		=> $this->getnrphone(),
				":inadmin"		=> $this->getinadmin()
		));

		$this->setData($results[0]);
	}


	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser" => $this->getiduser()
		));
	}


	public static function getForgot($email, $inadmin = true)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email", array(
			":email" => $email
		));

		//var_dump($results);
		//exit();

		if(count($results) === 0 ){
			throw new \Exception("Não foi possível recuperar a senha.");
		} else {

			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(

				":iduser" => $data["iduser"],
				":desip"  => $_SERVER['REMOTE_ADDR']
			));

			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar sua senha.");
				
			} else {

				$dataRecovery = $results2[0];

				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);

   			 	$result = base64_encode($iv.$code);

				//$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

	 			if ($inadmin === true) {
                 	
                 	$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";

             	} else {
                 	
                 	$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
             	} 
				
				$mailer = new Mailer(
									$data['desemail'], //endereço do e-mail
									$data['desperson'],  //nome do usuário
									"Redefina sua senha", //assunto
									"forgot", // no do template que está na pasta /VIEWS/EMAIL/
									array("name" => $data["desperson"], "link" => $link )); //DADOS(CAMPOS) PARA RENDERIZAR QUE ESTÃO NO E-MAIL

				$mailer->send();

				return $data;

			}
		}

	}


	public static function validForgotDecrypt($codert)
	{	
		//echo $codert;
	    //echo "//----";

		$result = base64_decode($codert);

		$code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');

	    $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;

	    $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);

	    //$idrecovery = 84;

	    //echo "idrecovery = \n";
	    //var_dump($idrecovery);
	    //exit;

		$sql = new SQL();

		$results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
								INNER JOIN tb_users b USING(iduser) 
								INNER JOIN tb_persons c USING(idperson)
								WHERE
								a.idrecovery = :idrecovery 
								AND a.dtrecovery IS NULL
								AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();" , array(":idrecovery" => $idrecovery));

		if (count($results) === 0 )
		{
			throw new \Exception("Não foi possível recuperar a senha - L208 - $idrecovery");
			
		} else {

			return $results[0];
		}
	}


	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(":idrecovery" => $idrecovery));	
	}


	public function setPassword($password)
	{
		$sql = new Sql();

		//echo "senha: \n";
		//var_dump($password);
		//echo "====================\n"; 

		//echo "id user: \n";
		//var_dump($this->getiduser());
		//echo "====================\n"; 
		//exit();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(":password" => $password, ":iduser" => $this->getiduser() ));
	}



}

?>