<?php
/* Paramètres de sécuité de session PHP */
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', false);

/* Début de la session */
session_start();


/**
 * Classe Session
 *
 * Permet de gerer et vérifier la session d'un client
 */
class Session
{	
    /**
     * check : 
     */
	public static function check()
	{
		Session::check_session();
		return Session::check_auth();
    }
	
    /**
     * get_info : retourne l'objet User stocké en session du client actuel
     */
	public static function get_info()
	{
		if(isset($_SESSION['user'])) return unserialize($_SESSION['user']);
		return false;
	}
	
    /**
     * secure_session : creer un nouveau jeton pour le chargement d'une nouvelle page et fixe le temps de session
     */
	private static function secure_session()
	{
		/*
		$cookie_name = 'facekikou_'.session_id().':'.$_SESSION['s_nb_token'];
		$_SESSION['s_token'] = sha1(mt_rand() + uniqid('',true)); //création d'un nouveau token unique
		setcookie($cookie_name, NULL, -1); //suprimer le token chez le client
		$_SESSION['s_nb_token']++;
		$cookie_name = 'facekikou_'.session_id().':'.$_SESSION['s_nb_token'];
		setcookie($cookie_name,$_SESSION['s_token'], time()+600); //enregistement du token chez le client
		$_SESSION['s_time'] = time()+600; //10 min de temps de session
		*/

	}
	
    /**
     * check_session : vérifie si le jeton et le temps de session du client sont corrects
     */
	private static function check_session()
	{
		/*
		if(!isset($_SESSION['s_nb_token'])) $_SESSION['s_nb_token'] = 0;
		$cookie_name = 'facekikou_'.session_id().':'.$_SESSION['s_nb_token'];
		if(isset($_SESSION['s_token']) && isset($_SESSION['s_time']) &&  isset($_COOKIE[$cookie_name])) //vérification
		{
			if(($_COOKIE[$cookie_name] == $_SESSION['s_token']) && (time() < $_SESSION['s_time'])) Session::secure_session(); // resecurise la sesion, pour un prochain échange avec le serveur
			else Session::logout(); //informations fausse = déconnexion
		}
		else Session::secure_session(); // resecurise la sesion, pour un prochain échange avec le serveur
		*/
	}
	
    /**
     * check_auth : vérifie si un client est authentifié
     */
	private static function check_auth()
	{
		if(isset($_SESSION['user'])) return true; //si la variable session de l'objet User du client existe
		return false;
	}
	
    /**
     * login : création d'une session pour un client
     *
     * @param User $_login : objet User du client
     */
	public static function login($_login)
	{
		$_SESSION['user'] = serialize($_login); //mise en session de l'objet User du client
		Request::redirect(); //redirection à la racine du site
	}
	
    /**
     * logout : detruire la session du client
     */
	public static function logout() 
	{
		session_unset(); //supprime les variables de la session
		session_destroy(); //supprime la session
	}
}
?>