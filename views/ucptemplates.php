<?php
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showucptemplate'){
	$heading = '<h1>' . _("Edit Template") . '</h1>';
}else{
	$heading = '<h1>' . _("Add Template") . '</h1>';
}
echo $heading;
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<div class="display full-border">
					<form autocomplete="off" class="fpbx-submit" id="editT" name="editT" action="config.php?display=userman#ucptemplates" method="post" <?php if(!empty($template['id'])) {?>data-fpbx-delete="config.php?display=userman&amp;action=delucptemplate&amp;template=<?php echo $template['id']?>"<?php } ?> onsubmit="return">
						<input type="hidden" name="type" value="ucptemplate">
						<input type="hidden" name="submittype" value="gui">
						<input type="hidden" name="id" value="<?php echo !empty($template['id']) ? $template['id'] : ''?>">
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="">
										<div class="form-group row">
											<div class="col-md-3">
												<label class="control-label" for="templatename"><?php echo _("Template Name")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="templatename"></i>
											</div>
											<div class="col-md-9">
												<input class="form-control" id="templatename" name="templatename" value="<?php echo !empty($template['templatename']) ? $template['templatename'] : ''?> ">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="templatename-help" class="help-block fpbx-help-block"><?php echo _("The Template name")?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="">
										<div class="form-group row">
											<div class="col-md-3">
												<label class="control-label" for="description"><?php echo _("Template Description")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
											</div>
											<div class="col-md-9">
												<input class="form-control" id="description" name="description" value="<?php echo !empty($template['description']) ? $template['description'] : ''?>">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="description-help" class="help-block fpbx-help-block"><?php echo _("The Template description")?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="display"  id="create">
								<div class="element-container">
									<div class="">
										<div class="form-group row">
											<div class="col-md-6">
												<label class="control-label" for="createtemp">
													<?php echo _("Create Template Via") ?>
												</label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="createtemp"></i>
											</div>
											<div class="col-md-6">
												<span class="radioset">
													<input type="radio" name="createtemp" id="createtemp_import" value="import" CHECKED>
													<label for="createtemp_import">
													<?php echo _("Import from a User");?>
													</label>
													<input type="radio" name="createtemp" id="createtemp_create" value="create" >
													<label for="createtemp_create">
														<?php echo _("Create Using Template Creator");?>
													</label>
												</span>
											</div>
											<div class="row">
												<div class="col-md-12">
													<span id="createtemp-help" class="help-block fpbx-help-block">
													<?php echo _("You can create a template via copy existing settings from a user Or You can create the template via help of template creator") ?>
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="element-container" id="adminextdiv">
									<div class="row">
										<div class="col-md-12">
											<div class="">
												<div class="form-group row">
													<div class="col-md-3">
														<label class="control-label" for="group_users"><?php echo _('Import from a User')?></label>
															<i class="fa fa-question-circle fpbx-help-icon" data-for="group_users"></i>
													</div>
													<div class="col-md-9">
														<select id="userid" class="form-control" name="userid" >
															<option value=""><?php echo _("Select a User")?></option>
															<?php foreach($users as $user) {?>
															<option value="<?php echo $user['id']?>"><?php echo $user['username']?></option>
															<?php } ?>
														</select>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<span id="group_users-help" class="help-block fpbx-help-block"><?php echo _("Import this user's  current dashbord setting from UCP")?></span>
										</div>
									</div>
								</div>
								<div class="element-container" id="tempcreatediv">
									<div class="row">
										<div class="col-md-12">
											<div class="">
												<div class="form-group row">
													<div class="col-md-12">
														<label for="createtemp_create"><?php
														echo _("This will create a empty template now , you need to click on `eye` button once you submit this page, That will take you to UCP login where you can add template widgets ");
													?></div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<span id="group_users-help" class="help-block fpbx-help-block"><?php echo _("Import this user's  current dashbord setting from UCP")?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
