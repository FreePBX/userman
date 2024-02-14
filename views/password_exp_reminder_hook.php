
<?php
	$passexpiry = $passexpiry ?? null;
	$forcePasswordReset = $forcePasswordReset?? null;
if ($isPasswordExpiryReminderEnabledSystemWide) {
?>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="passexpiry_enable"><?php echo _('Password Expiry Reminder') ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="passexpiry_enable"></i>
					</div>
					<div class="col-md-9">
						<span class="radioset">
							<input type="radio" id="pwdexp1" name="passexpiry_enable" value="true" <?php echo ($passexpiry == true) ? 'checked' : ''; ?> data-checked="<?php echo ($passexpiry) ? 'true' : 'false' ?>">
							<label for="pwdexp1"><?php echo _('Yes') ?></label>
							<input type="radio" id="pwdexp2" name="passexpiry_enable" value="false" <?php echo ($passexpiry == false || (is_null($passexpiry) && $mode == "group")) ? 'checked' : ''; ?>>
							<label for="pwdexp2"><?php echo _('No') ?></label>
							<?php if ($mode == "user") { ?>
								<input type="radio" id="pwdexp3" name="passexpiry_enable" value='inherit' <?php echo is_null($passexpiry) ? 'checked' : ''; ?>>
								<label for="pwdexp3"><?php echo _('Inherit') ?></label>
							<?php } ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="passexpiry_enable-help" class="help-block fpbx-help-block"><?php echo _("If enabled, user will be notified to reset their password after password expiration days") ?></span>
		</div>
	</div>
</div>
<?php
}
if ((($action == 'adduser' && $mode == 'user') || $mode == 'group') && $isForcePasswordResetEnabledSystemWide) {
?>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="force-user-password-reset"><?php echo _('Force Password Reset') ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="force-user-password-reset"></i>
					</div>
					<div class="col-md-9">
						<?php if($mode == 'user'){ ?>
						<input type="hidden" id="isNewUser" name="isNewUser" value="<?php echo $isNewUser; ?>">
						<?php } ?>
						<span class="radioset">
							<input type="radio" id="forcePasswordReset1" name="forcePasswordReset" value="1" <?php echo $forcePasswordReset && $mode=='group' ? 'checked' : "";?> >
							<label for="forcePasswordReset1"><?php echo _('Yes') ?></label>
							<input type="radio" id="forcePasswordReset2" name="forcePasswordReset" value="0" <?php echo !$forcePasswordReset && $mode=='group' ? 'checked' : "";?> >
							<label for="forcePasswordReset2"><?php echo _('No') ?></label>
							<?php if ($mode == "user") { ?>
								<input type="radio" id="forcePasswordReset3" name="forcePasswordReset" value='inherit' checked>
								<label for="forcePasswordReset3"><?php echo _('Inherit') ?></label>
							<?php } ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="force-user-password-reset-help" class="help-block fpbx-help-block"><?php echo _("If the option is set to yes, the user will be forced to reset the password on first time login.") ?></span>
		</div>
	</div>
</div>
<?php
}
?>