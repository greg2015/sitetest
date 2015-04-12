<?php
require 'src/Request.php'; //Librairie rootage et redirection
require 'src/Database.php'; //Gestion de base de données
require 'src/Security.php'; //Librairie de sécurité
require 'src/Session.php'; //Gestion de session

/* Chargement automatique des classes */
function __autoload($_class) {
    if(file_exists('_model/'.$_class.'.php')) require_once('_model/'.$_class.'.php');    
    else if(file_exists('_control/'.$_class.'.php')) require_once('_control/'.$_class.'.php'); 
    else if(file_exists('_view/'.$_class.'.php')) require_once('_view/'.$_class.'.php'); 
    else exit(); 
}
/* récupération des paramètres d'url */
$url = Security::give_form_get(['ctrl' => false, 'name' => false, 'action' => false]);

/* vérification des paramètres d'url */
if($url['ctrl'] == 'logout')
{
	Session::logout(); //deconnexion
	$url['ctrl'] = NULL;
}
/** ROOTING **/
if(Session::check()) //si identifié
{
	
	if(isset($_GET['search'])) 
	{
		if (Security::check_string($_GET['search'],['simple' => true,'min' => 2, 'max' => 100]))
		Request::redirect('/user/'.$_GET['search']);
	}
	
	if($url['ctrl'] == 'user') //action sur controlleur utilisateur
	{
		$ctrl = new UserController($url['name']);

		
		if(is_null($url['action'])) $ctrl->wall();
		else if(method_exists($ctrl,$url['action'])) $ctrl->$url['action']();
		else Request::redirect();
	}
	else //action sur controlleurpublications
	{
		$ctrl = new PostController($url['name']);
		if(is_null($url['action'])) $ctrl->index();
		else if(method_exists($ctrl,$url['action'])) $ctrl->$url['action']();
		else Request::redirect();
	}
}
else //si pas identifié
{
	$ctrl = new IdentificationController();
	if(method_exists($ctrl,$url['ctrl'])) $ctrl->$url['ctrl']();
	else if($url['ctrl'] == NULL) $ctrl->signin();
	else Request::redirect();
}
?>