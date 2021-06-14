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
									<input type="text" class="form-control" id="hostname" name="hostname" value="<?php echo !empty($hostname) ? $hostname : ""?>" >
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
						<span id="pwd_uppercase_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter the minimum number of lowercase characters.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
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
													<label for="pwd_threshold_weak-no"><?php echo _("No")?></label>										
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
						<span id="pwd_threshold_enable-help" class="help-block fpbx-help-block"><?php echo _("Enter a password complexity threshold from 0 to 4, 0 being 'Really weak', and4 being 'Strong'.")."<br>"._("Enable or disable this rule Yes or No.")?></span>
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

			<div id="pwd-error">
			</div>
		</div>
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
