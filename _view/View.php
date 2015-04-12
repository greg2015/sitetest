<?php
/**
 * Classe View
 *
 * Permet d'afficher une partie de la page à partir d'un template en HTML
 */
class View
{
	private $page; //buffer : fichier template html dans une chaine de caractère
	private $variables; //tableau de variables du template avec leur valeurs
	private $loops; //tableau de variables complexes répetitives (listes) avec leurs valeurs
	private $clear; //VRAI si les identificateurs de variable du template sont supprimés
	
	/**
     *	instanciation
     *
     * @param string $_file : chemin vers le template HTML
     */
	function __construct($_file) 
	{
		if(empty($_file) || !file_exists($_file) || !is_readable($_file))
		{
			exit();
		}
		$handle = fopen($_file,'rb');
		$this->page = fread($handle,filesize($_file));
		fclose($handle);
		$this->variables = ['ROOT_PATH' => Request::get_root()];
		$this->loops = array();
		$this->clear = false;
    }
	
	/**
     *	set : ajoute une liste de variables et leurs valeurs au template
     *
     * @param array $_array : tableau clefs/valeurs de variables du template
     */
	public function set($_array = array())
	{
		if(empty($_array)) exit();
		$this->variables = array_merge($this->variables,$_array);
	}
	
	/**
     *	setLoop : ajoute une liste de variables complexes et répétitives et leurs valeurs au template
     *
     * @param array $_array : tableau clefs/valeurs de variables du template
     */
	public function set_loop($_array = array())
	{
		if(empty($_array)) exit();
		$this->loops = $_array;
	}
	
	/**
     *	display : affiche le template
     */
	public function display()
	{
		if (!$this->clear) $this->parse();
		$this->clear();
		echo $this->page;
	}

	/**
     *	clear : supprime tout les identificateurs de variables
     */
	public function clear()
	{
		$this->page = preg_replace('`{{.*}}`','',$this->page);
		$this->clear = true;
	}
	
	/**
     *	parse : parcours le buffer et change les identificateurs de variables par leurs valeurs
     */
	private function parse()
	{
		foreach($this->variables as $name => $value)
		{
			$this->page = preg_replace('`{{'.$name.'}}`',$value,$this->page);
		}
		foreach($this->loops as $name => $value)
		{
			$nb = count($value);
			$block = '';
			for ($i = 0; $i < $nb; $i++) {
				$tab_page = explode("\n", $this->page);
				for ($k = 0, $kmax = count($tab_page); $k < $kmax; $k++) $tab_page[$k] = trim($tab_page[$k]);
				$startTag = (array_search('{{BEGIN.'.$name.'}}', $tab_page)) + 1;
				$endTag = (array_search('{{END.'.$name.'}}', $tab_page)) - 1;
				$lengthTag = ($endTag - $startTag) + 1;
				$blockTag = array_slice($tab_page, $startTag, $lengthTag);
				$blockTag = implode("\n", $blockTag);
				foreach($value[$i] as $constant => $data) {
					$data = (file_exists($data)) ? $this->includeFile($data) : $data;;
					$blockTag = preg_replace('`{{'.$constant.'.'.$name.'}}`', $data, $blockTag);
				}
				$block = ($block == '') ? $blockTag : $block."\n".$blockTag;
			}
			$block = explode ("\n", $block);
			$firstPart = array_slice($tab_page, 0, $startTag - 1);
			$secondPart = array_slice($tab_page, $startTag + $lengthTag + 1);
			$this->page = array_merge($firstPart, $block, $secondPart);
			for ($i = 0, $imax = count($this->page); $i < $imax; $i++) $this->page[$i] = html_entity_decode($this->page[$i]);
			$this->page = implode("\n", $this->page);
		}
	}
}
?>