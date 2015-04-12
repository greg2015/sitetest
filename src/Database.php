<?php
/**
 * Classe Database
 *
 * Permet de communiquer facilement avec une base de données
 */
class Database
{
	static private $instance; // instance objet Database
	public $base; //objet PDO de la classe
	private $dbname; //nom de la base de données
	
	/**
     *	instanciation
     *
     * @param string $_type : type de la base de données (ex : mysql)
     * @param string $_host : adresse de l'hebergeur
     * @param string $_dbname : nom de la base de données
     * @param string $_login : login de la base de données
     * @param string $_password : mot de passe de la base de données
     */
	function __construct($_type,$_host,$_dbname,$_login,$_password)
	{
		$this->dbname = $_dbname;
		try{$this->base = new PDO("$_type:host=$_host;dbname=$_dbname;charset=utf8",$_login,$_password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));}
		catch(PDOException $e)
		{
			echo 'Erreur connexion : ',$e->getMessage();
			exit();
		}
	}
	
	/**
     *	prepare_execute : prepare et execute une requete en base de données
     *
     * @param string $_req : requete en SQL
     * @param array $_param : tableau clefs / valeurs avec les parmètres de la requete
     */
	public function prepare_execute($_req,$_param = array())
	{
		$stmt = $this->base->prepare($_req);
		$stmt->execute($_param);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
     *	call_function : prepare et execute une fonction en base de données
     *
     * @param string $_name : nom de la fonction
     * @param array $_param : tableau clefs / valeurs avec les parmètres de la fonction
     */
	public function call_function($_name,$_param = array())
	{
		$i=0;
		$list= '';
		$values = array();
		foreach($_param as $p)
		{
			$values[':P'.$i] = $p;
			if($i != 0) $list.=',';
			$list.=':P'.$i;
			++$i;
		}
		$stmt = $this->base->prepare("SELECT `$_name`(".$list.") AS `result`;");
		$stmt->execute($values);
		$tab = $stmt->fetch(PDO::FETCH_NUM);
		return $tab[0];
	}

	/**
     *	get_by_id : retourne une ligne spécifique de la table 
     *
     * @param string $_table : nom de la table
     * @param string $_idparam : nom de la colonne de l'id
     * @param string $_id : valeur de l'id
     */
	public function get_by_id($_table,$_idparam,$_id)
	{
		$stmt = $this->prepare_execute('SELECT * FROM '.$_table.' WHERE '.$_idparam.' = :id LIMIT 1;',[':id' => $_id]);
		if (empty($stmt)) return NULL;
		return $stmt[0];
	}
	
	/**
     *	get : obtenir une instance de la base de données 
     */
	public static function get()
	{
        if (!isset(self::$instance))
            self::$instance = new Database('mysql','localhost','gdelillo_db','root','');
        return self::$instance;
	}
}
?>