<?php
/**
 * Classe Page
 *
 * Permet d'afficher les principaux éléments d'une page du site
 */
class Page
{
    public $title; //titre de la page
	public $css; //tableau de chemins vers fichier css 
	public $js; //tableau de chemeins ver fichiers js
	public $user; //objet User de l'utilisateur authentifié ou NULL
	public $box; //tableau de boites de dialogues

	/**
     *	instanciation
     *
     * @param string $_title : titre de la page
     * @param User $_user : objet User de l'utilisateur authentifié ou NULL
     * @param array $_css : tableau de chemins vers fichier css
     * @param array $_js : tableau de chemeins ver fichiers js
     */
	function __construct($_title,$_user = NULL,$_css = array(),$_js = array()) 
	{
		/* parmétrage de la page */
        $this->title = $_title;
		$this->user = $_user;
		$this->box = NULL;
		
        /* formatage tableau css */
        foreach ($_css as $path) array_push($this->css,['PATH' => $path]);

        /* formatage tableau js */
        foreach ($_js as $path) array_push($this->js,['PATH' => $path]);
    }

	/**
     *	begin : affiche l'entete de la page (head + <body>)
     *
     * @param string $_class : valeur class de la balise <body>
     */
	public function begin($_class = 'container')
	{ 
	
		/* Affichage de l'header */
		$header = new View('_view/structure/header.html');
		$header->set(['CSS_PATH' => Request::get_root('/src/css'), 'TITLE' => $this->title]);
		if (!empty($this->css)) $header->setLoop(['CSS' => $this->css]);
		$header->display();

		/* Affichage des messages */
		if (!is_null($this->box))
		{
			$nav = new View('_view/structure/box.html');
			$nav->set_loop(['BOX' => $this->box]);
			$nav->display();
		}

		/* Affichage de la barre de menu */
		if (!is_null($this->user))
		{
			$nav = new View('_view/structure/nav.html');
			$nav->set(['PSEUDO' => $this->user->pseudo,'TITLE' => $this->title, 'NBDMDS' => $this->user->number_want_be_friend()]);
			$nav->display();
		}
		
		echo '<div class="',$_class,'">';
	}
	
	/**
     *	finish : affiche la fin de la page (</body> + js)
     */
	public function finish() //footer personalisé
	{ 
		$footer = new View('_view/structure/footer.html');
		$footer->set(['JS_PATH' => Request::get_root('/src/js')]);
		if (!empty($this->js)) $footer->set_loop(['JS' => $this->js]);
		$footer->display();
	} 

	/**
     *	add : ajouter une boite de dialogue
     *
     * @param string $_value : texte de la boite de dialogue
     * @param string $_type : type de boite de dialogue (class bootstrap => couleur et titre)
     * @param string $_title : titre de la boite de dialogue
     */
	public function add($_value,$_type='info',$_title = NULL)
	{
		if (is_null($this->box)) $this->box = array();
		if (is_null($_title))
		switch($_type)
		{
			case 'danger' : $title = 'Oh mince !';
			break;
			case 'warning' : $title = 'Attention !';
			break;
			case 'success' : $title = 'Bravo !';
			break;
			default : $title = 'Ooh !';
			break;
			
		}else $title = $_title;
		array_push($this->box,['TYPE' => $_type,'TITLE' => $title, 'TEXT' => $_value]);
	}
}
?>