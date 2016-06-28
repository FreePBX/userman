var deleteExts = {
	'users': [],
	'groups': []
},
translations = {
	'user': _('user'),
	'users': _('users'),
	'group': _('group'),
	'groups': _('groups')
};
$("#email-users").click(function() {
	$(this).prop("disabled",true);
	$.post( "ajax.php", {command: "email", module: "userman", extensions: deleteExts.users}, function(data) {
		if(data.status) {
			alert(_("Email Sent"));
		} else {
			alert(data.message);
		}
		$(this).prop("disabled",false);
	});
});
$(".btn-remove").click(function() {
	var type = $(this).data("type"), btn = $(this), section = $(this).data("section");
	var chosen = $("#table-"+section).bootstrapTable("getSelections");
	$(chosen).each(function(){
		deleteExts[type].push(this['id']);
	});
	if(confirm(sprintf(_("Are you sure you wish to delete these %s?"),translations[type]))) {
		btn.find("span").text(_("Deleting..."));
		btn.prop("disabled", true);
		$.post( "ajax.php", {command: "delete", module: "userman", extensions: deleteExts[type], type: type}, function(data) {
			if(data.status) {
				btn.find("span").text(_("Delete"));
				$("#table-"+section).bootstrapTable('remove', {
					field: "id",
					values: deleteExts[type]
				});
			} else {
				btn.find("span").text(_("Delete"));
				btn.prop("disabled", true);
				alert(data.message);
			}
		});
	}
});
$("#table-groups").on("reorder-row.bs.table", function (table,rows) {
	var order = {};
	$.each(rows, function(k, v) {
		order[k] = v.id;
	});
	$.post( "ajax.php", {command: "updateSort", module: "userman", sort: JSON.stringify(order)}, function(data) {
		$("#table-groups").bootstrapTable('refresh');
	});
});
$("table").on("post-body.bs.table", function () {
	$("table .fa-trash-o").off("click");
	$("table .fa-trash-o").click(function() {
		var id = $(this).data("id"), section = $(this).data("section"), type = $(this).parents("table").data("type");
		if(confirm(sprintf(_("Are you sure you wish to delete this %s?"),translations[type]))) {
			$.post( "ajax.php", {command: "delete", module: "userman", extensions: [id], type: type}, function(data) {
				if(data.status) {
					$("#table-"+section).bootstrapTable('remove', {
						field: "id",
						values: [id.toString()]
					});
				} else {
					btn.find("span").text(_("Delete"));
					btn.prop("disabled", true);
					alert(data.message);
				}
			});
		}
	});
});
$("table").on("page-change.bs.table", function () {
	$(".btn-remove").prop("disabled", true);
	deleteExts['users'] = [];
	deleteExts['groups'] = [];
});
$("table").on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
	var toolbar = $(this).data("toolbar"),
			button = $(toolbar).find(".btn-remove"),
			buttone = $(toolbar).find(".btn-send"),
			id = $(this).prop("id"),
			type = $(this).data("type");
	button.prop('disabled', !$("#"+id).bootstrapTable('getSelections').length);
	buttone.prop('disabled', !$("#"+id).bootstrapTable('getSelections').length);
	deleteExts[type] = $.map($("#"+id).bootstrapTable('getSelections'), function (row) {
		return row.id;
  });
});

$("#submitsend").click(function(e) {
	e.stopPropagation();
	e.preventDefault();
	$("input[name=submittype]").val("guisend");
	$(".fpbx-submit").submit();
});

//from http://stackoverflow.com/a/26744533 loads url params to an array
var params={};window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(str,key,value){params[key] = value;});
//Tab and Button stuff
$( document ).ready(function() {
	var hash = (window.location.hash != "") ? window.location.hash : "users";
	if(hash == '#settings'){
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="submitsend"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$("#action-bar").removeClass("hidden");
	} else if(params['action'] == 'adduser' || params['action'] == 'showuser'){
		$('input[name="submitsend"]').removeClass('hidden');
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$('input[name="delete"]').removeClass('hidden');
	}else if(params['action'] == 'addgroup' || params['action'] == 'showgroup') {
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$('input[name="delete"]').removeClass('hidden');
	} else {
		$("#action-bar").addClass("hidden");
	}

	$(".nav-tabs a[href="+hash+"]").tab('show');
	//we should be at the user tab by default so we will show add user.
});
//this fires when you change tabs
$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
	//Button Related
	switch(e.target.hash){
		case "#settings":
			$("#action-bar").removeClass("hidden");
			$('input[name="submit"]').removeClass('hidden');
			$('input[name="reset"]').removeClass('hidden');
		break;
		case "#users":
			$("#action-bar").addClass("hidden");
			$('input[name="submit"]').addClass('hidden');
			$('input[name="reset"]').addClass('hidden');
		break;
		case "#groups":
			$("#action-bar").addClass("hidden");
			$('input[name="submit"]').addClass('hidden');
			$('input[name="reset"]').addClass('hidden');
		break;
		default:
			return;
	}
	//Add hash to url for reloading
	window.location.hash = e.target.hash.replace();
});

//Making Password Modal work
$(document).on("click", 'a[id^="pwmlink"]', function(){
	var pwuid = $(this).data('pwuid');
	console.log(pwuid);
	$("#pwuid").val(pwuid);
	$("#pwsub").attr("disabled", false);
	$("#pwsub").html(_("Update Password"));
});
$("#pwsub").on("click", function(){
	var button = $(this);
	button.html(_('Updating'));
	button.attr("disabled", true);
	var uid = $("#pwuid").val();
	var pass = $("#password").val();
	$.ajax({
		url: "ajax.php",
		data: {
			module:'userman',
			command:'updatePassword',
			id: uid,
			newpass: pass
		},
		type: "GET",
		dataType: "json",
		success: function(data){
			console.log(data);
				button.html(data.message);

		},
		error: function(xhr, status, e){
			console.dir(xhr);
			console.log(status);
			console.log(e);
		}
	});
});

function userActions(value, row, index) {
	var html = '<a href="?display=userman&amp;action=showuser&amp;user='+row.id+'"><i class="fa fa-edit"></i></a>';

	if(permissions.changePassword) {
		html += '<a data-toggle="modal" data-pwuid="'+row.id+'" data-target="#setpw" id="pwmlink'+row.id+'" class="clickable"><i class="fa fa-key"></i></a>';
	}

	if(permissions.removeUser) {
		html += '<a class="clickable"><i class="fa fa-trash-o" data-section="users" data-id="'+row.id+'"></i></a>';
	}
	return html;
}

function groupActions(value, row, index) {
	var html = '<a href="?display=userman&amp;action=showgroup&amp;group='+row.id+'"><i class="fa fa-edit"></i></a>';

	if(permissions.removeGroup) {
		html += '<a class="clickable"><i class="fa fa-trash-o" data-section="groups" data-id="'+row.id+'"></i></a>';
	}
	return html;
}

$("#user-side").on("click-row.bs.table", function(row, $element) {
	window.location = "?display=userman&action=showuser&user="+$element.id;
});

$("#group-side").on("click-row.bs.table", function(row, $element) {
	window.location = "?display=userman&action=showgroup&group="+$element.id;
});
