<?php switch($action) {
	case "adduser":
	case "showucptemplate":
	case "adducptemplate" :
	case "showmembers" :
	case "showuser": ?>
	<div id="toolbar-all">
		<a href="?display=userman#users" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Users")?></a>
		<a href="?display=userman#ucptemplates" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Templates")?></a>
		<?php if($permissions['addUser']) { ?>
			<a href="?display=userman&amp;action=adduser&amp;directory=<?php echo $directory?>" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo _("Add User")?></a>
		<?php } ?>
	</div>
	<table id="user-side" data-url="ajax.php?module=userman&amp;command=getUsers&amp;directory=<?php echo $directory?>" data-cache="false" data-toolbar="#toolbar-all" data-toggle="table" data-search="true" class="table">
		<thead>
			<tr>
				<th data-field="username"><?php echo _("Username")?></th>
				<th data-field="description"><?php echo _("Description")?></th>
			</tr>
		</thead>
	</table>
	<?php break;
	case "addgroup":
	case "showgroup":?>
	<div id="toolbar-all">
		<a href="?display=userman#groups" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Groups")?></a>
		<?php if($permissions['addGroup']) { ?>
			<a href="?display=userman&amp;action=addgroup&amp;directory=<?php echo $directory?>" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo _("Add Group")?></a>
		<?php } ?>
	</div>
	<table id="group-side" data-url="ajax.php?module=userman&amp;command=getGroups&amp;directory=<?php echo $directory?>" data-cache="false" data-toolbar="#toolbar-all" data-toggle="table" data-search="true" class="table">
		<thead>
			<tr>
				<th data-field="groupname"><?php echo _("Group Name")?></th>
				<th data-field="description"><?php echo _("Description")?></th>
			</tr>
		</thead>
	</table>
	<?php break;
	case "adddirectory":
	case "showdirectory":?>
	<div id="toolbar-all">
		<a href="?display=userman#directories" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Directories")?></a>
		<a href="?display=userman&amp;action=adddirectory" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo _("Add Directory")?></a>
	</div>
	<table id="directory-side" data-url="ajax.php?module=userman&amp;command=getDirectories" data-cache="false" data-toolbar="#toolbar-all" data-toggle="table" data-search="true" class="table">
		<thead>
			<tr>
				<th data-field="name"><?php echo _("Directory Name")?></th>
			</tr>
		</thead>
	</table>
	<?php break;
	case "showcallactivitygroup":
	case "addcallactivitygroup":?>
	<div id="toolbar-all">
		<a href="?display=userman#users" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Users")?></a>
		<a href="?display=userman#call_activity_groups" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Call Activity Groups")?></a>
		<a href="?display=userman&amp;action=addcallactivitygroup&amp;" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo _("Add Call Activity Group")?></a>
	</div>
	<table id="call-activity-groups-side" data-url="ajax.php?module=userman&amp;command=getCallActivityGroups&amp;" data-cache="false" data-toolbar="#toolbar-all" data-toggle="table" data-search="true" class="table">
		<thead>
			<tr>
				<th data-field="groupname"><?php echo _("Call Activity Group Name")?></th>
				<th data-field="description"><?php echo _("Description")?></th>
			</tr>
		</thead>
	</table>
	<?php break; }?>