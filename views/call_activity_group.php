<?php
$call_activity_group_user_limit = $callActivityUserLimit? $callActivityUserLimit : 50;
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showcallactivitygroup'){
	$heading = '<h1>' . _("Edit Call Activity Group") . '</h1>';
}else{
	$heading = '<h1>' . _("Add Call Activity Group") . '</h1>';
}
echo $heading;
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<div class="display full-border">
					<form autocomplete="off" class="fpbx-submit" id="editCallActivityGroup" name="editCallActivityGroup" action="config.php?display=userman#callactivitygroups" method="post" <?php if(!empty($callactivitygroup['id'])) {?>data-fpbx-delete="config.php?display=userman&amp;action=delcallactivitygroup&amp;callactivitygroup=<?php echo $callactivitygroup['id']?>"<?php } ?> onsubmit="return">
						<input type="hidden" name="type" value="callactivitygroup">
						<input type="hidden" name="submittype" value="gui">
						<input type="hidden" name="id" value="<?php echo !empty($callactivitygroup['id']) ? $callactivitygroup['id'] : ''?>">
                        <input type="hidden" name="users" value="<?php echo !empty($callactivitygroup['usersarray']) ? implode(",",$callactivitygroup['usersarray']) : '' ; ?>" id="call_activity_users">
						<input type="hidden" name="users_limit" value="<?php echo $call_activity_group_user_limit; ?>" id="call_activity_user_limit">
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="callactivitygroupname"><?php echo _("Group Name")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="callactivitygroupname"></i>
											</div>
											<div class="col-md-9">
												<input class="form-control" id="callactivitygroupname" name="callactivitygroupname" value="<?php echo !empty($callactivitygroup['groupname']) ? $callactivitygroup['groupname'] : ''?> ">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="callactivitygroupname-help" class="help-block fpbx-help-block"><?php echo _("The Call Activity group name")?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="description"><?php echo _("Group Description")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
											</div>
											<div class="col-md-9">
												<input class="form-control" id="description" name="description" value="<?php echo !empty($callactivitygroup['description']) ? $callactivitygroup['description'] : ''?>">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="description-help" class="help-block fpbx-help-block"><?php echo _("The Group description")?></span>
								</div>
							</div>
						</div>
                        <div class="element-container" id="adminextdiv">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="form-group">
                                            <div class="col-md-3">
                                                <label class="control-label" for="call_activity_group_users"><?php echo _('Select Users')?></label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="call_activity_group_users"></i>
                                            </div>
                                            <div class="col-md-9">
                                                <select id="call_activity_group_users" class="form-control" multiple="multiple" >
                                                    <?php foreach($users as $user) { ?>
                                                        <option value="<?php echo $user['id']?>" <?php echo !empty($callactivitygroup['usersarray']) && in_array($user['id'], $callactivitygroup['usersarray']) ? 'selected' : '' ?>><?php echo $user['username'].( $user['default_extension']? " <".$user['default_extension'].">":"") ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <span id="call_activity_group_users-help" class="help-block fpbx-help-block"><?php echo _("Select users from User Management to add to the Call Activity Group. </br>User names and their default extensions are displayed in this format: <i> username  < default_extension >. </i> <br/>Note: The number of users in a group is limited to ".$call_activity_group_user_limit.".")?></span>
                                </div>
                            </div>
                        </div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
