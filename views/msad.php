<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-connection"><?php echo _("Secure Connection Type")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-connection"></i>
					</div>
					<div class="col-md-9">
						<select id="msad-connection" data-default="<?php echo $defaults['connection']?>" name="msad-connection" class="form-control">
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
			<span id="msad-connection-help" class="help-block fpbx-help-block"><?php echo _("The Active Directory secure connection type")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-host"><?php echo _("Host")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-host"></i>
					</div>
					<div class="col-md-9">
						<input id="msad-host" name="msad-host" data-default="<?php echo $defaults['host']?>" type="text" class="form-control" value="<?php echo isset($config['host']) ? $config['host'] : $defaults['host']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-host-help" class="help-block fpbx-help-block"><?php echo _("The active directory host")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-post"><?php echo _("Port")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-port"></i>
					</div>
					<div class="col-md-9">
						<input id="msad-port" data-default="<?php echo $defaults['port']?>" name="msad-port" type="text" class="form-control" value="<?php echo isset($config['port']) ? $config['port'] : $defaults['port']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-port-help" class="help-block fpbx-help-block"><?php echo _("The active directory port")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-host"><?php echo _("Username")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-username"></i>
					</div>
					<div class="col-md-9">
						<input id="msad-username" data-default="<?php echo $defaults['username']?>" name="msad-username" type="text" class="form-control" value="<?php echo isset($config['username']) ? $config['username'] : $defaults['username']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-username-help" class="help-block fpbx-help-block"><?php echo _("The active directory username")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-password"><?php echo _("Password")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-password"></i>
					</div>
					<div class="col-md-9">
						<input id="msad-password" data-default="<?php echo $defaults['password']?>" name="msad-password" type="text" class="form-control" value="<?php echo isset($config['password']) ? $config['password'] : $defaults['password']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-password-help" class="help-block fpbx-help-block"><?php echo _("The active directory password")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-domain"><?php echo _("Domain")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-domain"></i>
					</div>
					<div class="col-md-9">
						<input id="msad-domain" data-default="<?php echo $defaults['domain']?>" name="msad-domain" type="text" class="form-control" value="<?php echo isset($config['domain']) ? $config['domain'] : $defaults['domain']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-domain-help" class="help-block fpbx-help-block"><?php echo _("The active directory domain")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-dn"><?php echo _("Base DN")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-dn"></i>
					</div>
					<div class="col-md-9">
						<input id="msad-dn" data-default="<?php echo $defaults['dn']?>" name="msad-dn" type="text" class="form-control" value="<?php echo isset($config['dn']) ? $config['dn'] : $defaults['dn']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-dn-help" class="help-block fpbx-help-block"><?php echo _("The base DN. Usually in the format of CN=Users,DC=domain,DC=local")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="msad-status"><?php echo _("Status")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-status"></i>
					</div>
					<div class="col-md-9">
						<div id="msad-status" class="bg-<?php echo $status['type']?>"><?php echo $status['message']?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="msad-status-help" class="help-block fpbx-help-block"><?php echo _("The connection status of the Active Directory Server")?></span>
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
							<label class="control-label" for="msad-userdn"><?php echo _("User DN")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userdn"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userdn" data-default="<?php echo $defaults['userdn']?>" name="msad-userdn" type="text" class="form-control" value="<?php echo isset($config['userdn']) ? $config['userdn'] : $defaults['userdn']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userdn-help" class="help-block fpbx-help-block"><?php echo _("This value is used in addition to the base DN when searching and loading users. An example is ou=Users. If no value is supplied, the subtree search will start from the base DN.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userobjectclass"><?php echo _("User object class")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userobjectclass"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userobjectclass" data-default="<?php echo $defaults['userobjectclass']?>" name="msad-userobjectclass" type="text" class="form-control" value="<?php echo isset($config['userobjectclass']) ? $config['userobjectclass'] : $defaults['userobjectclass']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userobjectclass-help" class="help-block fpbx-help-block"><?php echo _("The LDAP user object class type to use when loading users.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userobjectfilter"><?php echo _("User object filter")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userobjectfilter"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userobjectfilter" data-default="<?php echo $defaults['userobjectfilter']?>" name="msad-userobjectfilter" type="text" class="form-control" value="<?php echo isset($config['userobjectfilter']) ? $config['userobjectfilter'] : $defaults['userobjectfilter']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userobjectfilter-help" class="help-block fpbx-help-block"><?php echo _("The filter to use when searching user objects.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-usernameattr"><?php echo _("User name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usernameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usernameattr" data-default="<?php echo $defaults['usernameattr']?>" name="msad-usernameattr" type="text" class="form-control" value="<?php echo isset($config['usernameattr']) ? $config['usernameattr'] : $defaults['usernameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usernameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use on the user object (eg. cn, sAMAccountName)")?></span>
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
							<label class="control-label" for="msad-usernamerdnattr"><?php echo _("User name RDN attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usernamerdnattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usernamerdnattr" data-default="<?php echo $defaults['usernamerdnattr']?>" name="msad-usernamerdnattr" type="text" class="form-control" value="<?php echo isset($config['usernamerdnattr']) ? $config['usernamerdnattr'] : $defaults['usernamerdnattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usernamerdnattr-help" class="help-block fpbx-help-block"><?php echo _("The RDN to use when loading the user username (eg. cn).")?></span>
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
							<label class="control-label" for="msad-userfirstnameattr"><?php echo _("User first name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userfirstnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userfirstnameattr" data-default="<?php echo $defaults['userfirstnameattr']?>" name="msad-userfirstnameattr" type="text" class="form-control" value="<?php echo isset($config['userfirstnameattr']) ? $config['userfirstnameattr'] : $defaults['userfirstnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userfirstnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user first name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userlastnameattr"><?php echo _("User last name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userlastnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userlastnameattr" data-default="<?php echo $defaults['userlastnameattr']?>" name="msad-userlastnameattr" type="text" class="form-control" value="<?php echo isset($config['userlastnameattr']) ? $config['userlastnameattr'] : $defaults['userlastnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userlastnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user last name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userdisplaynameattr"><?php echo _("User display name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userdisplaynameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userdisplaynameattr" data-default="<?php echo $defaults['userdisplaynameattr']?>" name="msad-userdisplaynameattr" type="text" class="form-control" value="<?php echo isset($config['userdisplaynameattr']) ? $config['userdisplaynameattr'] : $defaults['userdisplaynameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userdisplaynameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user full name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-usergroupmemberattr"><?php echo _("User group attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usergroupmemberattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usergroupmemberattr" data-default="<?php echo $defaults['usergroupmemberattr']?>" name="msad-usergroupmemberattr" type="text" class="form-control" value="<?php echo isset($config['usergroupmemberattr']) ? $config['usergroupmemberattr'] : $defaults['usergroupmemberattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usergroupmemberattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the users groups.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userexternalidattr"><?php echo _("User unique identifier attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userexternalidattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userexternalidattr" data-default="<?php echo $defaults['userexternalidattr']?>" name="msad-userexternalidattr" type="text" class="form-control" value="<?php echo isset($config['userexternalidattr']) ? $config['userexternalidattr'] : $defaults['userexternalidattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userexternalidattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use for tracking user identity across user renames.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-usermailattr"><?php echo _("User email attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usermailattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usermailattr" data-default="<?php echo $defaults['usermailattr']?>" name="msad-usermailattr" type="text" class="form-control" value="<?php echo isset($config['usermailattr']) ? $config['usermailattr'] : $defaults['usermailattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usermailattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user email.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userdescriptionattr"><?php echo _("User Description attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userdescriptionattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userdescriptionattr" data-default="<?php echo $defaults['userdescriptionattr']?>" name="msad-userdescriptionattr" type="text" class="form-control" value="<?php echo isset($config['userdescriptionattr']) ? $config['userdescriptionattr'] : $defaults['userdescriptionattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user description.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-usertitleattr"><?php echo _("User Title attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usertitleattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usertitleattr" data-default="<?php echo $defaults['usertitleattr']?>" name="msad-usertitleattr" type="text" class="form-control" value="<?php echo isset($config['usertitleattr']) ? $config['usertitleattr'] : $defaults['usertitleattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usertitleattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user title.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-usercompanyattr"><?php echo _("User Company attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usercompanyattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usercompanyattr" data-default="<?php echo $defaults['usercompanyattr']?>" name="msad-usercompanyattr" type="text" class="form-control" value="<?php echo isset($config['usercompanyattr']) ? $config['usercompanyattr'] : $defaults['usercompanyattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usercompanyattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user company.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userdepartmentattr"><?php echo _("User Department attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userdepartmentattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userdepartmentattr" data-default="<?php echo $defaults['userdepartmentattr']?>" name="msad-userdepartmentattr" type="text" class="form-control" value="<?php echo isset($config['userdepartmentattr']) ? $config['userdepartmentattr'] : $defaults['userdepartmentattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userdepartmentattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user department.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userhomephoneattr"><?php echo _("User Home Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userhomephoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userhomephoneattr" data-default="<?php echo $defaults['userhomephoneattr']?>" name="msad-userhomephoneattr" type="text" class="form-control" value="<?php echo isset($config['userhomephoneattr']) ? $config['userhomephoneattr'] : $defaults['userhomephoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userhomephoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user home phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userworkphoneattr"><?php echo _("User Work Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userworkphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userworkphoneattr" data-default="<?php echo $defaults['userworkphoneattr']?>" name="msad-userworkphoneattr" type="text" class="form-control" value="<?php echo isset($config['userworkphoneattr']) ? $config['userworkphoneattr'] : $defaults['userworkphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userworkphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user work phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-usercellphoneattr"><?php echo _("User Cell Phone attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-usercellphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-usercellphoneattr" data-default="<?php echo $defaults['usercellphoneattr']?>" name="msad-usercellphoneattr" type="text" class="form-control" value="<?php echo isset($config['usercellphoneattr']) ? $config['usercellphoneattr'] : $defaults['usercellphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-usercellphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user cell phone.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-userfaxphoneattr"><?php echo _("User Fax attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-userfaxphoneattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-userfaxphoneattr" data-default="<?php echo $defaults['userfaxphoneattr']?>" name="msad-userfaxphoneattr" type="text" class="form-control" value="<?php echo isset($config['userfaxphoneattr']) ? $config['userfaxphoneattr'] : $defaults['userfaxphoneattr']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-userfaxphoneattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the user fax.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-la"><?php echo _("User extension Link attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-la"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-la" data-default="<?php echo $defaults['la']?>" name="msad-la" type="text" class="form-control" value="<?php echo isset($config['la']) ? $config['la'] : $defaults['la']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-la-help" class="help-block fpbx-help-block"><?php echo _("If this is set then User Manager will use the defined attribute of the user from the Active Directory server as the extension link. NOTE: If this field is set it will overwrite any manually linked extensions where this attribute extists!! (Try lowercase if it is not working.)")?></span>
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
							<label class="control-label" for="msad-groupdnaddition"><?php echo _("Group DN")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupdnaddition"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupdnaddition" data-default="<?php echo $defaults['groupdnaddition']?>" name="msad-groupdnaddition" type="text" class="form-control" value="<?php echo isset($config['groupdnaddition']) ? $config['groupdnaddition'] : $defaults['groupdnaddition']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupdnaddition-help" class="help-block fpbx-help-block"><?php echo _("This value is used in addition to the base DN when searching and loading groups. An example is ou=Groups. If no value is supplied, the subtree search will start from the base DN.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-groupobjectclass"><?php echo _("Group object class")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupobjectclass"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupobjectclass" data-default="<?php echo $defaults['groupobjectclass']?>" name="msad-groupobjectclass" type="text" class="form-control" value="<?php echo isset($config['groupobjectclass']) ? $config['groupobjectclass'] : $defaults['groupobjectclass']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupobjectclass-help" class="help-block fpbx-help-block"><?php echo _("The LDAP user object class type to use when loading groups.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-groupobjectfilter"><?php echo _("Group object filter")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupobjectfilter"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupobjectfilter" data-default="<?php echo $defaults['groupobjectfilter']?>" name="msad-groupobjectfilter" type="text" class="form-control" value="<?php echo isset($config['groupobjectfilter']) ? $config['groupobjectfilter'] : $defaults['groupobjectfilter']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupobjectfilter-help" class="help-block fpbx-help-block"><?php echo _("The filter to use when searching group objects.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-groupnameattr"><?php echo _("Group name attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupnameattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupnameattr" data-default="<?php echo $defaults['groupnameattr']?>" name="msad-groupnameattr" type="text" class="form-control" value="<?php echo isset($config['groupnameattr']) ? $config['groupnameattr'] : $defaults['groupnameattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupnameattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group name.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-groupdescriptionattr"><?php echo _("Group description attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupdescriptionattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupdescriptionattr" data-default="<?php echo $defaults['groupdescriptionattr']?>" name="msad-groupdescriptionattr" type="text" class="form-control" value="<?php echo isset($config['groupdescriptionattr']) ? $config['groupdescriptionattr'] : $defaults['groupdescriptionattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupdescriptionattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group description.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-groupmemberattr"><?php echo _("Group members attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupmemberattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupmemberattr" data-default="<?php echo $defaults['groupmemberattr']?>" name="msad-groupmemberattr" type="text" class="form-control" value="<?php echo isset($config['groupmemberattr']) ? $config['groupmemberattr'] : $defaults['groupmemberattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupmemberattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use when loading the group members.")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="msad-groupexternalidattr"><?php echo _("Group unique identifier attribute")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="msad-groupexternalidattr"></i>
						</div>
						<div class="col-md-9">
							<input id="msad-groupexternalidattr" data-default="<?php echo $defaults['groupexternalidattr']?>" name="msad-groupexternalidattr" type="text" class="form-control" value="<?php echo isset($config['groupexternalidattr']) ? $config['groupexternalidattr'] : $defaults['groupexternalidattr']?>" required>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="msad-groupexternalidattr-help" class="help-block fpbx-help-block"><?php echo _("The attribute field to use for tracking group identity across group renames.")?></span>
			</div>
		</div>
	</div>
</fieldset>
<style>
	#msad-status {
		padding: 5px;
		border-radius: 5px;
		margin-top: 5px;
	}
</style>
