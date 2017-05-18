<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-connection"><?php echo _("Secure Connection Type")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-connection"></i>
					</div>
					<div class="col-md-9">
						<select id="openldap2-connection" data-default="<?php echo $defaults['connection']?>" name="openldap2-connection" class="form-control">
							<option <?php $config['connection'] == '' ? 'selected' : ''?>><?php echo _("None")?></option>
							<option <?php $config['connection'] == 'tls' ? 'selected' : ''?>>Start TLS</option>
							<option <?php $config['connection'] == 'ssl' ? 'selected' : ''?>>SSL</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-connection-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP secure connection type")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-host"><?php echo _("Host")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-host"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap2-host" data-default="<?php echo $defaults['host']?>" name="openldap2-host" type="text" class="form-control" value="<?php echo isset($config['host']) ? $config['host'] : $defaults['host']?>" required>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-host-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP host")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-post"><?php echo _("Port")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-port"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap2-port" data-default="<?php echo $defaults['port']?>" name="openldap2-port" type="text" class="form-control" value="<?php echo isset($config['port']) ? $config['port'] : $defaults['port']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-port-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP port")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-host"><?php echo _("Bind DN or Username")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-username"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap2-username" data-default="<?php echo $defaults['username']?>" name="openldap2-username" type="text" class="form-control" value="<?php echo isset($config['username']) ? $config['username'] : $defaults['username']?>" required>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-username-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP username")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-password"><?php echo _("Password")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-password"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap2-password" data-default="<?php echo $defaults['password']?>" name="openldap2-password" type="text" class="form-control" value="<?php echo isset($config['password']) ? $config['password'] : $defaults['password']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-password-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP password")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-basedn"><?php echo _("Base DN")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-basedn"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap2-basedn" data-default="<?php echo $defaults['basedn']?>" name="openldap2-basedn" type="text" class="form-control" value="<?php echo isset($config['basedn']) ? $config['basedn'] : $defaults['basedn']?>" required>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-basedn-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP Base-DN. Usually in the format of DC=example,DC=com")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap2-status"><?php echo _("Status")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-status"></i>
					</div>
					<div class="col-md-9">
						<div id="openldap2-status" class="bg-<?php echo $status['type']?>"><?php echo $status['message']?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap2-status-help" class="help-block fpbx-help-block"><?php echo _("The connection status of the OpenLDAP Server")?></span>
		</div>
	</div>
</div>
<fieldset>
	<legend>User configuration</legend>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userdn"><?php echo _("User DN")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userdn"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userdn" data-default="<?php echo $defaults['userdn']?>" name="openldap2-userdn" type="text" class="form-control" value="<?php echo isset($config['userdn']) ? $config['userdn'] : $defaults['userdn']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userdn-help" class="help-block fpbx-help-block"><?php echo _("This value is used in addition to the base DN when searching and loading users. An example is ou=Users. If no value is supplied, the subtree search will start from the base DN.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userobjectclass"><?php echo _("User object class")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userobjectclass"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userobjectclass" data-default="<?php echo $defaults['userobjectclass']?>" name="openldap2-userobjectclass" type="text" class="form-control" value="<?php echo isset($config['userobjectclass']) ? $config['userobjectclass'] : $defaults['userobjectclass']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userobjectclass-help" class="help-block fpbx-help-block"><?php echo _("The LDAP user object class type to use when loading users.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userobjectfilter"><?php echo _("User object filter")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userobjectfilter"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userobjectfilter" data-default="<?php echo $defaults['userobjectfilter']?>" name="openldap2-userobjectfilter" type="text" class="form-control" value="<?php echo isset($config['userobjectfilter']) ? $config['userobjectfilter'] : $defaults['userobjectfilter']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userobjectfilter-help" class="help-block fpbx-help-block"><?php echo _("The filter to use when searching user objects.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usernameattr"><?php echo _("User name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usernameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-usernameattr" data-default="<?php echo $defaults['usernameattr']?>" name="openldap2-usernameattr" type="text" class="form-control" value="<?php echo isset($config['usernameattr']) ? $config['usernameattr'] : $defaults['usernameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usernameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use on the user object (eg. cn, sAMAccountName)")?></span>
			</div>
		</div>
	</div>
	<!--
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usernamerdnattr"><?php echo _("User name RDN attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usernamerdnattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-usernamerdnattr" data-default="<?php echo $defaults['usernamerdnattr']?>" name="openldap2-usernamerdnattr" type="text" class="form-control" value="<?php echo isset($config['usernamerdnattr']) ? $config['usernamerdnattr'] : $defaults['usernamerdnattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usernamerdnattr-help" class="help-block fpbx-help-block"><?php echo _("The RDN to use when loading the user username (eg. cn).")?></span>
			</div>
		</div>
	</div>
	-->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userfirstnameattr"><?php echo _("User first name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userfirstnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userfirstnameattr" data-default="<?php echo $defaults['userfirstnameattr']?>" name="openldap2-userfirstnameattr" type="text" class="form-control" value="<?php echo isset($config['userfirstnameattr']) ? $config['userfirstnameattr'] : $defaults['userfirstnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userfirstnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user first name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userlastnameattr"><?php echo _("User last name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userlastnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userlastnameattr" data-default="<?php echo $defaults['userlastnameattr']?>" name="openldap2-userlastnameattr" type="text" class="form-control" value="<?php echo isset($config['userlastnameattr']) ? $config['userlastnameattr'] : $defaults['userlastnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userlastnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user last name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userdisplaynameattr"><?php echo _("User display name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userdisplaynameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userdisplaynameattr" data-default="<?php echo $defaults['userdisplaynameattr']?>" name="openldap2-userdisplaynameattr" type="text" class="form-control" value="<?php echo isset($config['userdisplaynameattr']) ? $config['userdisplaynameattr'] : $defaults['userdisplaynameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userdisplaynameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user full name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usergroupmemberattr"><?php echo _("User group attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usergroupmemberattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-usergroupmemberattr" data-default="<?php echo $defaults['usergroupmemberattr']?>" name="openldap2-usergroupmemberattr" type="text" class="form-control" value="<?php echo isset($config['usergroupmemberattr']) ? $config['usergroupmemberattr'] : $defaults['usergroupmemberattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usergroupmemberattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the users groups.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userexternalidattr"><?php echo _("User unique identifier attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userexternalidattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userexternalidattr" data-default="<?php echo $defaults['userexternalidattr']?>" name="openldap2-userexternalidattr" type="text" class="form-control" value="<?php echo isset($config['userexternalidattr']) ? $config['userexternalidattr'] : $defaults['userexternalidattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userexternalidattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use for tracking user identity across user renames.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usermailattr"><?php echo _("User email attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usermailattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-usermailattr" data-default="<?php echo $defaults['usermailattr']?>" name="openldap2-usermailattr" type="text" class="form-control" value="<?php echo isset($config['usermailattr']) ? $config['usermailattr'] : $defaults['usermailattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usermailattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user email.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userdescriptionattr"><?php echo _("User Description attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userdescriptionattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userdescriptionattr" data-default="<?php echo $defaults['userdescriptionattr']?>" name="openldap2-userdescriptionattr" type="text" class="form-control" value="<?php echo isset($config['userdescriptionattr']) ? $config['userdescriptionattr'] : $defaults['userdescriptionattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user description.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usertitleattr"><?php echo _("User Title attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usertitleattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userdescriptionattr" data-default="<?php echo $defaults['usertitleattr']?>" name="openldap2-usertitleattr" type="text" class="form-control" value="<?php echo isset($config['usertitleattr']) ? $config['usertitleattr'] : $defaults['usertitleattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usertitleattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user title.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usercompanyattr"><?php echo _("User Company attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usercomapnyattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-usercompanyattr" data-default="<?php echo $defaults['usercompanyattr']?>" name="openldap2-usercompanyattr" type="text" class="form-control" value="<?php echo isset($config['usercompanyattr']) ? $config['usercompanyattr'] : $defaults['usercompanyattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usercompanyattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user company.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userdepartmentattr"><?php echo _("User Department attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userdepartmentattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userdepartmentattr" data-default="<?php echo $defaults['userdepartmentattr']?>" name="openldap2-userdepartmentattr" type="text" class="form-control" value="<?php echo isset($config['userdepartmentattr']) ? $config['userdepartmentattr'] : $defaults['userdepartmentattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userdepartmentattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user department.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userhomephoneattr"><?php echo _("User Home Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userhomephoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userhomephoneattr" data-default="<?php echo $defaults['userhomephoneattr']?>" name="openldap2-userhomephoneattr" type="text" class="form-control" value="<?php echo isset($config['userhomephoneattr']) ? $config['userhomephoneattr'] : $defaults['userhomephoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user home phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userworkphoneattr"><?php echo _("User Work Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userworkphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userworkphoneattr" data-default="<?php echo $defaults['userworkphoneattr']?>" name="openldap2-userworkphoneattr" type="text" class="form-control" value="<?php echo isset($config['userworkphoneattr']) ? $config['userworkphoneattr'] : $defaults['userworkphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userworkphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user work phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-usercellphoneattr"><?php echo _("User Cell Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-usercellphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-usercellphoneattr" data-default="<?php echo $defaults['usercellphoneattr']?>" name="openldap2-usercellphoneattr" type="text" class="form-control" value="<?php echo isset($config['usercellphoneattr']) ? $config['userdcellphoneattr'] : $defaults['usercellphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-usercellphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user cell phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-userfaxphoneattr"><?php echo _("User Fax attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-userfaxphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-userfaxphoneattr" data-default="<?php echo $defaults['userfaxphoneattr']?>" name="openldap2-userfaxphoneattr" type="text" class="form-control" value="<?php echo isset($config['userfaxphoneattr']) ? $config['userfaxphoneattr'] : $defaults['userfaxphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-userfaxphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user fax.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-la"><?php echo _("User extension Link attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-la"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-la" data-default="<?php echo $defaults['la']?>" name="openldap2-la" type="text" class="form-control" value="<?php echo isset($config['la']) ? $config['la'] : $defaults['la']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-la-help" class="help-block fpbx-help-block"><?php echo _("If this is set then User Manager will use the defined attribute of the user from the OpenLDAP server as the extension link. NOTE: If this field is set it will overwrite any manually linked extensions where this attribute extists!! (Try lowercase if it is not working.)")?></span>
			</div>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend>Group configuration</legend>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupdnaddition"><?php echo _("Group DN")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupdnaddition"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupdnaddition" data-default="<?php echo $defaults['groupdnaddition']?>" name="openldap2-groupdnaddition" type="text" class="form-control" value="<?php echo isset($config['groupdnaddition']) ? $config['groupdnaddition'] : $defaults['groupdnaddition']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupdnaddition-help" class="help-block fpbx-help-block"><?php echo _("This value is used in addition to the base DN when searching and loading groups. An example is ou=Groups. If no value is supplied, the subtree search will start from the base DN.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupobjectclass"><?php echo _("Group object class")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupobjectclass"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupobjectclass" data-default="<?php echo $defaults['groupobjectclass']?>" name="openldap2-groupobjectclass" type="text" class="form-control" value="<?php echo isset($config['groupobjectclass']) ? $config['groupobjectclass'] : $defaults['groupobjectclass']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupobjectclass-help" class="help-block fpbx-help-block"><?php echo _("The LDAP user object class type to use when loading groups.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupobjectfilter"><?php echo _("Group object filter")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupobjectfilter"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupobjectfilter" data-default="<?php echo $defaults['groupobjectfilter']?>" name="openldap2-groupobjectfilter" type="text" class="form-control" value="<?php echo isset($config['groupobjectfilter']) ? $config['groupobjectfilter'] : $defaults['groupobjectfilter']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupobjectfilter-help" class="help-block fpbx-help-block"><?php echo _("The filter to use when searching group objects.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupnameattr"><?php echo _("Group name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupnameattr" data-default="<?php echo $defaults['groupnameattr']?>" name="openldap2-groupnameattr" type="text" class="form-control" value="<?php echo isset($config['groupnameattr']) ? $config['groupnameattr'] : $defaults['groupnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupdescriptionattr"><?php echo _("Group description attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupdescriptionattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupdescriptionattr" data-default="<?php echo $defaults['groupdescriptionattr']?>" name="openldap2-groupdescriptionattr" type="text" class="form-control" value="<?php echo isset($config['groupdescriptionattr']) ? $config['groupdescriptionattr'] : $defaults['groupdescriptionattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group description.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupmemberattr"><?php echo _("Group members attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupmemberattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupmemberattr" data-default="<?php echo $defaults['groupmemberattr']?>" name="openldap2-groupmemberattr" type="text" class="form-control" value="<?php echo isset($config['groupmemberattr']) ? $config['groupmemberattr'] : $defaults['groupmemberattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupmemberattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group members.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap2-groupexternalidattr"><?php echo _("Group unique identifier attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap2-groupexternalidattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap2-groupexternalidattr" data-default="<?php echo $defaults['groupexternalidattr']?>" name="openldap2-groupexternalidattr" type="text" class="form-control" value="<?php echo isset($config['groupexternalidattr']) ? $config['groupexternalidattr'] : $defaults['groupexternalidattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap2-groupexternalidattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use for tracking group identity across group renames.")?></span>
			</div>
		</div>
	</div>
</fieldset>
<style>
	#openldap2-status {
		padding: 5px;
		border-radius: 5px;
		margin-top: 5px;
	}
</style>
