<form autocomplete="off" class="fpbx-submit" name="general" action="config.php?display=userman#settings" method="post" onsubmit="return">
	<input type="hidden" name="type" value="general">
	<input type="hidden" name="submittype" value="gui">
	<div class="nav-container setting-navs">
		<div class="scroller scroller-left"><i class="glyphicon glyphicon-chevron-left"></i></div>
		<div class="scroller scroller-right"><i class="glyphicon glyphicon-chevron-right"></i></div>
		<div class="wrapper">
			<ul class="nav nav-tabs list" role="tablist">
				<li data-name="tab1" class="change-tab active"><a href="#tab1" aria-controls="tab1" role="tab" data-toggle="tab"><?php echo _("Email Settings")?></a></li>
				<li data-name="tab2" class="change-tab"><a href="#tab2" aria-controls="tab2" role="tab" data-toggle="tab"><?php echo _("Authentication Settings")?></a></li>
				<li data-name="tab3" class="change-tab"><a href="#tab3" aria-controls="tab3" role="tab" data-toggle="tab"><?php echo _("Password Management")?></a></li>
			</ul>
		</div>
	</div>
	<div class="tab-content">
		<div id="tab1" class="tab-pane display active">
			<h3><?php echo _("Email Settings")?></h3>
			<!--Email Subject-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="auto-email"><?php echo _("Send Email on External New User Creation")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="auto-email"></i>
								</div>
								<div class="col-md-9">
									<span class="radioset">
										<input type="radio" id="auto-email-yes" name="auto-email" value="yes" <?php echo ($autoEmail) ? "checked" : ""?>>
										<label for="auto-email-yes"><?php echo _("Yes")?></label>
										<input type="radio" id="auto-email-no" name="auto-email" value="no" <?php echo !($autoEmail) ? "checked" : ""?>>
										<label for="auto-email-no"><?php echo _("No")?></label>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="auto-email-help" class="help-block fpbx-help-block"><?php echo _("Whether to send an email (using the template below) to new users when they are created externally (not directly through User Manager)")?></span>
					</div>
				</div>
			</div>
			<!--END Email Subject-->
			<!-- Email Html -->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="mailtype"><?php echo _("Send Email as HTML")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="mailtype"></i>
								</div>
								<div class="col-md-9">
									<span class="radioset">
										<input type="radio" id="mailtype-yes" name="mailtype" value="html" <?php echo $mailtype === "html" ? "checked" : ""?>>
										<label for="mailtype-yes"><?php echo _("Yes")?></label>
										<input type="radio" id="mailtype-no" name="mailtype" value="text" <?php echo $mailtype !== "html" ? "checked" : ""?>>
										<label for="mailtype-no"><?php echo _("No")?></label>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="mailtype-help" class="help-block fpbx-help-block"><?php echo _("Whether Email Body will send as HTML or plain text to the user.")?></span>
					</div>
				</div>
			</div>
			<!-- END Email Html -->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="hostname"><?php echo _("Host Name")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="hostname"></i>
								</div>
								<div class="col-md-9">
									<input type="text" class="form-control" id="hostname" name="hostname" placeholder="<?php echo $host; ?>" value="<?php echo !empty($hostname) ? $hostname : ""?>" >
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="hostname-help" class="help-block fpbx-help-block"><?php echo sprintf(_("The hostname used for email. If left blank the default value of '%s' will be used"),$host)?></span>
					</div>
				</div>
			</div>
			<!--Email Subject-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="emailsubject"><?php echo _("Email Subject")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="emailsubject"></i>
								</div>
								<div class="col-md-9">
									<input type="text" class="form-control" id="emailsubject" name="emailsubject" value="<?php echo !empty($emailsubject) ? $emailsubject : _("Your %brand% Account")?>" >
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="emailsubject-help" class="help-block fpbx-help-block"><?php echo sprintf(_("Text to be used for the subject of the welcome email. Useable variables are:<ul><li>fname: First name</li><li>lname: Last name</li><li>brand: %s</li><li>title: title</li><li>username: Username</li><li>password: Password</li></ul>"),$brand)?></span>
					</div>
				</div>
			</div>
			<!--END Email Subject-->
			<!--Email Body-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="emailbody"><?php echo _("Email Body")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="emailbody"></i>
								</div>
								<div class="col-md-9">
									<textarea class="form-control" id="emailbody" name="emailbody" rows="15" cols="80" ><?php echo !empty($emailbody) ? $emailbody : file_get_contents(__DIR__.'/emails/welcome_text.tpl')?></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="emailbody-help" class="help-block fpbx-help-block"><?php echo sprintf(_("Text to be used for the body of the welcome email. Useable variables are:<ul><li>fname: First name</li><li>lname: Last name</li><li>brand: %s</li><li>title: title</li><li>username: Username</li><li>password: Password</li></ul>"),$brand)?></span>
					</div>
				</div>
			</div>
		</div>

				<!--Authentication Settings-->
				<?php
			extract($pwdSettings);
		?>
		<div id="tab2" class="tab-pane display">
			<h3><?php echo _("Password Policies")?></h3>
			<!--Length-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_length_enable"><?php echo _("Password Length")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_length_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_length_enable-yes" name="pwd_length_enable" value="yes" <?php echo ($pwd_length_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_length_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_length_enable-no" name="pwd_length_enable" value="no" <?php echo !($pwd_length_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_length_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_length_value" min="1" value=<?php echo !($pwd_length_value) ? 8 : $pwd_length_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_length_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum password length.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>
			<!--End of Length-->

			<!--Uppercase-->
            <div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_uppercase_enable"><?php echo _("Uppercase")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_uppercase_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_uppercase_enable-yes" name="pwd_uppercase_enable" value="yes" <?php echo ($pwd_uppercase_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_uppercase_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_uppercase_enable-no" name="pwd_uppercase_enable" value="no" <?php echo !($pwd_uppercase_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_uppercase_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_uppercase_value" min="1" value=<?php echo !($pwd_uppercase_value) ? 1 : $pwd_uppercase_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_uppercase_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum number of uppercase characters.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>
			<!--End of Uppsercase-->	

			<!--Lowercase-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_lowercase_enable"><?php echo _("Lowercase")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_lowercase_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_lowercase_enable-yes" name="pwd_lowercase_enable" value="yes" <?php echo ($pwd_lowercase_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_lowercase_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_lowercase_enable-no" name="pwd_lowercase_enable" value="no" <?php echo !($pwd_lowercase_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_lowercase_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_lowercase_value" min="1" value=<?php echo !($pwd_lowercase_value) ? 1 : $pwd_lowercase_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_lowercase_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum number of lowercase characters.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>
			<!--End of Lowercase-->

			<!--Numeric-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_numeric_enable"><?php echo _("Numeric") ?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_numeric_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_numeric_enable-yes" name="pwd_numeric_enable" value="yes" <?php echo ($pwd_numeric_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_numeric_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_numeric_enable-no" name="pwd_numeric_enable" value="no" <?php echo !($pwd_numeric_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_numeric_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_numeric_value" min="1" value=<?php echo !($pwd_numeric_value) ? 1 : $pwd_numeric_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_numeric_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum number of numeric characters.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>
			<!--End of Numeric-->

			<!--Special-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_special_enable"><?php echo _("Special")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_special_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_special_enable-yes" name="pwd_special_enable" value="yes" <?php echo ($pwd_special_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_special_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_special_enable-no" name="pwd_special_enable" value="no" <?php echo !($pwd_special_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_special_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_special_value" min="1" value=<?php echo !($pwd_special_value) ? 1 : $pwd_special_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_special_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum number of special characters.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>
			<!--END of Special-->

			<!--Punctuation-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_punctuation_enable"><?php echo _("Punctuation")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_punctuation_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_punctuation_enable-yes" name="pwd_punctuation_enable" value="yes" <?php echo ($pwd_punctuation_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_punctuation_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_punctuation_enable-no" name="pwd_punctuation_enable" value="no" <?php echo !($pwd_punctuation_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_punctuation_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_punctuation_value" min="1" value=<?php echo !($pwd_punctuation_value) ? 1 : $pwd_punctuation_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_punctuation_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum number of punctuation characters.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>		
			<!--End of Punctuation-->

			<!--Threshold weak password-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_threshold_enable"><?php echo _("Threshold Weak Password")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="pwd_threshold_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-10">
												<span class="radioset">
													<input type="radio" id="pwd_threshold_enable-yes" name="pwd_threshold_enable" value="yes" <?php echo ($pwd_threshold_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_threshold_enable-yes"><?php echo _("Yes")?></label>
													<input type="radio" id="pwd_threshold_enable-no" name="pwd_threshold_enable" value="no" <?php echo !($pwd_threshold_enable == "yes") ? "checked" : ""?>>
													<label for="pwd_threshold_enable-no"><?php echo _("No")?></label>										
												</span>
											</div>
											<div class="col-md-2">
												<span>
													<input class="form-control" type="number" name="pwd_threshold_value" min="1" value=<?php echo !($pwd_threshold_value) ? 4 : $pwd_threshold_value?>>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pwd_threshold_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter a password complexity threshold from 0 to 4, 0 being 'Really weak', and 4 being 'Strong'.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
					</div>
				</div>
			</div>		
			<!--End of Threshold weak password-->

			<!--Test-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="pwd_test_enable"><?php echo _("Test")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" id="pwd-templates-show" data-for="pws-test_enable"></i>
								</div>
								<div class="col-md-9">
									<div class="container-fluid">
										<input id="pwd_test" class="form-control password-meter" type="text" value="">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="pws-test_enable-help" class="help-block fpbx-help-block">
							<?php echo _("Enter a password to test.")?>
							<div id="pwd-templates">
							</div>
						</span>
					</div>
				</div>
			</div>
			<!--End of Test-->

			<div class="pwd-error">
			</div>
		</div>
		<!-- Start : Password Management -->
		
		<div id="tab3" class="tab-pane display">
			<h3><?php echo _("Password Management")?></h3>
			<?php extract($passwordReminder); ?>
			<div class="alert alert-warning">
				<p class="mb-0"><b>Note:</b> <?php echo _("Enabling / Disabling below settings will be applied for all users. You can manage these settings for each users from"); ?> <a href="/admin/config.php?display=userman" target="_blank">userman</a> </p>
			</div>

			<!-- Start: Force reset the password on first time login  -->
			<div class="element-container">
				<div class="row">
					<div class="form-group">
						<div class="col-md-7">
							<label class="control-label" for="forcePasswordReset"><?php echo _("Force reset the password on first time login") ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="forcePasswordReset"></i>&nbsp;
							<span id="forcePasswordReset-help" class="help-block fpbx-help-block m-0 p-0">
								<?php echo _("If the option is set to yes, the user will be forced to reset the password on first time login.") ?>
							</span>
						</div>
						<div class="col-md-5 radioset text-right">
							<input type="radio" id="forcePasswordReset1" name="forcePasswordReset"  value="1" <?php echo $forcePasswordReset ? 'checked=""' : "" ?>>
							<label for="forcePasswordReset1">Yes</label>
							<input type="radio" id="forcePasswordReset0" name="forcePasswordReset"  value="0" <?php echo $forcePasswordReset ? '' : "checked=''" ?>>
							<label for="forcePasswordReset0">No</label>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Force reset the password on first time login  -->
			
			<!-- Start: Password Expiry Reminder  -->
			<div class="element-container">
				<div class="row">
					<div class="form-group">
						<div class="col-md-7">
							<label class="control-label" for="passwordExpiryReminder"><?php echo _("Password Expiry Reminder") ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="passwordExpiryReminder"></i>&nbsp;
							<span id="passwordExpiryReminder-help" class="help-block fpbx-help-block m-0 p-0">
								<?php echo _("If this option is enabled user has to change their passwords every 60, 90 or XX number of days. In other words, the password has been in use for too long and user must choose a new password at this time") ?>
							</span>
						</div>
						<div class="col-md-5 radioset text-right">
							<input type="radio" id="passwordExpiryReminder1" onClick="handlePasswordExpiryReminder()" name="passwordExpiryReminder"  value="1" <?php echo $passwordExpiryReminder ? 'checked=""' : "" ?>>
							<label for="passwordExpiryReminder1">Yes</label>
							<input type="radio" id="passwordExpiryReminder0" onClick="handlePasswordExpiryReminder()" name="passwordExpiryReminder"  value="0" <?php echo $passwordExpiryReminder ? '' : "checked=''" ?>>
							<label for="passwordExpiryReminder0">No</label>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Password Expiry Reminder  -->
		
			<!-- Start: Password expiration Days-->
			<div class="element-container" style="<?php echo $passwordExpiryReminder ? "" : "display:none;" ?>" id="expirationDaysWrapper">
				<div class="row">
					<div class="form-group">
						<div class="col-md-7">
							<label class="control-label" for="passwordExpirationDays"><?php echo _("Password Expiration Days") ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="passwordExpirationDays"></i>&nbsp;
							<span id="passwordExpirationDays-help" class="help-block fpbx-help-block m-0 p-0">
								<?php echo _("Password expiration days is when an user requires to change their passwords. Example 60, 90 or XX number of days. User will be notified every time when they login, 5 days prior to the password expiry") ?>
							</span>
						</div>
						<div class="col-md-5">
							<input type="number" class="form-control" id="passwordExpirationDays" min="1" name="passwordExpirationDays" value=<?php echo $passwordExpirationDays ? $passwordExpirationDays : "90" ?>>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Password expiration Days-->
		
			<!-- Start: Password expiry reminder days -->
			<div class="element-container" style="<?php echo $passwordExpiryReminder ? "" : "display:none;" ?>" id="expiryReminderDaysWrapper">
				<div class="row">
					<div class="form-group">
						<div class="col-md-7">
							<label class="control-label" for="passwordExpiryReminderDays"><?php echo _("Password Expiry Reminder Days") ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="passwordExpiryReminderDays"></i>&nbsp;
							<span id="passwordExpiryReminderDays-help" class="help-block fpbx-help-block m-0 p-0">
								<?php echo _("X days from when user will get password expiry reminder") ?>
							</span>
						</div>
						<div class="col-md-5">
							<input type="number" class="form-control" id="passwordExpiryReminderDays" min="1" name="passwordExpiryReminderDays" value=<?php echo $passwordExpiryReminderDays ? $passwordExpiryReminderDays : "5" ?>>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Password expiry reminder days -->
		</div>
		<!-- End : Password Management -->
	</div>
</form>
<style>
.setting-navs .scroller-left {
	left: 41px;
}
.setting-navs .scroller-right {
	right: 38px;
}
</style>
<script>
	function handlePasswordExpiryReminder() {
		let passwordExpiryReminder = $('input[name=passwordExpiryReminder]:checked').val();
		if (parseInt(passwordExpiryReminder)) {
			$('#expirationDaysWrapper').show();
			$('#expiryReminderDaysWrapper').show();
		} else {
			$('#expirationDaysWrapper').hide();
			$('#expiryReminderDaysWrapper').hide();
		}
	}
</script>
