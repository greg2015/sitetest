<?php
/**
 * Classe Security
 *
 * Permet d'eviter les failles de sécurité du site et de la base de données
 */
class Security
{
	/**
     *	crypt_data : retourne un hashage de la chaine en paramètres, pour comparer des chaines sans les identifier
     *
     * @param string $_data : une chaine de caractères
     */
	public static function crypt_data($_data){ return sha1(md5('_sel_client%').$_data.md5('@sel_psw**'));} //hashage et salage d'une donnée

	/**
     *	give_get : retourne la variable GET dont le nom est en paramètre de la fonction
     *
     * @param string $_name : nom de la variable
     */
	public static function give_get($_name)
	{
		if(isset($_GET[$_name]) && !empty($_GET[$_name])) return Security::escapement($_GET[$_name]);
		return NULL;
	}

	/**
     *	give_form_get : retourne un tableau de variables GET dont les nom sont en paramètre en tableau
     *
     * @param array $_form : tableau clefs / valeurs (si valeur = true, la variable ne peux pas être null)
     */
	public static function give_form_get($_form)
	{
		foreach($_form as $var => $required){if (is_null($array[$var] = Security::give_get($var)) && $required) return NULL;}
		return $array;
	}

	/**
     *	give_post : retourne la variable POST dont le nom est en paramètre de la fonction
     *
     * @param string $_name : nom de la variable
     */
	public static function give_post($_name)
	{
		if(isset($_POST[$_name]) && !empty($_POST[$_name])) return Security::escapement($_POST[$_name]);
		return NULL;
	}

	/**
     *	give_form_post : retourne un tableau de variables POST dont les nom sont en paramètre en tableau
     *
     * @param array $_form : tableau clefs / valeurs (si valeur = true, la variable ne peux pas être null)
     */
	public static function give_form_post($_form)
	{
		foreach($_form as $var => $required){if (is_null($array[$var] = Security::give_post($var)) && $required) return NULL;}
		return $array;
	}

	/**
     *	escapement : formatte une variable pour éviter une faille
     *
     * @param string $_data : une chaine de caractères
     */
	public static function escapement($_data)
	{
		if(ctype_digit($_data)) return intval($_data); //si entier	
		return $_data; //si chaine
	}

	/**
     *	check_string : retourne si oui ou non une chaine vérifie des conditions
     *
     * @param string $_string : une chaine de caractères à tester
     * @param array $_param : un tableau clefs / valeurs de conditions
     *
     * min : nombre minimum de caractères
     * max : nombre maximum de caractères
     * regex : une exprèsion régulière
     * simple : pas d'accent ni de carctères spéciaux
     * email : est un email
     * set : fais partie d'une liste de chaine dans un tableau
     */
	public static function check_string($_string,$_param = array())
	{
		if (!isset($_param['not_null']) && is_null($_string)) return true;
		if (!is_string($_string)) return false;
		foreach($_param as $key => $value)
		{
			switch($key)
			{
				case 'min':
					if (strlen($_string) < $value) return false;
					break;
				case 'max':
					if (strlen($_string) > $value) return false;
					break;
				case 'regex':
					if (preg_match($value, $_string)) return false;
					break;
				case 'simple':
					if (preg_match('#[^a-zA-Z0-9._]#', $_string)) return false;
					break;
				case 'email':
					if (!filter_var($_string, FILTER_VALIDATE_EMAIL)) return false;
					break;
				case 'set':
					foreach ($value as $v) if(strcmp($_string,$v) == 0) return true;
					return false;
				default: break;
			}
		}
		return true;
	}

	/**
     *	check_number : retourne si oui ou non un nombre vérifie des conditions
     *
     * @param string $_number : un nombre à tester
     * @param array $_param : un tableau clefs / valeurs de conditions
     *
     * min : nombre minimum 
     * max : nombre maximum 
     */
	public static function check_number($_number,$_param = array())
	{
		if (!isset($_param['not_null']) && is_null($_number)) return true;
		if (!is_numeric($_number)) return false;
		foreach($_param as $key => $value)
		{
			switch($key)
			{
				case 'min':
					if ($_number < $value) return false;
					break;
				case 'max':
					if ($_number > $value) return false;
					break;				
				default: break;
			}
		}
		return true;
	}

	public static function upload_image($target,$name)
	{
		$target = $_SERVER['DOCUMENT_ROOT'].$target;
		$max_size = 100000;
		$max_width = 800;
		$max_height = 800;
		 
		$tabExt = array('jpg','gif','png','jpeg');    // Extensions autorisees
		$infosImg = array();
		 
		$extension = '';
		$message = '';
		$nomImage = '';
		 
		if( !is_dir($target) ) {
		  if( !mkdir($target, 0755) ) {
		  	echo $target, $name;
		    exit('Erreur : le répertoire cible ne peut-être créé ! Vérifiez que vous diposiez des droits suffisants pour le faire ou créez le manuellement !');
		  }
		}

		if(!empty($_POST))
		{
		  if(!empty($_FILES['fichier']['name']) )
		  {

		    $extension  = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
		    if(in_array(strtolower($extension),$tabExt))
		    {
		      $infosImg = getimagesize($_FILES['fichier']['tmp_name']);
		      if($infosImg[2] >= 1 && $infosImg[2] <= 14)
		      {

		        if(($infosImg[0] <= $max_width) && ($infosImg[1] <= $max_height) && (filesize($_FILES['fichier']['tmp_name']) <= $max_size))
		        {
		          if(isset($_FILES['fichier']['error']) && UPLOAD_ERR_OK === $_FILES['fichier']['error'])
		          {
		            $nomImage = $name.'.'. $extension;
		            if(move_uploaded_file($_FILES['fichier']['tmp_name'], $target.$nomImage)) return $extension;
		            else return false;
		          } else return false;
		        } else return false;
		      } else return false;
		    } else return false;
		  } else return NULL;
		}
		else return NULL;
	}
}
?>