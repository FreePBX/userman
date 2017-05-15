<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-connection"><?php echo _("Secure Connection Type")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-connection"></i>
					</div>
					<div class="col-md-9">
						<select id="openldap-connection" data-default="<?php echo $defaults['connection']?>" name="openldap-connection" class="form-control">
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
			<span id="openldap-connection-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP secure connection type")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-host"><?php echo _("Host")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-host"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap-host" data-default="<?php echo $defaults['host']?>" name="openldap-host" type="text" class="form-control" value="<?php echo isset($config['host']) ? $config['host'] : $defaults['host']?>" required>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap-host-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP host")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-post"><?php echo _("Port")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-port"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap-port" data-default="<?php echo $defaults['port']?>" name="openldap-port" type="text" class="form-control" value="<?php echo isset($config['port']) ? $config['port'] : $defaults['port']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap-port-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP port")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-host"><?php echo _("Bind DN or Username")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-username"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap-username" data-default="<?php echo $defaults['username']?>" name="openldap-username" type="text" class="form-control" value="<?php echo isset($config['username']) ? $config['username'] : $defaults['username']?>" required>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap-username-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP username")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-password"><?php echo _("Password")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-password"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap-password" data-default="<?php echo $defaults['password']?>" name="openldap-password" type="text" class="form-control" value="<?php echo isset($config['password']) ? $config['password'] : $defaults['password']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap-password-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP password")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-basedn"><?php echo _("Base DN")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-basedn"></i>
					</div>
					<div class="col-md-9">
						<input id="openldap-basedn" data-default="<?php echo $defaults['basedn']?>" name="openldap-basedn" type="text" class="form-control" value="<?php echo isset($config['basedn']) ? $config['basedn'] : $defaults['basedn']?>" required>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap-basedn-help" class="help-block fpbx-help-block"><?php echo _("The OpenLDAP Base-DN. Usually in the format of DC=example,DC=com")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="openldap-status"><?php echo _("Status")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-status"></i>
					</div>
					<div class="col-md-9">
						<div id="openldap-status" class="bg-<?php echo $status['type']?>"><?php echo $status['message']?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="openldap-status-help" class="help-block fpbx-help-block"><?php echo _("The connection status of the OpenLDAP Server")?></span>
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
							<label class="control-label" for="openldap-userdn"><?php echo _("User DN")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userdn"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userdn" data-default="<?php echo $defaults['userdn']?>" name="openldap-userdn" type="text" class="form-control" value="<?php echo isset($config['userdn']) ? $config['userdn'] : $defaults['userdn']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userdn-help" class="help-block fpbx-help-block"><?php echo _("This value is used in addition to the base DN when searching and loading users. An example is ou=Users. If no value is supplied, the subtree search will start from the base DN.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userobjectclass"><?php echo _("User object class")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userobjectclass"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userobjectclass" data-default="<?php echo $defaults['userobjectclass']?>" name="openldap-userobjectclass" type="text" class="form-control" value="<?php echo isset($config['userobjectclass']) ? $config['userobjectclass'] : $defaults['userobjectclass']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userobjectclass-help" class="help-block fpbx-help-block"><?php echo _("The LDAP user object class type to use when loading users.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userobjectfilter"><?php echo _("User object filter")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userobjectfilter"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userobjectfilter" data-default="<?php echo $defaults['userobjectfilter']?>" name="openldap-userobjectfilter" type="text" class="form-control" value="<?php echo isset($config['userobjectfilter']) ? $config['userobjectfilter'] : $defaults['userobjectfilter']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userobjectfilter-help" class="help-block fpbx-help-block"><?php echo _("The filter to use when searching user objects.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-usernameattr"><?php echo _("User name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usernameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-usernameattr" data-default="<?php echo $defaults['usernameattr']?>" name="openldap-usernameattr" type="text" class="form-control" value="<?php echo isset($config['usernameattr']) ? $config['usernameattr'] : $defaults['usernameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usernameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use on the user object (eg. cn, sAMAccountName)")?></span>
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
							<label class="control-label" for="openldap-usernamerdnattr"><?php echo _("User name RDN attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usernamerdnattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-usernamerdnattr" data-default="<?php echo $defaults['usernamerdnattr']?>" name="openldap-usernamerdnattr" type="text" class="form-control" value="<?php echo isset($config['usernamerdnattr']) ? $config['usernamerdnattr'] : $defaults['usernamerdnattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usernamerdnattr-help" class="help-block fpbx-help-block"><?php echo _("The RDN to use when loading the user username (eg. cn).")?></span>
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
							<label class="control-label" for="openldap-userfirstnameattr"><?php echo _("User first name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userfirstnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userfirstnameattr" data-default="<?php echo $defaults['userfirstnameattr']?>" name="openldap-userfirstnameattr" type="text" class="form-control" value="<?php echo isset($config['userfirstnameattr']) ? $config['userfirstnameattr'] : $defaults['userfirstnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userfirstnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user first name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userlastnameattr"><?php echo _("User last name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userlastnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userlastnameattr" data-default="<?php echo $defaults['userlastnameattr']?>" name="openldap-userlastnameattr" type="text" class="form-control" value="<?php echo isset($config['userlastnameattr']) ? $config['userlastnameattr'] : $defaults['userlastnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userlastnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user last name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userdisplaynameattr"><?php echo _("User display name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userdisplaynameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userdisplaynameattr" data-default="<?php echo $defaults['userdisplaynameattr']?>" name="openldap-userdisplaynameattr" type="text" class="form-control" value="<?php echo isset($config['userdisplaynameattr']) ? $config['userdisplaynameattr'] : $defaults['userdisplaynameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userdisplaynameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user full name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-usergroupmemberattr"><?php echo _("User group attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usergroupmemberattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-usergroupmemberattr" data-default="<?php echo $defaults['usergroupmemberattr']?>" name="openldap-usergroupmemberattr" type="text" class="form-control" value="<?php echo isset($config['usergroupmemberattr']) ? $config['usergroupmemberattr'] : $defaults['usergroupmemberattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usergroupmemberattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the users groups.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userexternalidattr"><?php echo _("User unique identifier attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userexternalidattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userexternalidattr" data-default="<?php echo $defaults['userexternalidattr']?>" name="openldap-userexternalidattr" type="text" class="form-control" value="<?php echo isset($config['userexternalidattr']) ? $config['userexternalidattr'] : $defaults['userexternalidattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userexternalidattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use for tracking user identity across user renames.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-usermailattr"><?php echo _("User email attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usermailattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-usermailattr" data-default="<?php echo $defaults['usermailattr']?>" name="openldap-usermailattr" type="text" class="form-control" value="<?php echo isset($config['usermailattr']) ? $config['usermailattr'] : $defaults['usermailattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usermailattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user email.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userdescriptionattr"><?php echo _("User Description attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userdescriptionattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userdescriptionattr" data-default="<?php echo $defaults['userdescriptionattr']?>" name="openldap-userdescriptionattr" type="text" class="form-control" value="<?php echo isset($config['userdescriptionattr']) ? $config['userdescriptionattr'] : $defaults['userdescriptionattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user description.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-usertitleattr"><?php echo _("User Title attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usertitleattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userdescriptionattr" data-default="<?php echo $defaults['usertitleattr']?>" name="openldap-usertitleattr" type="text" class="form-control" value="<?php echo isset($config['usertitleattr']) ? $config['usertitleattr'] : $defaults['usertitleattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usertitleattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user title.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-usercompanyattr"><?php echo _("User Company attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usercomapnyattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-usercompanyattr" data-default="<?php echo $defaults['usercompanyattr']?>" name="openldap-usercompanyattr" type="text" class="form-control" value="<?php echo isset($config['usercompanyattr']) ? $config['usercompanyattr'] : $defaults['usercompanyattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usercompanyattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user company.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userdepartmentattr"><?php echo _("User Department attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userdepartmentattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userdepartmentattr" data-default="<?php echo $defaults['userdepartmentattr']?>" name="openldap-userdepartmentattr" type="text" class="form-control" value="<?php echo isset($config['userdepartmentattr']) ? $config['userdepartmentattr'] : $defaults['userdepartmentattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userdepartmentattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user department.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userhomephoneattr"><?php echo _("User Home Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userhomephoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userhomephoneattr" data-default="<?php echo $defaults['userhomephoneattr']?>" name="openldap-userhomephoneattr" type="text" class="form-control" value="<?php echo isset($config['userhomephoneattr']) ? $config['userhomephoneattr'] : $defaults['userhomephoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user home phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userworkphoneattr"><?php echo _("User Work Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userworkphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userworkphoneattr" data-default="<?php echo $defaults['userworkphoneattr']?>" name="openldap-userworkphoneattr" type="text" class="form-control" value="<?php echo isset($config['userworkphoneattr']) ? $config['userworkphoneattr'] : $defaults['userworkphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userworkphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user work phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-usercellphoneattr"><?php echo _("User Cell Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-usercellphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-usercellphoneattr" data-default="<?php echo $defaults['usercellphoneattr']?>" name="openldap-usercellphoneattr" type="text" class="form-control" value="<?php echo isset($config['usercellphoneattr']) ? $config['userdcellphoneattr'] : $defaults['usercellphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-usercellphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user cell phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-userfaxphoneattr"><?php echo _("User Fax attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-userfaxphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-userfaxphoneattr" data-default="<?php echo $defaults['userfaxphoneattr']?>" name="openldap-userfaxphoneattr" type="text" class="form-control" value="<?php echo isset($config['userfaxphoneattr']) ? $config['userfaxphoneattr'] : $defaults['userfaxphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-userfaxphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user fax.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-la"><?php echo _("User extension Link attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-la"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-la" data-default="<?php echo $defaults['la']?>" name="openldap-la" type="text" class="form-control" value="<?php echo isset($config['la']) ? $config['la'] : $defaults['la']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-la-help" class="help-block fpbx-help-block"><?php echo _("If this is set then User Manager will use the defined attribute of the user from the OpenLDAP server as the extension link. NOTE: If this field is set it will overwrite any manually linked extensions where this attribute extists!! (Try lowercase if it is not working.)")?></span>
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
							<label class="control-label" for="openldap-groupdnaddition"><?php echo _("Group DN")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupdnaddition"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupdnaddition" data-default="<?php echo $defaults['groupdnaddition']?>" name="openldap-groupdnaddition" type="text" class="form-control" value="<?php echo isset($config['groupdnaddition']) ? $config['groupdnaddition'] : $defaults['groupdnaddition']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupdnaddition-help" class="help-block fpbx-help-block"><?php echo _("This value is used in addition to the base DN when searching and loading groups. An example is ou=Groups. If no value is supplied, the subtree search will start from the base DN.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-groupobjectclass"><?php echo _("Group object class")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupobjectclass"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupobjectclass" data-default="<?php echo $defaults['groupobjectclass']?>" name="openldap-groupobjectclass" type="text" class="form-control" value="<?php echo isset($config['groupobjectclass']) ? $config['groupobjectclass'] : $defaults['groupobjectclass']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupobjectclass-help" class="help-block fpbx-help-block"><?php echo _("The LDAP user object class type to use when loading groups.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-groupobjectfilter"><?php echo _("Group object filter")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupobjectfilter"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupobjectfilter" data-default="<?php echo $defaults['groupobjectfilter']?>" name="openldap-groupobjectfilter" type="text" class="form-control" value="<?php echo isset($config['groupobjectfilter']) ? $config['groupobjectfilter'] : $defaults['groupobjectfilter']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupobjectfilter-help" class="help-block fpbx-help-block"><?php echo _("The filter to use when searching group objects.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-groupnameattr"><?php echo _("Group name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupnameattr" data-default="<?php echo $defaults['groupnameattr']?>" name="openldap-groupnameattr" type="text" class="form-control" value="<?php echo isset($config['groupnameattr']) ? $config['groupnameattr'] : $defaults['groupnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-groupdescriptionattr"><?php echo _("Group description attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupdescriptionattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupdescriptionattr" data-default="<?php echo $defaults['groupdescriptionattr']?>" name="openldap-groupdescriptionattr" type="text" class="form-control" value="<?php echo isset($config['groupdescriptionattr']) ? $config['groupdescriptionattr'] : $defaults['groupdescriptionattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group description.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-groupmemberattr"><?php echo _("Group members attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupmemberattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupmemberattr" data-default="<?php echo $defaults['groupmemberattr']?>" name="openldap-groupmemberattr" type="text" class="form-control" value="<?php echo isset($config['groupmemberattr']) ? $config['groupmemberattr'] : $defaults['groupmemberattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupmemberattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group members.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="openldap-groupexternalidattr"><?php echo _("Group unique identifier attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="openldap-groupexternalidattr"></i>
						</div>
						<div class="col-md-9">
							<input id="openldap-groupexternalidattr" data-default="<?php echo $defaults['groupexternalidattr']?>" name="openldap-groupexternalidattr" type="text" class="form-control" value="<?php echo isset($config['groupexternalidattr']) ? $config['groupexternalidattr'] : $defaults['groupexternalidattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="openldap-groupexternalidattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use for tracking group identity across group renames.")?></span>
			</div>
		</div>
	</div>
</fieldset>
<style>
	#openldap-status {
		padding: 5px;
		border-radius: 5px;
		margin-top: 5px;
	}
</style>
