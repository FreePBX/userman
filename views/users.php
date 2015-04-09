<?php
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser'){
	$heading = '<h1>' . _("Edit User") . '</h1>';
	$formaction = 'config.php?display=userman&action=showuser&user=' . $user['id'];
}else{
	$heading = '<h1>' . _("Add User") . '</h1>';
	$formaction = 'config.php?display=userman';

}

echo $heading;
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm-9">
			<div class="fpbx-container">
				<?php if(!empty($message)){ ?>
					<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
				<?php } ?>
				<div class="display no-border">
					<div class="container-fluid">
						<div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active"><a href="#usermanlogin" aria-controls="usermanlogin" role="tab" data-toggle="tab"><?php echo _("Login Details")?></a></li>
								<li role="presentation"><a href="#usermanuser" aria-controls="usermanuser" role="tab" data-toggle="tab"><?php echo _("User Details")?></a></li>
								<?php if(\FreePBX::Config()->get('AUTHTYPE') == "usermanager") { ?>
									<li role="presentation"><a href="#pbx" aria-controls="pbx" role="tab" data-toggle="tab"><?php echo $brand?></a></li>
								<?php } ?>
								<?php echo $tabhtml?>
								<li role="presentation" class="<?php echo empty($hookHtml)?'hidden':''?>"><a href="#usermanother" aria-controls="usermanother" role="tab" data-toggle="tab"><?php echo _("Other Settings")?></a></li>
							</ul>
						</div>
						<form class="fpbx-submit" autocomplete="off" name="editM" id="editM" action="<?php echo $formaction ?>" method="post" data-fpbx-delete="config.php?display=userman&amp;action=deluser&amp;user=<?php echo $user['id']?>"onsubmit="return true;">
							<input type="hidden" name="type" value="user">
							<input type="hidden" name="prevUsername" value="<?php echo !empty($user['username']) ? $user['username'] : ''; ?>">
							<input type="hidden" name="user" value="<?php echo !empty($user['id']) ? $user['id'] : ''; ?>">
							<input type="hidden" name="submittype" value="gui">
							<div class="tab-content">
							<!--Login Details -->
							<div role="tabpanel" class="tab-pane active display" id="usermanlogin">
							<!-- LOGIN NAME-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="username"><?php echo _("Login Name")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="username"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="username" name="username" value="<?php echo !empty($user['username']) ? $user['username'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>" required pattern=".{3,255}">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="username-help" class="help-block fpbx-help-block"><?php echo _("This is the name that the user will use when logging in.")?></span>
									</div>
								</div>
							</div>
							<!--END LOGIN NAME-->
							<!--DESCRIPTION-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="description"><?php echo _("Description")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="description" name="description" value="<?php echo !empty($user['description']) ? $user['description'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="description-help" class="help-block fpbx-help-block"><?php echo _("A brief description for this user.")?></span>
									</div>
								</div>
							</div>
							<!--END DESCRIPTION-->
							<!--PASSWORD-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="password"><?php echo _("Password")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="password"></i>
												</div>
												<div class="col-md-9">
													<input type="password" class="form-control password-meter" id="password" name="password" value="<?php echo !empty($user['password']) ? '******' : ''; ?>" tabindex="<?php echo ++$tabindex;?>" required>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="password-help" class="help-block fpbx-help-block"><?php echo _("The user's password.")?></span>
									</div>
								</div>
							</div>
							<!--END Password-->
							<!--Linked Extensions-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="defaultextension"><?php echo _("Primary Linked Extension")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="defaultextension"></i>
												</div>
												<div class="col-md-9">
													<select id="defaultextension" name="defaultextension" class="form-control">
													<?php foreach($dfpbxusers as $dfpbxuser) {?>
														<option value="<?php echo $dfpbxuser['ext']?>" <?php echo $dfpbxuser['selected'] ? 'selected' : '' ?>><?php echo $dfpbxuser['name']?> &lt;<?php echo $dfpbxuser['ext']?>&gt;</option>
													<?php } ?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="defaultextension-help" class="help-block fpbx-help-block"><?php echo _("This is the extension this user is linked to from the Extensions page. A single user can only be linked to one extension, and one extension can only be linked to a single user. If using Rest Apps on a phone, this is the extension that will be mapped to the API permissions set below for this user.")?></span>
									</div>
								</div>
							</div>
							<!--END LINKED EXTENSIONS-->
							</div>
							<!-- End Login details -->

							<!--User Details-->
							<div role="tabpanel" class="tab-pane display" id="usermanuser">
							<!--FIRSTNAME-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="fname"><?php echo _("First Name")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="fname"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="fname" name="fname" value="<?php echo !empty($user['fname']) ? $user['fname'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="fname-help" class="help-block fpbx-help-block"><?php echo _("The user's first name.")?></span>
									</div>
								</div>
							</div>
							<!--END FIRSTNAME-->
							<!--LASTNAME-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="lname"><?php echo _("Last Name")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="lname"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="lname" name="lname" value="<?php echo !empty($user['lname']) ? $user['lname'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="lname-help" class="help-block fpbx-help-block"><?php echo _("The user's last name.")?></span>
									</div>
								</div>
							</div>
							<!--END LASTNAME-->
							<!--DISPLAYNAME-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="displayname"><?php echo _("Display Name")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="displayname"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="displayname" name="displayname" value="<?php echo !empty($user['displayname']) ? $user['displayname'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="displayname-help" class="help-block fpbx-help-block"><?php echo _("Display Name. Used in User Control Panel and Contact Manager to display a customized user name")?></span>
									</div>
								</div>
							</div>
							<!--END DISPLAYNAME-->
							<!--TITLE-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="title"><?php echo _("Title")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="title"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="title" name="title" value="<?php echo !empty($user['title']) ? $user['title'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="title-help" class="help-block fpbx-help-block"><?php echo _("The user's title.")?></span>
									</div>
								</div>
							</div>
							<!--END TITLE-->
							<!--COMPANY-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="company"><?php echo _("Company")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="company"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="company" name="company" value="<?php echo !empty($user['company']) ? $user['company'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="company-help" class="help-block fpbx-help-block"><?php echo _("The user's company.")?></span>
									</div>
								</div>
							</div>
							<!--END COMPANY-->
							<!--EMAIL-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="email"><?php echo _("Email Address")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="email"></i>
												</div>
												<div class="col-md-9">
													<input type="email" class="form-control" id="email" name="email" value="<?php echo !empty($user['email']) ? $user['email'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="email-help" class="help-block fpbx-help-block"><?php echo _("The email address to associate with this user.")?></span>
									</div>
								</div>
							</div>
							<!--END EMAIL-->
							<!--Cell Number-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="cell"><?php echo _("Cell Phone Number")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="cell"></i>
												</div>
												<div class="col-md-9">
													<input type="tel" class="form-control" id="cell" name="cell" value="<?php echo !empty($user['cell']) ? $user['cell'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="cell-help" class="help-block fpbx-help-block"><?php echo _("The user's cell (mobile) phone number.")?></span>
									</div>
								</div>
							</div>
							<!--END CELL-->
							<!--WORK Number-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="work"><?php echo _("Work Phone Number")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="work"></i>
												</div>
												<div class="col-md-9">
													<input type="tel" class="form-control" id="work" name="work" value="<?php echo !empty($user['work']) ? $user['work'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="work-help" class="help-block fpbx-help-block"><?php echo _("The user's work phone number.")?></span>
									</div>
								</div>
							</div>
							<!--END WORK-->
							<!--Home Number-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="home"><?php echo _("Home Phone Number")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="home"></i>
												</div>
												<div class="col-md-9">
													<input type="tel" class="form-control" id="home" name="home" value="<?php echo !empty($user['home']) ? $user['home'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="home-help" class="help-block fpbx-help-block"><?php echo _("The user's home phone number.")?></span>
									</div>
								</div>
							</div>
							<!--END HOME-->
							<!--fax Number-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="fax"><?php echo _("Fax Phone Number")?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="fax"></i>
												</div>
												<div class="col-md-9">
													<input type="tel" class="form-control" id="fax" name="fax" value="<?php echo !empty($user['fax']) ? $user['fax'] : ''; ?>" tabindex="<?php echo ++$tabindex;?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="fax-help" class="help-block fpbx-help-block"><?php echo _("The user's fax phone number.")?></span>
									</div>
								</div>
							</div>
							<!--END FAX-->
							</div>
							<!--END User Details-->
							<div role="tabpanel" class="tab-pane display" id="pbx">
								<div class="element-container">
									<div class="row">
										<div class="col-md-12">
											<div class="row">
												<div class="form-group">
													<div class="col-md-3">
														<label class="control-label" for="pbx_login"><?php echo sprintf(_('Allow %s Administration Login'),$brand)?></label>
														<i class="fa fa-question-circle fpbx-help-icon" data-for="pbx_login"></i>
													</div>
													<div class="col-md-9 radioset">
														<input type="radio" id="pbxlogin1" name="pbx_login" value="true" <?php echo ($pbx_login) ? 'checked' : ''?>>
														<label for="pbxlogin1"><?php echo _("Yes")?></label>
														<input type="radio" id="pbxlogin2" name="pbx_login" value="false" <?php echo (!$pbx_login) ? 'checked' : ''?>>
														<label for="pbxlogin2"><?php echo _("No")?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<span id="pbx_login-help" class="help-block fpbx-help-block"><?php echo sprintf(_("May this user log in to the %s Administration Pages?"),$brand)?></span>
										</div>
									</div>
								</div>
								<div class="element-container">
									<div class="row">
										<div class="col-md-12">
											<div class="row">
												<div class="form-group">
													<div class="col-md-3">
														<label class="control-label" for="pbx_admin"><?php echo _('Grant Full Administration Rights')?></label>
														<i class="fa fa-question-circle fpbx-help-icon" data-for="pbx_admin"></i>
													</div>
													<div class="col-md-9 radioset">
														<input type="radio" id="pbxadmin1" name="pbx_admin" value="true" <?php echo ($pbx_admin) ? 'checked' : ''?>>
														<label for="pbxadmin1"><?php echo _("Yes")?></label>
														<input type="radio" id="pbxadmin2" name="pbx_admin" value="false" <?php echo (!$pbx_admin) ? 'checked' : ''?>>
														<label for="pbxadmin2"><?php echo _("No")?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<span id="pbx_admin-help" class="help-block fpbx-help-block"><?php echo _("Grant full administration rights regardless of extension range or module access. This will also grant this user access to module admin and advanced settings.")?></span>
										</div>
									</div>
								</div>
								<div class="element-container">
									<div class="row">
										<div class="col-md-12">
											<div class="row">
												<div class="form-group">
													<div class="col-md-3">
														<label class="control-label" for="pbx_modules"><?php echo _('Administration Access')?></label>
														<i class="fa fa-question-circle fpbx-help-icon" data-for="pbx_modules"></i>
													</div>
													<div class="col-md-9">
														<select id="pbx_modules" class="bsmultiselect " name="pbx_modules[]" multiple="multiple">
															<?php foreach($modules as $key => $val) {?>
																<option value="<?php echo $key?>" <?php echo in_array($key,$pbx_modules) ? 'selected' : '' ?>><?php echo $val['name']?></option>
															<?php } ?>
														</select>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<span id="pbx_modules-help" class="help-block fpbx-help-block"><?php echo _("Select the Admin Sections this user should have access to.")?></span>
										</div>
									</div>
								</div>
							</div>
							<!--Module Specific -->
							<?php echo $moduleHtml ?>
							<div role="tabpanel" class="tab-pane display" id="usermanother">
								<?php echo $hookHtml;?>
							</div>
						</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-3 hidden-xs bootnav">
			<?php echo load_view(dirname(__FILE__).'/rnav.php',array("users"=>$users)); ?>
		</div>
	</div>
</div>
