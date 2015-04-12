<?php
/**
 * Controlleur d'utilisateur
 *
 * Permet de geret les actions de l'utilisateur en rapport avec lui-meme ou d'autres utilisateurs
 */
class UserController
{
	private $url; //paramètre en url (pseudo d'un utilisateur)
	private $me; //objet User de l'utilisateur actuel
	private $user; //objet User de l'utilisateur en url
	private $is_me; //VRAI si l'utilisateur en url et l'utilisateur actuel
	private $page; //objet Page pour l'affichage

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
		$this->is_me = ($this->me->pseudo == $this->url); //booleen si c'est le pseudo de l'utilisateur session
		
		/* Initialisation de la page */
		$this->page = new Page('',$this->me);
		
		/* Vérifications */
		if (!$this->is_me) //utilisateur autre que utilisateur session
		{
			if(!$this->user = User::get_by_pseudo($this->url)) $this->search(); //si le pseudo n'existe pas, redirection
			if(!$this->me->is_friend($this->user)) $this->be_friend(); //vérification si ami sinon demande en ami
		} 
		else $this->user = $this->me;
	}

	/**
     *	wall : affiche le mur de l'utilisateur ayant son pseudo en url
     */
	public function wall()
	{
		if($this->is_me)
		{
			$this->page->title = 'Mon mur';
			$post_title = 'Que faite vous en ce moment ?';
		}
		else
		{
			$this->page->title = 'Mur de '.$this->user->pseudo;
			$post_title = 'Publier un message sur le mur de '.$this->user->pseudo.' ?';
		}
		$this->page->begin();
		$wall = new View('_view/html/wall.html');
		$wall->set(array_merge(['TITLE' => $this->page->title, 'POST_TITLE' => $post_title], $this->user->get_tab()));
		if(!is_null($publications = Post::get_collection_tab(Post::get_user_wall($this->user,50,0)))) //affichage des 50 dernières publications sur le mur
		{
			$wall->set_loop(['MESSAGE' => $publications]);
		}
		$wall->display();
		$this->page->finish();
	}

	/**
     *	friends : affiche les amis de l'utilisateur ayant son pseudo en url
     */
	public function friends()
	{
		if($this->is_me) $this->page->title = 'Mes amis';
		else $this->page->title = 'Amis de '.$this->user->pseudo;
		$this->page->begin();
		if ($this->is_me)
		{
			if ($this->user->number_want_be_friend() >0) // affichages des demandes d'amitiée
			{
				$demands = User::get_collection_tab($this->user->who_want_be_friend()); //récupération des demandes
				$dlist = new View('_view/html/want_be_friend.html');
				$dlist->set(['TITRE' => 'Demandes en ami']);
				$dlist->set_loop(['FRIEND' => $demands]);
				$dlist->display();
			}
		}
		if(!is_null($tab = User::get_collection_tab($this->user->get_friends()))) // affichage des amis
		{
			$list = new View('_view/html/friends.html');
			$list->set(['TITRE' => $this->page->title]);
			$list->set_loop(['FRIEND' => $tab]);
			$list->display();
		}
		$this->page->finish();
	}


	public function photo()
	{
		
		if(!$this->is_me) Request::redirect();
		if (!(is_null($chg = $this->me->chg_photo(true))))
		{
			if ($chg)
			{
				Session::login(User::get_by_id($this->me->id));
			}
			else
			{
				$this->page->add('Image incorrect !','danger');
			}
		}
		$this->page->title = 'Modifier photo';
		$this->page->begin();
		$vphoto = new View('_view/html/photo.html');
		$vphoto->set($this->me->get_tab());
		$vphoto->display();
		$this->page->finish();
	}

	public function del_photo()
	{
		
		if(!$this->is_me) Request::redirect();
		$this->me->chg_photo(NULL);
		Session::login(User::get_by_id($this->me->id));
	}

	/**
     *	search : affiche la liste des utilisateurs qui corresponde à une recherche
     */
	private function search()
	{
		$this->page->title = 'Recherche utilisateur : '.$this->url;
		$this->page->begin();
		if(!is_null($search = User::get_collection_tab(User::get_by_search($this->url))))
		{
			
			$list = new View('_view/html/search.html');
			$list->set(['TITRE' => $this->page->title]);
			$list->set_loop(['RESULT' => $search]);
			$list->display();
		}
		else echo 'pas de résultat';
		$this->page->finish();
		exit(); // éviter erreur
	}

	/**
     *	be_friend : affiche la page d'invitation à devenir ami
     */
	private function be_friend()
	{
		if(isset($_GET['want_be_friend'])) $this->want_be_friend(); //demande d'amitiée
		if(isset($_GET['dont_want_be_friend'])) $this->dont_want_be_friend(); //refus demande d'amitiée
		if($this->me->want_be_friend($this->user)) $button= 'Demande déjà envoyée';
		else $button = 'Demander en ami';
		$this->page->title = $this->user->pseudo;
		$this->page->begin();
		$add_box = new View('_view/html/be_friend.html');
		$add_box->set(['PSEUDO' => $this->user->pseudo, 'DESCRIPTION' => $this->user->description, 'BUTTON' => $button]);
		$add_box->display();
		$this->page->finish();
		exit(); // éviter erreur
	}
	
	/**
     *	want_be_friend : demande d'amitiée
     */
	public function want_be_friend()
	{ 
		$this->me->be_friend($this->user);
		Request::previous_page();
	}
	
	/**
     *	dont_want_be_friend : refus demande d'amitiée
     */
	public function dont_want_be_friend()
	{ 
		$this->me->dont_be_friend($this->user);
		Request::previous_page();
	}
}
?>
