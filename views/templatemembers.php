<?php 
function showMiddle() {
	$ret = "<span class='col-sm-2 middle'>\n";
	$ret .= "<button class='btn toggle' data-cmd='allleft'> <i class='fa fa-arrow-left' ></i></button><br/>";
	$ret .= "<button class='btn toggle' data-cmd='swap'> <i class='fa fa-arrows-h'></i></button><br/>";
	$ret .= "<button class='btn toggle' data-cmd='allright'><i class='fa fa-arrow-right'></i></button><br/>";
	$ret .= "</span>\n";
	return $ret;
}
?>
<div class="fpbx-container">
	<h1>UCP Templates Users</h1>
	<?php 
		if(!is_array($members['members']) || count($members['members']) == 0){
	?>
	<div class="alert alert-warning">
		<?php echo _("There is NO members associated with this template. Please use Groups->UCP->General or User->UCP->General To associate this template to Users .");?>
	</div>
	<?php
		}
	?>
	<form method='post' id="form_usertemplate" name ="form_usertemplate" action='config.php?display=userman' class="fpbx-submit" >
	<input type="hidden" name="type" value="rebuilducp">
	<input type="hidden" name="submittype" value="gui">
	<input type="hidden" name="templateid" value=<?php echo $templateid;?>>
	<ul class="nav nav-tabs pb-0" id="Users" role="tablist">
		<li data-name="users" class="change-tab"><a href="#users" role="tab" class="nav-link active" data-toggle="tab"><?php echo $name ?></a></li>
	</ul>
	<div class="tab-content display">
		<div id='users' class='tab-pane active'>
			<div class = "row">
				<fieldset class='users_list ui-sortable left col-sm-5' id='users_deny' data-otherid='users_allow'>
					<legend> <?php echo _("Members")?> </legend>
					<?php 
					if(!is_array($members['members'])){
						$members['members'] = [];
					}
					foreach ($members['members'] as $u) {
						echo "    <span class='dragitem' data-userid='".$u['id']."'>".$u['username']."</span>\n";
					}
					?>
					</fieldset>
					<?php echo showMiddle(); ?>
					<fieldset class='users_list ui-sortable right col-sm-5' id='users_allow' data-otherid='users_deny'>
					<legend> <?php echo _("Force Rebuild Templates For Users")?> </legend>
				</fieldset>
			</div>
		</div>
	</div>
	</form>
</div>
<script type='text/javascript'>

	$(document).ready(function() {	
		$("#form_usertemplate button").click(function(ev){
	});
	Sortable.create(users_allow, {
		group: 'usr',
		multiDrag: true,
		selectedClass: "selected",
		filter: "legend",
	});

	Sortable.create(users_deny, {
		group: 'usr',
		multiDrag: true,
		selectedClass: "selected",
		filter: "legend",
	});
	$(window).resize(function() { set_height(); });
	$('.dragitem').click(function(e){
		$(this).addClass('filtered-item');
	})
	function set_height() {
			var height = 0;
			$("#tabs>.tab:visible").height('auto').each(function(){
				console.log($(this).height());
				height = $(this).height() > height ? $(this).height() : height;
			}).height(height);
	}
	$('.toggle').click(function(e) {
		e.preventDefault();
		var cmd=$(this).data('cmd');
		var tabname = $(".nav-tabs .active").parent().data('name');
		var thistab = $('#'+tabname).children();
		var left = thistab.children('.left');
		var right = thistab.children('.right');
		if (cmd == 'allleft') {
			let className = (right.children(".filtered-item").length > 0) ? ".filtered-item" : "span";
			right.children(className).each(function() { $(this).appendTo(left); });
			left.children().removeClass('filtered-item');
		} else if (cmd == 'allright') {
			let className = (left.children(".filtered-item").length > 0) ? ".filtered-item" : "span";
			left.children(className).each(function() { $(this).appendTo(right); });
			right.children().removeClass('filtered-item');
		} else {
			oldleft = left.children('span');
			right.children('span').each(function() { $(this).appendTo(left); });
			oldleft.each(function() { $(this).appendTo(right); });
		}
	});
});

	
</script>
