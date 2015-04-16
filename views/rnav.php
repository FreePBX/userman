<div class="list-group">
	<a href='config.php?display=userman' class="list-group-item"><i class="fa fa-th-list"></i>&nbsp;&nbsp;&nbsp;<?php echo _('User List')?></a>
	<a href='config.php?display=userman' class="list-group-item"><i class="fa fa-th-list"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Group List')?></a>
	<a href='config.php?display=userman' class="list-group-item"><i class="fa fa-th-list"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Permissions')?></a>
	<?php if($permissions['addUser']) {?>
		<a href='config.php?display=userman&amp;action=adduser' class="list-group-item"><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Add New User')?></a>
	<?php } ?>
	<?php if($permissions['addGroup']) {?>
		<a href='config.php?display=userman&amp;action=addgroup' class="list-group-item"><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Add New Group')?></a>
	<?php } ?>
	<a href='#' class="list-group-item hidden" id = "delchecked"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;&nbsp;<?php echo _('Delete Selected')?></a>
</div>
