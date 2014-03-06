<h2><?php echo (isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser') ? _("Edit User") : _("Add User")?></h2>
<?php if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser') {?>
	<p>
		<a href="config.php?display=userman&amp;action=deluser&amp;user=<?php echo $user['id']?>">
			<span>
				<img width="16" height="16" border="0" title="<?php echo sprintf(_('Delete User %s'),$user['username'])?>" alt="<?php echo sprintf(_('Delete User %s'),$user['username'])?>" src="images/core_delete.png"><?php echo sprintf(_('Delete User %s'),$user['username'])?>
			</span>
		</a>
	</p>
<?php } ?>
<?php if(!empty($message)) {?>
	<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
<?php } ?>
<form autocomplete="off" name="editM" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
	<input type="hidden" name="prevUsername" value="<?php echo !empty($user['username']) ? $user['username'] : ''; ?>">
	<input type="hidden" name="user" value="<?php echo !empty($user['id']) ? $user['id'] : ''; ?>">
    <table>
        <tr class="guielToggle" data-toggle_class="userman">
            <td colspan="2"><h4><span class="guielToggleBut">-  </span><?php echo _("User Settings")?></h4><hr></td>
        </tr>
        <tr class="userman">
            <td><a href="#" class="info"><?php echo _("Username")?>:<span><?php echo _("This is the user login")?></span></a></td>
            <td><input type="text" name="username" maxlength="100" value="<?php echo !empty($user['username']) ? $user['username'] : ''; ?>"></td>
        </tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("This is the user description")?></span></a></td>
			<td><input type="text" name="description" maxlength="100" value="<?php echo !empty($user['description']) ? $user['description'] : ''; ?>"></td>
		</tr>
        <tr class="userman">
            <td><a href="#" class="info"><?php echo _("Password")?>:<span><?php echo _("This is the user's Password")?></span></a></td>
        	<td><input type="password" name="password" maxlength="150" value="<?php echo !empty($user['password']) ? '******' : ''; ?>"></td>
		</tr>
		<tr class="userman">
    		<td><a href="#" class="info"><?php echo _("Assigned Extensions")?>:<span><?php echo _("")?></span></a></td>
			<td>
				<div class="extensions-list">
				<?php foreach($fpbxusers as $fpbxuser) {?>
					<label><input class="extension-checkbox" data-name="<?php echo $fpbxuser['name']?>" data-extension="<?php echo $fpbxuser['ext']?>" type="checkbox" name="assigned[]" value="<?php echo $fpbxuser['ext']?>" <?php echo $fpbxuser['selected'] ? 'checked' : '' ?>> <?php echo $fpbxuser['name']?> &lt;<?php echo $fpbxuser['ext']?>&gt;</label><br />
				<?php } ?>
				</div>
			</td>
		</tr>
	</table>
	<?php echo $hookHtml;?>
	<table>
		<tr>
			<td colspan="2"><input type="submit" name="submit" value="Submit"></td>
		</tr>
	</table>
</form>
