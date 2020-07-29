<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$allObject = jeeObject::buildTree();
sendVarToJs('object_id', init('object_id'));
$graphdata = array();
sendVarToJs('jeedomBackgroundImg', 'plugins/bestway/core/img/panel.jpg');
$graphData['day'] = array('start' => date('Y-m-d', strtotime('now -3 month')), 'end' => date('Y-m-d', strtotime('now')));
?>
<div class="row row-overflow" id="div_watering">
	<div class="col-lg-2 reportModeHidden">
		<div class="bs-sidebar">
			<ul id="ul_object" class="nav nav-list bs-sidenav">
				<li class="nav-header">{{Liste objets}}</li>
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
				<?php
				foreach ($allObject as $object_li) {
					if ($object_li->getIsVisible() != 1 || count($object_li->getEqLogic(true, false, 'watering', null, true)) == 0) {
						continue;
					}
					$margin = 5 * $object_li->parentNumber();
					if ($object_li->getId() == init('object_id')) {
						echo '<li class="cursor li_object active" ><a data-object_id="' . $object_li->getId() . '" href="index.php?v=d&p=panel&m=bestway&object_id=' . $object_li->getId() . '" style="padding: 2px 0px;"><span style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</span></a></li>';
					} else {
						echo '<li class="cursor li_object" ><a data-object_id="' . $object_li->getId() . '" href="index.php?v=d&p=panel&m=bestway&object_id=' . $object_li->getId() . '" style="padding: 2px 0px;"><span style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</span></a></li>';
					}
				}
				?>
			</ul>
		</div>
	</div>
	<?php
	if (init('report') != 1) {
		echo '<div class="col-lg-10">';
	} else {
		echo '<div class="col-lg-12">';
	}
	$bestways = bestway::generatePanel('dashboard',init('object_id',null),init('period', config::byKey('savePeriod', 'bestway','D')));
	echo $bestways['period'];
	$graphs = $bestways['graphData'];
	foreach ($bestways['bestways'] as $bestway) {
		echo '<legend>'.$bestway['eqLogic']['name'].'</legend>';
		echo '<div class="row">';
		echo '<div class="col-lg-5 col-sm-6 col-xs-6 div_eqLogicBestway">';
		echo $bestway['html'];
		echo '</div>';
		echo '<div class="col-lg-7 col-sm-6 col-xs-6">';
		echo '<div id="div_chartBestway'.$bestway['eqLogic']['id'].'"></div>';
		echo '</div>';
		echo '</div>';
		$graphs[$bestway['eqLogic']['id']] = $bestway['graph'];
	}
	?>
</div>
</div>
<?php sendVarToJs('bestway_graphs', $graphs);?>
<?php include_file('desktop', 'panel', 'js', 'bestway');?>
