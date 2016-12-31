<?php
	define('ENABLED', false);

	if(!ENABLED)
		die(); 

	define('RUNEHQ_URL', 'http://www.runehq.com/oldschoolquest');

	define('URL', 'url');
	define('NAME', 'name');
	define('REQS', 'requirements');
	define('MEMBERS', 'members');
	define('QUESTS', 'quests');
	define('LEVELS', 'levels');
	define('OTHERS', 'others');
	define('SKILL', 'skill');
	define('LEVEL', 'level');
	define('BOOSTABLE', 'boostable');

	set_time_limit(0);
	libxml_use_internal_errors(true);

	$doc = DOMDocument::loadHTMLFile(RUNEHQ_URL);
	$quest_list = load_all_quests($doc);
	print_quest_list($quest_list);

	// =============== FUNCTIONS ===============

	function load_all_quests($doc) {
		$quest_list = array();
		$trs = $doc->getElementsByTagName('tr');

		foreach($trs as $tr) {
			$links = $tr->getElementsByTagName('a');
			foreach($links as $a) {
				$parent = $a->parentNode->parentNode;
				$inner = get_inner_html($parent);
				$members = strpos($inner, 'Members</td>') > 0;

				$q = load_quest($a, $members);
				array_push($quest_list, $q);
			}
		}

		return $quest_list;
	}

	function load_quest($node, $members) {
		$q = array();
		$q[NAME] = $node->nodeValue;
		$q[URL] = 'http://www.runehq.com' . $node->getAttribute('href');
		$q[MEMBERS] = $members;

		$doc = DOMDocument::loadHTMLFile($q[URL]);
		$reqs = get_quest_reqs($doc);
		$q[REQS] = $reqs;

		return $q;
	}

	function get_quest_reqs($doc) {
		$divs = $doc->getElementsByTagName('div');

		$guide_divs = array();
		foreach($divs as $div) {
			$class = $div->getAttribute('class');
			if(strpos($class, 'guide') !== false)
				array_push($guide_divs, $div);
		}

		$quest_reqs = $guide_divs[3];
		$other_reqs = $guide_divs[4];

		$quests = array();
		$levels = array();
		$others = array();

		$quest_list = $quest_reqs->getElementsByTagName('a');
		foreach($quest_list as $q)
			array_push($quests, $q->nodeValue);

		$tokens = preg_split("#<br/?>#", get_inner_html($other_reqs));

		$skill_regex = "/^(?:level\s)?(\d{1,2})\s([A-Za-z]+|(?:quest points)|(?:combat level))(?:\s\(boostable\))?\.?$/i";
		$link_regex = "/\<a href\=\".+\">([A-Za-z]+)\<\/a\>/";

		foreach($tokens as $t) {
			$filtered = str_replace(array("\n", "\r", '&#13;'), '', $t);
			$filtered = preg_replace($link_regex, "$1", $filtered);
			$matches = array();

			if(preg_match($skill_regex, $filtered, $matches)) {
				$skill_obj = array();
				$skill_obj[LEVEL] = (int) $matches[1];
				$skill_obj[SKILL] = str_replace(' ', '_', strtolower($matches[2]));
				if(strpos($filtered, 'Boostable') > 0)
					$skill_obj[BOOSTABLE] = true;

				array_push($levels, $skill_obj);
			} 
			else array_push($others, $filtered);
		}

		$reqs = array();
		$reqs[QUESTS] = $quests;
		$reqs[LEVELS] = $levels;
		$reqs[OTHERS] = $others;

		return $reqs;
	}

	function print_quest_list($quest_list) {
		echo "<pre>" . json_encode($quest_list, JSON_PRETTY_PRINT) . "</pre>";
	}

	// Source: https://secure.php.net/manual/en/class.domelement.php#101243
	function get_inner_html($node) {
	    $innerHTML= '';
	    $children = $node->childNodes;
	    foreach ($children as $child)
	        $innerHTML .= $child->ownerDocument->saveXML($child);
	    
	    return $innerHTML;
	} 

?>	

