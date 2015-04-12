<?php
/**
 * Controlleur d'identification
 *
 * Permet de geret les actions d'un utilisateur non authentifié ou/et non inscrit
 */
class IdentificationController
{
	/**
     *	sigin : propose et gére l'identification, page par default pour un utilisateur non authentifié 
     */
	public function signin()
	{
		$page = new Page('Bienvenue');
		
		/* récupération si identification */
		$login = Security::give_form_post(['name' => true, 'pswd' => true]); //récupération de l'identifiant et du mot de passe
		if (!is_null($login))
		{
			if(is_null($user = User::get_by_login($login['name'], Security::crypt_data($login['pswd'])))) //vérification si identification correct
			{
				$page->add('Votre identifiant ou votre mot de passe est incorrect !','danger');
			}
			else Session::login($user); //mise à jour de la session
		}
		
		if (Security::give_get('ctrl') == 'logout') $page->add('Vous êtes déconnecté !'); //affichage de deconnexion
		
		$page->begin();
		$vform = new View('_view/html/signin.html');
		$vform->set(['IDENTIFIANT' => $login['name']]);
		$vform->display();
		$page->finish(); 
	}
	
	/**
     *	signup : propose et gére l'inscription
     */
	public function signup()
	{
		$page = new Page('Inscription');

		/* vérification si inscription */
		if ($have_form = !is_null($form = Security::give_form_post(['mail' => true, 'pswd' => true, 'pseudo' => true, 'alias' => false, 'age' => false, 'desc' => false, 'sexe' =>false])))
		{
		if (!Security::check_string($form['mail'],['min' => 6, 'max' => 30, 'email' => true, 'not_null' => true])) $page->add('Mail incorrect','danger');
		if (!Security::check_string($form['pswd'],['min' => 6, 'max' => 30, 'not_null' => true])) $page->add('Mot de passe incorrect','danger');
		if (!Security::check_string($form['pseudo'],['simple' => true,'min' => 4, 'max' => 30, 'not_null' => true])) $page->add('Pseudo incorrect','danger');
		if (!Security::check_string($form['alias'],['simple' => true, 'min' => 4, 'max' => 30])) $page->add('Alias incorrect','danger');
		if (!Security::check_number($form['age'],['min' => 1, 'max' => 150])) $page->add('Age incorrect','danger');
		if (!Security::check_string($form['sexe'],['set' => ['M','F']])) $page->add('Sexe incorrect','danger');
		if(!$page->box)
		{
			if(!User::signup($form))
			{
				$page->add('Le pseudo "'.$form['pseudo'].'" existe déjà, vous devez en choisir un autre !','danger');
				$form['pseudo'] = '';
			}
			else
			{
				$page->add('Vous vous êtes inscrit !','success');
				$have_form = false;
			}
		}
		}
		$page->begin();
		$vform = new View('_view/html/signup.html');
		if($have_form){
			$vform->set(['MAIL' => $form['mail'],
			'PSEUDO' => $form['pseudo'],
			'ALIAS' => $form['alias'],
			'AGE' => $form['age'],
			'DESCRIPTION' => $form['desc'],]);
		}
		$vform->display();
		$page->finish(); 
	}
}
?>
