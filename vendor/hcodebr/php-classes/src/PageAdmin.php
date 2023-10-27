<?php 

namespace Hcode;

class PageAdmin extends Page{

	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		//PARENT:: Chama o método construct da Page.php
		parent::__construct($opts, $tpl_dir);
	}

}

?>