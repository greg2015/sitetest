<?php
/**
 * Classe Request
 *
 * Permet d'obtenir une url ou une rediretion absolue à partir d'une url relative
 */
class Request
{
	static private $root = '/imac/s2'; //racine du site, à changer en fonction de l'emplacement du site sur le serveur
	
    /**
     * get_root : obtenir le chemin absolue vers une destination
     *
     * @param string $_url [facultatif] : chemin à partir de la racine du site (relatif)
     */
	public static function get_root($_url = ''){
		return self::$root.$_url;
	}
	
    /**
     * redirect : redirection de la page vers une destination
     *
     * @param string $_url [facultatif] : chemin à partir de la racine du site (relatif)
     */
	public static function redirect($_url = ''){
		header('Location: '.self::$root.$_url);
		exit();
	}
	

    /**
     * previous_page : redirection vers la page precedente
     */
	public static function previous_page(){
		header('Location: '.$_SERVER['HTTP_REFERER']);
		exit();
	}
	
}
?>    