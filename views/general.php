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
			<!--Email Subject-->
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="emailsubject"><?php echo _("Email Body")?></label>
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
									<label class="control-label" for="emailbody"><?php echo _("Email Subject")?></label>
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
		<div id="tab2" class="tab-pane display">
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="authtype"><?php echo _("Authentication Type")?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="authtype"></i>
								</div>
								<div class="col-md-9">
									<select id="authtype" name="authtype" class="form-control">
										<?php foreach($auths as $rawname => $auth) {?>
											<option value="<?php echo $rawname?>" <?php echo $rawname == $authtype ? 'selected' : ''?>><?php echo $auth['name']?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="authtype-help" class="help-block fpbx-help-block"><?php echo sprintf(_("The authentication engine to use"),$brand)?></span>
					</div>
				</div>
			</div>
			<?php foreach($auths as $rawname => $auth) {?>
				<div id="<?php echo $rawname?>-auth-settings" class="auth-settings hidden">
					<?php echo $auth['html']?>
				</div>
			<?php } ?>
		</div>
	</div>
	<!--END Email Body-->
</form>
<script>
	var val = $("#authtype").val();
	$("#" + val + "-auth-settings").removeClass("hidden");

	$("#authtype").change(function() {
		var val = $(this).val();
		$(".auth-settings").addClass("hidden");
		$("#" + val + "-auth-settings").removeClass("hidden");
	});
</script>
<style>
.setting-navs .scroller-left {
	left: 41px;
}
.setting-navs .scroller-right {
	right: 38px;
}
</style>
