<?php
	define('SITE_PREFIX', 'https://oldschool.runescape.wiki');
	define('BASE_URL', SITE_PREFIX . '/w/Quests/List');

	define('URL', 'url');
	define('NAME', 'name');
	define('REQS', 'requirements');
	define('MEMBERS', 'members');
	define('QUESTS', 'quests');
	define('LEVELS', 'levels');
	define('SKILL', 'skill');
	define('LEVEL', 'level');
	define('BOOSTABLE', 'boostable');

	set_time_limit(0);
	libxml_use_internal_errors(true);

	$doc = DOMDocument::loadHTMLFile(BASE_URL);
	$quest_list = load_all_quests($doc);
	usort($quest_list, "cmp_quest_name");
	print_quest_list($quest_list);

	// =============== FUNCTIONS ===============

	function load_all_quests($doc) {
		$quests = array();
		$members = false;

		$tables = $doc->getElementsByTagName('table');
		foreach($tables as $table) {
			$trs = $table->getElementsByTagName('tr');
			if(sizeof($trs) == 7) // quest difficulty listings
				continue;

			foreach($trs as $tr) {
				$as = $tr->getElementsByTagName('a');
				if(sizeof($as) == 0)
					continue;

				if(strpos($as[0]->nodeValue, '/') > -1)
					continue;

				$quest = array();
				$quest[NAME] = $as[0]->nodeValue;

				$quest[URL] = SITE_PREFIX . $as[0]->getAttribute('href');
				$quest[MEMBERS] = $members;

				echo "LOADING " . $quest[NAME] . "\n";
				$quest[REQS] = get_quest_reqs($quest[NAME], $quest[URL]);

				array_push($quests, $quest);
			}

			$members = true;
		}

		return $quests;
	}

	function get_quest_reqs($name, $url) {
		$cache_loc = 'cache/' . str_replace('/', '-', $name) . '.html';
		$cached = false;
		if(file_exists($cache_loc)) {
			$url = $cache_loc;
			$cached = true;
		}

		$contents = file_get_contents($url);
		if(!$cached) {
			file_put_contents($cache_loc, $contents);
			echo "Cached to $cache_loc...\n";
		}
		$doc = DOMDocument::loadHTML($contents);

		$tables = $doc->getElementsByTagName('table');
		foreach($tables as $table) {
			$class = $table->getAttribute('class');
			if(strpos($class, 'questdetails') > -1) {
				$trs = $table->getElementsByTagName('tr');
				foreach($trs as $tr) {
					$tds = $tr->getElementsByTagName('td');
					if(sizeof($tds) == 2 && strpos($tds[0]->nodeValue, 'Requirements') > -1)
						return load_quest_reqs($tds[1]);
				}
			}
		}

		return array();
	}

	function load_quest_reqs($holder) {
		$quests = array();
		$levels = array();

		$startedlevels = false;
		$donelevels = false;

		$lis = $holder->getElementsByTagName('li');
		foreach($lis as $li) {
			$txt = $li->nodeValue;

			if(is_numeric($txt[0]) && !$donelevels) {
				$startedlevels = true;
				echo "TXT: [$txt]\n";

				$txt = str_replace(" ", ' ', $txt);
				$txt = str_replace("  ", '/', $txt);
				$txt = str_replace("\n", ' ', $txt);
				$boostable = strpos(strtolower($txt), 'boost') > -1;

				if(strpos($txt, ' '))
					$txt = substr($txt, 0, strpos($txt, ' '));

				echo "FIXED TXT: [$txt]\n";

				$div = strpos($txt, '/');
				if($div > 0) {
					$skillname = substr($txt, $div + 1);
					$req =  substr($txt, 0, $div);

					$obj = array();
					$obj[SKILL] = strtolower($skillname);
					$obj[LEVEL] = (int) $req;
					$obj[BOOSTABLE] = $boostable;
					array_push($levels, $obj);	
				}
			} else if($startedlevels)
				$donelevels = true;

			if(strpos(strtolower($txt), 'following quests') > -1)
				$quests = load_quest_req_list($li, $quests);
		}

		$reqs = array();
		$reqs[QUESTS] = $quests;
		$reqs[LEVELS] = $levels;

		return $reqs;
	}

	function load_quest_req_list($holder, $quests) {
		$children = $holder->childNodes;
		if($children->length > 0)
			$children = $children->item(1)->childNodes;

		foreach($children as $child) {
			$txt = $child->nodeValue;
			echo "TXT: [$txt]\n";

			$newline = strpos($txt, "\n");
			if($newline > -1)
				$txt = substr($txt, 0, $newline);
			$parentsis = strpos($txt, "(");
			if($parentsis > -1)
				$txt = substr($txt, 0, $parentsis);

			echo "FIXED TXT: [$txt]\n";

			if(strlen($txt))
				array_push($quests, $txt);
		}

		return $quests;
	}

	function print_quest_list($quest_list) {
		$json = json_encode(utf8ize($quest_list), JSON_PRETTY_PRINT);
		file_put_contents('quests.json', $json);
		echo "\nDone!\n";
	}

	function cmp_quest_name($q1, $q2) {
		return strcmp($q1[NAME], $q2[NAME]);
	}

	// https://stackoverflow.com/a/19366999
	function utf8ize($d) {
	    if (is_array($d)) {
	        foreach ($d as $k => $v) {
	            $d[$k] = utf8ize($v);
	        }
	    } else if (is_string ($d)) {
	        return utf8_encode($d);
	    }
	    return $d;
	}

?>	

