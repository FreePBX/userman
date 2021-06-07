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
									<div class="row">
										<div class="form-group">
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
									<div class="row">
										<div class="form-group">
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
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="group_users"><?php echo _('Import from Users')?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="group_users"></i>
											</div>
											<div class="col-md-9">
												<select id="userid" class="form-control" name="userid" >
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
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
