<?php
$userman = setup_userman();
$users = $userman->getAllUsers();
$userrows = '';
foreach($users as $row){
$uid = $row['id'];
$username = $row['username'];	
$displayname = $row['dn'];	
$extension = $row['default_extension'];	
$description = $row['description'];
$userrows .= <<<HERE
<tr id = "row$uid">
<td><input type = "checkbox" class="" id="actonthis$uid" name="actionList[]" value="$uid"></td>
<td>$username</td>
<td>$displayname</td>
<td>$extension</td>
<td>$description</td>
<td><a href="config.php?display=userman&action=showuser&user=$uid"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<i class="fa fa-key"></i>&nbsp;&nbsp;<a href="#" id="del$uid" data-uid="$uid" ><i class="fa fa-trash-o"></i></a></td>
</tr>
HERE;
}
?>
<div class="table-responsive"> 
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
		  <th><input type="checkbox" class="" id="action-toggle-all"></th>
          <th><?php echo _("Username") ?></th>
          <th><?php echo _("Display Name") ?></th>
          <th><?php echo _("Extension") ?></th>
          <th><?php echo _("Description") ?></th>
          <th><?php echo _("Action") ?></th>
        </tr>
      </thead>
      <tbody>
		  <?php echo $userrows ?>
      </tbody>
    </table>
  </div>
