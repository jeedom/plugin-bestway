<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('bestway');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_syncBestway">
				<i class="fas fa-sync-alt"></i>
				<br />
				<span>{{Synchroniser}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes équipements Bestway}}</legend>
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
				<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
			</div>
		</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br />
				<div class="row">
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Nom de l'équipement Bestway}}</label>
									<div class="col-sm-3">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Objet parent}}</label>
									<div class="col-sm-3">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											$options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
												$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Catégorie}}</label>
									<div class="col-sm-9">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
										?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label"></label>
									<div class="col-sm-9">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Activer filtration auto}}</label>
									<div class="col-sm-6">
										<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="filter::auto" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Chauffage, durée max autorisé (en min,0 pour illimité)}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="heating::maxDuration" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Me prévenir quand mon SPA est à température}}</label>
									<div class="col-sm-3">
										<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="notify::targetTemp" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Commande d'information}}</label>
									<div class="col-sm-6">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="info::cmd" />
											<span class="input-group-btn">
												<a class="btn btn-default listCmdAction roundedRight" data-subType="message"><i class="fas fa-list-alt"></i></a>
											</span>
										</div>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{MAC}}</label>
									<div class="col-sm-6">
										<span class="eqLogicAttr label label-info" data-l1key="configuration" data-l2key="mac" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Wifi version}}</label>
									<div class="col-sm-6">
										<span class="eqLogicAttr label label-info" data-l1key="configuration" data-l2key="wifi_soft_version" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ID}}</label>
									<div class="col-sm-6">
										<span class="eqLogicAttr label label-info" data-l1key="logicalId" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Modèle}}</label>
									<div class="col-sm-6">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="device">
											<option value="">{{Aucun}}</option>
											<?php
											foreach (bestway::devicesParameters() as $key => $info) {
												echo '<option value="' . $key . '">[' . $key . '] ' . $info['name'] . '</option>';
											}
											?>
										</select>
									</div>
								</div>
								<center>
									<img src="<?php echo $plugin->getPathImgIcon() ?>" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;" onerror="this.src='<?php echo $plugin->getPathImgIcon() ?>'" />
								</center>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br /><br />
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th>
							<th>{{Type}}</th>
							<th>{{Etat}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>

<?php include_file('desktop', 'bestway', 'js', 'bestway'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>