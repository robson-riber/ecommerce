<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;


$app->get('/', function() {

	$products = Product::ListAll();


	$page = new Page();
	$page->setTpl("index", ["products" => Product::checklist($products) ]); //arquivo html
});

$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	$category = new Category();

	$category->get((int) $idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) {
		array_push($pages, [
			'link' => '/categories/' . $category->getidcategory() . '?page=' .$i,
			'page' => $i	 	
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category' 	=> $category->getValues(),
		'products' 	=> $pagination['data'],
		'pages'		=> $pages
	]);
});


$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product' => $product->getValues(), 
		'categories' => $product->getcategories()]);
});


$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	//echo ("aqui 1");
	//exit;

	$page = new Page();

	$page->setTpl("cart");

	/*$page->setTpl("cart", [
		'cart' => $cart->getValues(),
		'products' => $cart->getProducts()
	]);*/

});


$app->get("/cart/:idproduct/add", function($idproduct){

	//echo("arg1 - linha 82 site.php");
	//echo ($idproduct);
	//exit;

	$product = new Product();

	$product->get((int) $idproduct);

	//echo "dados do produto\n";
	//var_dump($product);

	$cart = Cart::getFromSession();

	//$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	//for ($i = 0; $i < $qtd; $i++)
	//{
	//	$cart->addProduct($product);
	//}

	//echo "dados daaaaa getFromSession";
	//echo "======\n";

	//var_dump($cart);
	
	//exit;

	$cart->addProduct($product);

	header("Location: /cart");
	exit;
});


$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int) $idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});


$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int) $idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});



?>