<?php
/**
 * Classe User
 *
 * Permet d'obtenir et de manipuler un utilisateur
 */
class User
{
	public $id; //id de l'utilisateur en base de données
	public $mail; //mail de l'utilisateur
	public $pseudo; //pseudo de l'utilisateur
	public $alias; //alias de l'utilisateur
	public $age; //age de l'utilisateur
	public $description; //description de l'utilisateur
	public $sexe; //sexe de l'utilisateur
	public $photo; //url de la photo de l'utilisateur

	/**
     *	instanciation
     *
     * @param string $_id : id de l'utilisateur en base de données
     * @param string $_mail : mail de l'utilisateur
     * @param string $_pseudo : pseudo de l'utilisateur
     * @param string $_alias : alias de l'utilisateur
     * @param string $_age : age de l'utilisateur
     * @param string $_description : description de l'utilisateur
     * @param string $_sexe : sexe de l'utilisateur
     * @param string $_photo : url de la photo de l'utilisateur
     */
	function __construct($_id, $_mail, $_pseudo, $_alias = NULL, $_age = NULL, $_description = NULL, $_sexe = NULL, $_photo = NULL)
	{
		$this->id = $_id;
		$this->mail = $_mail;
		$this->pseudo = $_pseudo;
		$this->alias = $_alias;
		$this->age = $_age;
		$this->description = $_description;
		$this->sexe = $_sexe;
		$this->photo = $_photo;
	}

	/**
     *	get_tab: retourne l'objet sous forme de tableau
     */
	public function get_tab()
	{
		$mdi = '';
		$photo = '';
		if(!is_null($this->photo)) $photo = '<img alt src="'.$this->photo.'" draggable="false">';
		else $mdi= 'mdi-action-perm-identity';
		return ['ID' => $this->id, 'MAIL' => $this->mail, 'PSEUDO' => $this->pseudo, 'ALIAS' => $this->alias, 'AGE' => $this->age, 'DESCRIPTION' => $this->description, 'SEXE' => $this->sexe, 'PHOTO' => $photo, 'MDI' => $mdi];
	}

	/**
     *	is_friend : retourne si oui ou non, l'utilisateur en paramètre est un ami de l'utilisateur
     *
     * @param User $_user : objet User de l'utilisateur à tester
     */
	public function is_friend($_user) 
	{
		return Database::get()->call_function('is_friend',[$this->id,$_user->id]);
	}
	
	/**
     *	be_friend : envoyer ou confirmer une demande d'amitiée
     *
     * @param User $_user : objet User de l'utilisateur destinataire
     */
	public function be_friend($_user) 
	{
		return Database::get()->call_function('be_friend',[$this->id,$_user->id]);
	}

	/**
     *	dont_be_friend : detruire un lien d'amitié ou refuser une demande d'amitiée
     *
     * @param User $_user : objet User de l'utilisateur destinataire
     */
	public function dont_be_friend($_user)
	{
		return Database::get()->call_function('dont_be_friend',[$this->id,$_user->id]);
	}

	public function chg_photo($_action)
	{

		if (is_null($_action)) Database::get()->call_function('delete_photo',[$this->id]);
		$ext = Security::upload_image(Request::get_root('/src/img/user/'),$this->id);
		if (is_null($ext)) return NULL;
		if ($ext)
		{
			return Database::get()->call_function('add_photo',[$this->id,Request::get_root('/src/img/user/'.$this->id.'.'.$ext)]);
		}
		return false;
	}
	/**
     *	get_friends : retourne un tableau (collection) des objets utilisateurs ami de l'utilisateur
     */
	public function get_friends() ///*****
	{
		$collection = array();
		$i=0;
		$tab = Database::get()->prepare_execute('SELECT * FROM user_view WHERE idUser IN (SELECT idFriend FROM friendship WHERE idUser = :id AND agree = TRUE UNION(SELECT idUser FROM friendship WHERE idFriend = :id AND agree = TRUE));',[':id' => $this->id]);
		foreach($tab as $line) $collection[$i++] = User::populate($line);
		return $collection;
	}

	/**
     *	number_want_be_friend : retourne le nombre de demandes d'amitiée
     */
	public function number_want_be_friend()
	{
		$tab = Database::get()->prepare_execute('SELECT COUNT(*) AS NB FROM user_view WHERE idUser IN (SELECT idUser FROM friendship WHERE idFriend = :id AND agree = FALSE);',[':id' => $this->id]);
		return $tab[0]['NB'];
	}

	/**
     *	want_be_friend : retourne si une demande d'ami à oui ou non été effectuée
     *
     * @param User $_user : utilisateur destinataire de la demande
     */
	public function want_be_friend($_user)
	{
		return Database::get()->call_function('already_want_be_friend',[$this->id, $_user->id]);
	}

	/**
     *	who_want_be_friend : retourne un tableau (collection) des objets utilisateurs qui veulent étre ami avec l'utilisateur
     */
	public function who_want_be_friend()
	{
		$collection = array();
		$i=0;
		$tab = Database::get()->prepare_execute('SELECT * FROM user_view WHERE idUser IN (SELECT idUser FROM friendship WHERE idFriend = :id AND agree = FALSE);',[':id' => $this->id]);
		foreach($tab as $line) $collection[$i++] = User::populate($line);
		return $collection;
	}

	/**
     *	[static] signup : effectue l'inscription de l'utilisateur en base de donnés
     *
     * @param array $_form : tableau d'information d'utilisateur
     */
	public static function signup($_form)
	{
		$no_error = true;
		$db = Database::get();
		$db->base->beginTransaction();
		try
		{
			$no_error = $db->call_function('signup',[$_form['mail'],Security::crypt_data($_form['pswd']),$_form['pseudo'],$_form['alias'],$_form['age'],$_form['desc'],$_form['sexe']]);
			$db->base->commit();
		}
  		catch(PDOException $e) {
			$dbh->base->rollback();
			return false;
		}
		return $no_error;
	}

	/**
     *	[static] get_by_id : obtenir l'objet utilisateur correspondant à l'id en paramètre
     *
     * @param numbrer $_id : id de l'utilisateur en base de données
     */
	public static function get_by_id($_id)
	{
		$tab = Database::get()->get_by_id('user_view','idUser',$_id);
		return User::populate($tab);
	}
	
	/**
     *	[static] get_by_pseudo : obtenir l'objet utilisateur correspondant au pseudo en paramètre
     *
     * @param string $_pseudo : pseudo de l'utilisateur en base de données
     */
	public static function get_by_pseudo($_pseudo)
	{
		$tab = Database::get()->prepare_execute('SELECT * FROM user_view WHERE pseudo LIKE :pseudo LIMIT 1;',[':pseudo' => $_pseudo]);
		if(empty($tab)) return false;
		return User::populate($tab[0]);
	}
	
	/**
     *	[static] get_by_login : obtenir l'objet utilisateur correspondant au informations d'identifiaction en paramètres
     *
     * @param string $_name : pseudo ou mail de l'utilisateur
     * @param string $_password : mot de passe de l'utilisateur
     */
	public static function get_by_login($_name,$_password)
	{
		$id = Database::get()->call_function('login',[$_name, $_password]);
		return User::get_by_id($id);
	}

	/**
     *	[static] get_by_search
     */
	public static function get_by_search($_search)
	{
		$tab = Database::get()->prepare_execute('SELECT * FROM user_view WHERE pseudo LIKE :search OR alias LIKE :search OR description LIKE :search LIMIT 20;',[':search' => '%'.$_search.'%']);;
		return User::populate_collection($tab);
	}

	/**
     *	[static] get_collection_tab : retourne la collection d'objets User sous forme de tableau
     *
     * @param array $_collection : tableau d'objet User
     */
	public static function get_collection_tab($_collection)
	{
		if (empty($_collection)) return NULL;
		$tab = array();
		foreach($_collection as $user) array_push($tab, $user->get_tab());
		return $tab;
	}
	
	/**
     *	[static] populate_collection : retourne un tableau d'objet User à partir d'un tableau de plusieurs ligne en base de données
     *
     * @param array $_tab : tableau de lignes en base de données
     */
	private static function populate_collection($_tab)
	{
		$collection = array();
		foreach($_tab as $line) array_push($collection,User::populate($line));
		return $collection;
	}

	/**
     *	[static] populate : retourne un objet User à partir d'une ligne de base de données
     *
     * @param array $_tab : ligne en base de données
     */
	private static function populate($_tab)
	{
		if (is_null($_tab)) return NULL;
		return new User($_tab['idUser'], $_tab['mail'], $_tab['pseudo'], $_tab['alias'], $_tab['age'], $_tab['description'], $_tab['sexe'], $_tab['photo']);
	}
}
?>