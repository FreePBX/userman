<!--Password Modal-->
<div class="modal fade" id="setpw">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title mr-auto"><i class="fa fa-key"></i>&nbsp;&nbsp;<?php echo _("Set Password")?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="element-container">
					<div class="row">
						<div class="col-md-12">
							<div class="">
								<div class="form-group row">
									<div class="col-md-12">
										<label class="control-label" for="password"><?php echo _("Password")?></label>
									</div>
									<div class="col-md-12 position-relative">
										<input type="hidden" id="pwuid" value=''>
										<input type="password" autocomplete="new-password" class="form-control password-meter" id="password" name="password" value="<?php echo !empty($user['password']) ? '******' : ''; ?>" required>
									</div>
									<div class="col-md-12 pt-3">
										<br/>
										<div class="pwd-error">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo _("Close")?></button>
			<button type="button" class="btn btn-primary" id="pwsub"><?php echo _("Update Password")?></button>
		</div>
	 </div>
  </div>
</div>

<!--End Password Modal-->
