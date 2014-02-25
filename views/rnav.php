<div class='rnav'>
	<ul>
		<li class="rnav-heading">User List</li>
		<li><a href='config.php?display=userman'>Add New User</a></li>
		<li><hr></li>
		<?php foreach($users as $user) {?>
			<li><a href='config.php?display=userman&amp;action=showuser&amp;user=<?php echo $user['id']?>'><?php echo $user['username']?></a></li>
		<?php }?>
	</ul>
</div>