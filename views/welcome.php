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
							<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab"><?php echo _("Settings"); ?></a></li>
						</ul>
						<div class="tab-content display">
							<div role="tabpanel" id="users" class="tab-pane active">
								<div class="container-fluid">
									<div class="table-responsive">
										<div id="toolbar-users">
											<?php if($permissions['addUser']) {?>
											<a href="config.php?display=userman&amp;action=adduser" id="add-users" class="btn btn-danger btn-add" data-type="users" data-section="users">
												<i class="fa fa-user-plus"></i> <span><?php echo _('Add')?></span>
											</a>
											<?php } ?>
											<?php if($permissions['removeUser']) {?>
											<button id="remove-users" class="btn btn-danger btn-remove" data-type="users" disabled data-section="users">
												<i class="fa fa-user-times"></i> <span><?php echo _('Delete')?></span>
											</button>
											<?php } ?>
										</div>
										<table data-toolbar="#toolbar-users" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped" id="table-users">
											<thead>
												<tr>
													<th data-checkbox="true"></th>
													<th data-sortable="true" data-field="id"><?php echo _("ID") ?></th>
													<th data-sortable="true"><?php echo _("Username") ?></th>
													<th data-sortable="true"><?php echo _("Display Name") ?></th>
													<th data-sortable="true"><?php echo _("Extension") ?></th>
													<th data-sortable="true"><?php echo _("Description") ?></th>
													<th><?php echo _("Action") ?></th>
												</tr>
											</thead>
											<tbody>
												<?php foreach($users as $row){ ?>
													<tr id = "row<?php echo $row['id']?>">
														<td></td>
														<td><?php echo $row['id']?></td>
														<td><?php echo $row['username']?></td>
														<td><?php echo $row['displayname']?></td>
														<td><?php echo $row['default_extension']?></td>
														<td><?php echo $row['description']?></td>
														<td>
															<a href="config.php?display=userman&amp;action=showuser&amp;user=<?php echo $row['id']?>">
															<i class="fa fa-edit"></i></a>&nbsp;&nbsp;
															<?php if($permissions['changePassword']) { ?>
																<a data-toggle="modal" data-pwuid="<?php echo $row['id']?>" data-target="#setpw" id="pwmlink<?php echo $row['id']?>">
																	<i class="fa fa-key"></i></a>&nbsp;&nbsp;
															<?php } ?>
															<?php if($permissions['removeUser']) { ?>
																<a href="#" id="del<?php echo $row['id']?>" data-uid="<?php echo $row['id']?>" >
																	<i class="fa fa-trash-o"></i>
																</a>
															<?php } ?>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div role="tabpanel" id="groups" class="tab-pane">
								<div class="container-fluid">
									<div class="table-responsive">
										<div id="toolbar-groups">
											<?php if($permissions['addGroup']) {?>
											<a href="config.php?display=userman&amp;action=addgroup" id="add-groups" class="btn btn-danger btn-add" data-type="groupss" data-section="groups">
												<i class="fa fa-user-plus"></i> <span><?php echo _('Add')?></span>
											</a>
											<?php } ?>
											<?php if($permissions['removeGroup']) {?>
											<button id="remove-groups" class="btn btn-danger btn-remove" data-type="groups" disabled data-section="groups">
												<i class="fa fa-user-times"></i> <span><?php echo _('Delete')?></span>
											</button>
											<?php } ?>
										</div>
										<table data-toolbar="#toolbar-groups" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped" id="table-groups">
											<thead>
												<tr>
													<th data-checkbox="true"></th>
													<th data-sortable="true" data-field="id"><?php echo _("ID") ?></th>
													<th data-sortable="true"><?php echo _("Group Name") ?></th>
													<th data-sortable="true"><?php echo _("Description") ?></th>
													<th data-sortable="true"><?php echo _("Priority") ?></th>
													<th data-sortable="true"><?php echo _("Action") ?></th>
												</tr>
												</thead>
												<tbody>
													<?php foreach($groups as $row){ ?>
														<tr id = "grow$uid">
														<td></td>
														<td><?php echo $row['id']?></td>
														<td><?php echo $row['groupname']?></td>
														<td><?php echo $row['description']?></td>
														<td><?php echo $row['priority']?></td>
														<td class="actions"><a href="config.php?display=userman&amp;action=showgroup&amp;group=<?php echo $row['id']?>">
															<i class="fa fa-edit"></i></a>&nbsp;&nbsp;
															<?php if($permissions['removeGroup']) { ?>
																<a href="#" id="gdel<?php echo $row['id']?>" data-uid="<?php echo $row['id']?>" ><i class="fa fa-trash-o"></i></a>
															<?php } ?>
															</td>
														</tr>
													<?php } ?>
												</tbody>
											</table>
										</div>
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
			<?php echo load_view(dirname(__FILE__).'/rnav.php',array("users"=>$users,"permissions"=>$permissions)); ?>
		</div>
	</div>
</div>
