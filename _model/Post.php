<?php
/**
 * Classe Post
 *
 * Permet d'obtenir et de manipuler une publication
 */
class Post
{
	public $id; //id en base de données de la publication
	public $user; //objet User de l'auteur de la publication
	public $message; //texte de la publication
	public $date; //date de la publication
	public $wall; //objet User du destinataire de la publication (ou NULL)

	/**
     *	instanciation
     *
     * @param string $_id : id de la publication en base de données
     * @param string $_iduser : id de l'auteur en base de données
     * @param string $_message : texte de la publication
     * @param string $_date : date de la publication
     * @param string $_idwall : id du destinataire en base de données
     */
	function __construct($_id, $_iduser, $_message, $_date = NULL, $_idwall = NULL)
	{
		$this->id = $_id;
		$this->user = User::get_by_id($_iduser);
		$this->message = $_message;
		$this->date = $_date;
		if(!is_null($_idwall)) $this->wall = User::get_by_id($_idwall);
		else $this->wall = NULL;
	}
	
	/**
     *	get_tab: retourne l'objet sous forme de tableau 
     */
	public function get_tab()
	{
		return array_merge(['ID' => $this->id, 'MESSAGE' => $this->message, 'DATE' => $this->date, 'WALL' => ($this->wall != NULL)? 'Mur de '.$this->wall->pseudo:''],$this->user->get_tab());
	}
	
	/**
     *	persist: enregistre ou met à jour un utilisateur en base de données
     */
	public function persist()
	{
		is_null($this->wall)? $idWall = NULL : $idWall = $this->wall->id;
		$id = Database::get()->call_function('persist_post',[$this->id,$this->user->id,$idWall,preg_replace('@(https?://[a-z0-9._/-]+\.(jpg|jpeg|png|bmp|gif]))@', '<img alt class="pp" src="$1">', $this->message)]);
	}
	
	/**
     *	[static] get_by_id : obtenir l'objet publication correspondant à l'id en paramètre
     *
     * @param numbrer $_id : id de la publication en base de données
     */
	public static function get_by_id($_id)
	{
		$tab = Database::get()->get_by_id('post','idPost',$_id);
		return Post::populate($tab);
	}
	
	/**
     *	[static] get_user_wall : obtenir les publications sur le mur d'un utilisateur
     *
     * @param User $_user : objet User du propriétaire du mur
     * @param numbrer $_limit : nombre de publication voulu
     * @param numbrer $_offset : décalage à partir de la première publication
     */
	public static function get_user_wall($_user,$_limit,$_offset)
	{
		$tab = Database::get()->prepare_execute('SELECT * FROM post WHERE idUser = :id OR idWall = :id  ORDER BY date DESC LIMIT '.$_limit.' OFFSET '.$_offset.';',[':id' => $_user->id]);
		if(empty($tab)) return false;
		return Post::populate_collection($tab);
	}
	
	/**
     *	[static] get_user_news : obtenir les publications du fil d'actualité d'un utilisateur
     *
     * @param User $_user : objet User du propriétaire du fil d'actualité
     * @param numbrer $_limit : nombre de publication voulu
     * @param numbrer $_offset : décalage à partir de la première publication
     */
	public static function get_user_news($_user,$_limit,$_offset)
	{
		$tab = Database::get()->prepare_execute('SELECT * FROM post WHERE idUser IN (SELECT idFriend FROM friendship WHERE idUser = :id AND agree = TRUE UNION (SELECT idUser FROM friendship WHERE idFriend = :id AND agree = TRUE)) OR idUser = :id ORDER BY date DESC LIMIT '.$_limit.' OFFSET '.$_offset.';',[':id' => $_user->id]);
		if(empty($tab)) return false;
		return Post::populate_collection($tab);
	}

	/**
     *	[static] get_collection_tab : retourne la collection d'objets Post sous forme de tableau
     *
     * @param array $_collection : tableau d'objet Post
     */
	public static function get_collection_tab($_collection)
	{

		if (empty($_collection)) return NULL;
		$tab = array(); 
		foreach($_collection as $post) array_push($tab, $post->get_tab());
		return $tab;
	}
	
	/**
     *	[static] populate_collection : retourne un tableau d'objet Post à partir d'un tableau de plusieurs ligne en base de données
     *
     * @param array $_tab : tableau de lignes en base de données
     */
	private static function populate_collection($_tab)
	{
		$collection = array();
		foreach($_tab as $line) array_push($collection,Post::populate($line));
		return $collection;
	}

	/**
     *	[static] populate : retourne un objet Post à partir d'une ligne de base de données
     *
     * @param array $_tab : ligne en base de données
     */
	private static function populate($_tab)
	{
		if (is_null($_tab)) return NULL;
		return new Post($_tab['idPost'], $_tab['idUser'], $_tab['message'], $_tab['date'], $_tab['idWall']);
	}
}
?>