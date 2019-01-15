<!DOCTYPE html>
<html>
<head>
	<title>OSRS Quest Calculator</title>

	<link rel="stylesheet" href="qc.css" />
</head>
<body>
	<div id="main-container">
		<div id="side-panel" class="split-panel">
			<div id="side-panel-contents">
				<div id="title">OSRS Quest Calculator</div>

				<div id="skills-container">
					<div class="subtitle">Skills</div>

					<div id="skill-inputs">
						Loading skills...
						<!-- Fill in from JS -->
					</div>

					<div id="last-skills-line">
						<div id="combat-level-display">
							<img src="img/skill_icons/combat.png"></img>
							<span id="combat-level">3</span>
						</div>
						<div id="update-button-container">
							<button id="update-button">UPDATE</button>
						</div>
					</div>
				</div>

				<div id="filters-container">
					<div class="subtitle">Show...</div>
					
					<div id="filters">
						Loading Filters...
						<!-- Fill in from JS -->
					</div>
				</div>
			</div>

			<div id="side-panel-footer">
				Quest info from <a href="https://oldschool.runescape.wiki">OSRS Wiki</a><br>
				Site by <a href="https://twitter.com/Vazkii/">Vazkii</a>, source @ <a href="https://github.com/Vazkii/osrsqc">github</a><br>
				Not affiliated with Jagex or OSRS Wiki.
			</div>
		</div>
		<div id="main-panel" class="split-panel">
			<div id="quest-list-container">
				<div id="quest-list">
					Loading quests...
					<!-- Fill in from JS -->
				</div>
				<div id="no-quests">No quests to show :(</div>
			</div>
		</div>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/mustache.js/2.1.3/mustache.js"></script>
	<script src="qc.js"></script>
</body>


</html>
