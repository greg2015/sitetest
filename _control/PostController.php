<?php
/**
 * Controlleur de publications
 *
 * Permet de geret les actions de l'utilisateur en rapport avec les publications
 */
class PostController
{
	private $url; //paramètre en url (pseudo d'un utilisateur)
	private $me; //objet User de l'utilisateur actuel
	private $user; //objet User de l'utilisateur en url
	private $is_me; //VRAI si l'utilisateur en url et l'utilisateur actuel

	/**
     *	instanciation
     *
     * @param string $_url : paramètre en url (pseudo d'un utilisateur)
     */
	function __construct($_url)
	{
		/* Recupération des information */
		$this->me = Session::get_info(); 
		$this->url = $_url;
		if(is_null($_url)) $this->is_me = true; //si pas de pseudo en paramètre => page d'accueil 
		else $this->is_me = ($this->me->pseudo == $this->url); //booleen si c'est le pseudo de l'utilisateur session
		
		/* Vérifications */
		if (!$this->is_me) //utilisateur autre que utilisateur session
		{
			if(!$this->user = User::get_by_pseudo($this->url)) Request::redirect(); //si le pseudo n'existe pas, redirection
			if(!$this->me->is_friend($this->user)) Request::redirect(); //si le pseudo n'est pas celui d'un ami, redirection
		} 
		else $this->user = NULL; //le message n'est pas publié sur le mur d'un utilisateur
	}

	/**
     *	index : affichage du tableaux de bord des publications, page par default pour un utilisateur authentifié
     */
	public function index()
	{
		$page = new Page('Fil d\'actualités',$this->me);
		$page->begin();
		$board = new View('_view/html/board.html');
		$board->set($this->me->get_tab());
		if(!is_null($publications = Post::get_collection_tab(Post::get_user_news($this->me,50,0)))) //affichage des 50 dernieres publications (pour l'utilisateur)
		{
			$board->set_loop(['MESSAGE' => $publications]);
		}
		$board->display();
		$page->finish(); 
	}
	
	/**
     *	send : publier un message, si un pseudo est spécifié en url il est le destinataire
     */
	public function send()
	{
		$text = Security::give_post('msg'); //récupération du texte du message en "post"
		if(is_null($text)) Request::redirect(); //redirection si message invalide
		if(is_null($this->user)) $wall = NULL; //le message n'est pas publié sur le mur d'un utilisateur
		else $wall = $this->user->id;//le message est publié sur le mur d'un utilisateur
		$message = new Post(NULL,$this->me->id,$text,NULL,$wall); //création d'une nouvelle publication
		$message->persist(); //enregistrement du message en bdd;
		Request::previous_page();
	}
}
?>