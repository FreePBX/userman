<?php switch($action) { ?>
<?php case "adduser":
			case "showuser":?>
<div id="toolbar-all">
	<a href="?display=userman#users" class="btn btn-default"><i class="fa fa-list"></i> <?php echo _("List Users")?></a>
	<a href="?display=userman&amp;action=adduser" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo _("Add User")?></a>
</div>
 <table id="user-side" data-url="ajax.php?module=userman&amp;command=getUsers" data-cache="false" data-toolbar="#toolbar-all" data-toggle="table" data-search="true" class="table">
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
	<a href="?display=userman&amp;action=addgroup" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo _("Add Group")?></a>
</div>
 <table id="group-side" data-url="ajax.php?module=userman&amp;command=getGroups" data-cache="false" data-toolbar="#toolbar-all" data-toggle="table" data-search="true" class="table">
    <thead>
        <tr>
            <th data-field="groupname"><?php echo _("Group Name")?></th>
						<th data-field="description"><?php echo _("Description")?></th>
        </tr>
    </thead>
</table>
<?php break; }?>
