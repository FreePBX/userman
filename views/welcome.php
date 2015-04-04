<?php
if(!empty($message)){
	$htmlmessage = '<div class="alert alert-' . $message['type'] . ' fade">' . $message['message'] . '</div>';
}
echo $htmlmessage;
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-9">
			<div class="fpbx-container">
				 <div class="display no-border">
					<h1><?php echo _("User Manager")?></h1>
					<div class="panel panel-info">
						<div class="panel-heading">
							<div class="panel-title">
								<a href="#" data-toggle="collapse" data-target="#moreinfo"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _("What is User Manager")?>
							</div>
						</div>
						<!--At some point we can probably kill this... Maybe make is a 1 time panel that may be dismissed-->
						<div class="panel-body collapse" id="moreinfo">
							<p><?php echo sprintf(_('%s User Manager is taking the place of several modules which have attempted to create and manage users separate from Extensions. Modules such as iSymphony and RestAPI are examples of these type of modules. In %s 12, the new User Control Panel also uses User Manager.'),DASHBOARD_FREEPBX_BRAND,DASHBOARD_FREEPBX_BRAND)?></p>
							<p><?php echo sprintf(_('In %s User Manager you can create users that have access to Extensions or Device/User Mode Users and the settings associated with those Devices. For example, a new user can be created that can log into User Control Panel and access the voicemail of 3 other accounts.'),DASHBOARD_FREEPBX_BRAND)?></p>
						</div>
					</div>
					<div role="tabpanel">
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#users" aria-controls="users" role="tab" data-toggle="tab"><?php echo _("Users"); ?></a></li>
							<li role="presentation"><a href="#groups" aria-controls="groups" role="tab" data-toggle="tab"><?php echo _("Groups"); ?></a></li>
							<li role="presentation"><a href="#permissions" aria-controls="groups" role="tab" data-toggle="tab"><?php echo _("Permissions"); ?></a></li>
							<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab"><?php echo _("Settings"); ?></a></li>
						</ul>
						<div class="tab-content display">
							<div role="tabpanel" id="users" class="tab-pane active">
								<div class="container-fluid">
									<?php echo load_view(dirname(__FILE__).'/usergrid.php'); ?>
								</div>
							</div>
							<div role="tabpanel" id="groups" class="tab-pane">
								<div class="container-fluid">
									<?php echo load_view(dirname(__FILE__).'/groupgrid.php'); ?>
								</div>
							</div>
							<div role="tabpanel" id="permissions" class="tab-pane">
								<div class="container-fluid">
									<?php echo load_view(dirname(__FILE__).'/permissionsgrid.php'); ?>
								</div>
							</div>
							<div role="tabpane" id="settings" class="tab-pane">
								<div class="container-fluid">
									<?php echo load_view(dirname(__FILE__).'/general.php'); ?>
								</div>
							</div>
						</div>
					</div>
					<?php echo load_view(dirname(__FILE__).'/passwordmodal.php')?>
				</div>
			</div>
		</div>
		<div class="col-sm-3 hidden-xs bootnav">
			<?php echo load_view(dirname(__FILE__).'/rnav.php',array("users"=>$users)); ?>
		</div>
	</div>
</div>
