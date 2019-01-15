var debug = false;

var filters = {
	'show-p2p': new Filter(true, 'show-p2p', 'P2P Quests',
		function(quest) {
			return !quest.members;
		}),

	'show-cant-do': new Filter(true, 'show-cant-do', 'Missing Requirements',
		function(quest) {
			if(!quest.requirements)
				return true;

			if(quest.requirements.levels)
				for(i in quest.requirements.levels) {
					var req = quest.requirements.levels[i];
					var skill = req.skill;
					var level = req.level;
					if(skills[skill] < level)
						return false;
				}

			if(quest.requirements.quests)
				for(i in quest.requirements.quests) {
					var reqQuest = quest.requirements.quests[i];
					if(reqQuest in questsByName && completedQuests.indexOf(reqQuest) < 0)
						return false;
				}

			return true;
		}),

	'show-completed': new Filter(true, 'show-completed', 'Completed Quests',
		function(quest) {
			return completedQuests.indexOf(quest.name) < 0;
		})
};

var skills = {
	'attack': 1,
	'hitpoints': 10,
	'mining': 1,
	'strength': 1,
	'agility': 1,
	'smithing': 1,
	'defence': 1,
	'herblore': 1,
	'fishing': 1,
	'ranged': 1,
	'thieving': 1,
	'cooking': 1,
	'prayer': 1,
	'crafting': 1,
	'firemaking': 1,
	'magic': 1,
	'fletching': 1,
	'woodcutting': 1,
	'runecrafting': 1,
	'slayer': 1,
	'farming': 1,
	'construction': 1,
	'hunter': 1,
	'quest': 1,
	'combat': 3,
}

var quests = [];
var questsByName = {};
var completedQuests = [];

$(function() {
	console.log('Reading this console is XP waste.');

	loadCookies();
	computeCombat();

	loadSkillInput();
	loadFilters();
	loadQuests();

	$(document).on('input', '.input-level', function(i) {
		var skill = $(this).attr('data-skill');
		var level = $(this).val();

		if(level == '' || level == 0)
			level = 1;

		if(typeof level == 'string')
			level = parseInt(level);

		skills[skill] = level;
		$.cookie('skill_' + skill, level);
		computeCombat();
	});

	$('.filter-checkbox').change(function() {
		var key = $(this).attr('data-key');
		filters[key].enabled = $(this).is(":checked");
		$.cookie('filter_' + key, filters[key].enabled);

		applyFilters();
	});

	$(document).on('click', '.quest-check', function() {
		var enabled = $(this).hasClass('quest-complete');

		var parent = $(this).parent().parent();
		var quest = parent.attr('data-name');
		var index = parent.attr('data-index');

		$.cookie('quest_' + index, !enabled);

		if(enabled) {
			$(this).removeClass('quest-complete');
			$(this).find('.quest-cm0').removeClass('quest-cm0c');
			$(this).find('.quest-cm1').removeClass('quest-cm1c');

			setTimeout(function() {
				completedQuests.splice(completedQuests.indexOf(quest));
				applyFilters();
			}, 300);
		} else {
			$(this).addClass('quest-complete');
			$(this).find('.quest-cm0').addClass('quest-cm0c');
			$(this).find('.quest-cm1').addClass('quest-cm1c');

			setTimeout(function() {
				completedQuests.push(quest);
				applyFilters();
			}, 300);
		}
	});

	$('#update-button').click(applyFilters);

	checkBadQuestReqs();
});

function computeCombat() {
	var base = 0.25 * (skills['defence'] + skills['hitpoints'] + Math.floor(skills['prayer'] / 2));
	var melee = 0.325 * (skills['attack'] + skills['strength']);
	var range = 0.325 * (Math.floor(skills['ranged'] / 2) + skills['ranged']);
	var magic = 0.325 * (Math.floor(skills['magic'] / 2) + skills['magic']);
	var combat = Math.floor(base + Math.max(melee, range, magic));

	$('#combat').text(combat);
	skills['combat'] = combat;
}

function shouldQuestBeVisible(quest) {
	var visible = true;
	for(key in filters) {
		var filter = filters[key];
		if(!filter.enabled)
			visible = visible && filter.predicate(quests[quest.attr('data-index')]);
	}

	return visible
}

function checkBadQuestReqs() {
	if(!debug)
		return;

	for(i in quests) {
		var quest = quests[i];
		for(j in quest.requirements.quests) {
			var req = quest.requirements.quests[j];
			if(!(req in questsByName))
				console.log('Bad quest requirement "' + req + '" in quest "' + quest.name + '"');
		}
	}
}

function applyFilters() {
	var hasVisibleQuests = false;
	$(document).find('.quest').each(function(i) {
		var visible = shouldQuestBeVisible($(this));

		if(visible) {
			hasVisibleQuests = true;
			var defaultHeight = $(this).attr('data-height');
			$(this).show();
			$(this).animate({
				'height': defaultHeight,
				'margin-top': '10px',
				'margin-right': '10px',
				'margin-bottom': '10px',
				'padding': '5px'
			}, 200, function() {
				$(this).animate({
         			"margin-left": '10px'
         		}, 200);
			});
		} else $(this).animate({
         		"margin-left": '-1000px'
         	}, 300, function() {
         		$(this).animate({
         			"height": '0px',
         			'margin-top': '0px',
					'margin-right': '0px',
					'margin-bottom': '0px',
					'padding': '0px'
         		}, 200, function() {
         			$(this).hide();
         		});
         	});
	});

	setTimeout(function() {
		if(hasVisibleQuests)
			$('#no-quests').fadeOut();
		else $('#no-quests').fadeIn();
	}, 400);
}

function loadSkillInput() {
	var templateSkillInput = loadTemplate('skill-input');

	var skillInputsHtml = '';
	var i = 0;
	for(skill in skills) {
		if(skill == 'combat') {
			skillInputsHtml += '</div>';
			break;
		}

		if(i % 3 == 0) {
			if(i > 0)
				skillInputsHtml += '</div>';
			skillInputsHtml += '<div class="skills-line">';
		}

		var current = skills[skill];
		var maxchars = (skill == 'quest' ? 3 : 2);

		skillInputsHtml += Mustache.to_html(templateSkillInput, {
			'skill': skill,
			'maxchars': maxchars,
			'current': current
		});

		i++;
	}

	$('#skill-inputs').html(skillInputsHtml);
}

function loadFilters() {
	var templateFilter = loadTemplate('filter');

	var filterHtml = '';
	for(key in filters) {
		var filter = filters[key];
		filterHtml += Mustache.to_html(templateFilter, {
			'key': filter.key,
			'desc': filter.desc,
			'checked': filter.enabled
		});
	}

	$('#filters').html(filterHtml);
}

function loadQuests() {
	var templateQuest = loadTemplate('quest');

	$.getJSON('quests.json', function(data) {
		var str = '';
		
		data.forEach(function(quest, i) {
			quest['index'] = i;
			quest['has-levels'] = quest.requirements && quest.requirements.levels && quest.requirements.levels.length;
			quest['has-quests'] = quest.requirements && quest.requirements.quests && quest.requirements.quests.length;
			quest['has-requirements'] = quest['has-levels'] || quest['has-quests'];
			quest['done'] = $.cookie('quest_' + i);
			if(quest.done)
				completedQuests.push(quest.name);

			questsByName[quest['name']] = quest;
			quests.push(quest);

			str += Mustache.to_html(templateQuest, quest);
		});

		$('#quest-list').html(str);

		$('.quest').each(function(i) {
			$(this).attr('data-height', $(this).height());
		});

		checkBadQuestReqs();
		applyFilters();
	});
}

function loadCookies() {
	var cookies = $.cookie();
	for(i in filters) {
		var key = 'filter_' + i;
		if(key in cookies)
			filters[i].enabled = (cookies[key] == 'true');
	}

	for(i in skills) {
		var key = 'skill_' + i;
		if(key in cookies)
			skills[i] = parseInt(cookies[key]);
	}
}

function loadTemplate(name) {
	var templateData = '';

	$.ajax({
		url: 'templates/' + name + '.html',
		async: false,
		success: function(data) {
			templateData = data;
		}
	});

	return templateData;
}

function Filter(enabled, key, desc, predicate) {
	this.enabled = enabled;
	this.key = key;
	this.desc = desc;
	this.predicate = predicate;
}