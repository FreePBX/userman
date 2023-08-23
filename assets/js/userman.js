var deleteExts = {
	'users': [],
	'groups': [],
	'directories': []
},
translations = {
	'user': _('user'),
	'users': _('users'),
	'group': _('group'),
	'groups': _('groups'),
	'directory': _('directory'),
	'directories': _('directories')
};
if($("#directory").length) {
	$("#directory").submit(function(e) {
		if($("#name").val() === "") {
			return warnInvalid($("#name"),_("Name can not be blank!"));
		}
		$("#submit").prop("disabled",true);
	});
}
$("#pwd-templates-show").mouseenter(function(){
	$.post(window.FreePBX.ajaxurl, {command: "pwdTest", module: "userman", pwd: ""}, function(data) {
		if(data.templates){
			Tcontent = "<div class='container'>";
			$.each(data.templates, function(index, item) {
				Tcontent += "<li>"+index+" - <b>"+item+"</b></li>";				
			});
			Tcontent += '</div>';
			$("#pwd-templates").html(Tcontent);
		}
	});
});
$(".password-meter").keyup(function() {
	$.post(window.FreePBX.ajaxurl, {command: "pwdTest", module: "userman", pwd: this.value}, function(data) {
		if(data.status){
			$(".pwd-error").html("");
			$("#action-bar").show();
		}
		else{
			error_content = '<div class="alert alert-warning" role="alert">';
			$.each(data.error, function(index, item) {
				error_content += "<li>"+item+"</li>";				
			});
			error_content += '</div>';
			$(".pwd-error").html(error_content);
		}
	});
});
if($("#editT").length) {
        $("#editT").submit(function(e) {
            if($("#templatename").val().trim() === "") {
				return warnInvalid($("#templatename"),_("Template Name can not be blank!"));
			}
			if($("input[name='createtemp']:checked").val() == 'import') {
				if($("#userid").val().trim() === "") {
					return warnInvalid($("#userid"),_("Please select a user to import the settings !"));
				}
			}
            $("#submit").prop("disabled",true);
   });
}

$("#email-users").click(function() {
	var $this = this;
	$(this).prop("disabled",true);
	$.post(window.FreePBX.ajaxurl, {command: "email", module: "userman", extensions: deleteExts.users}, function(data) {
		fpbxToast(data.message, '', (data.status ? "success" : "error"));
		$($this).prop("disabled",false);
	});
});
$("#directory-users").change(function() {
	var val = $(this).val();
	$("#remove-users").attr('disabled', true);
	if(val === '') {
		$("#table-users").bootstrapTable('refresh',{url: window.FreePBX.ajaxurl + '?module=userman&command=getUsers'});
		$("#table-users").bootstrapTable('showColumn','auth');
		// $("#remove-users").removeClass("hidden");
		$("#remove-users").removeClass("btn-remove");
		$("#remove-users").attr('disabled', true);
		$("#remove-users").attr("title", _("Select Directory to enable 'Delete' Button"));
		$("#add-users").attr('disabled', true);
		$("#add-users").attr("title", _("Select Directory to enable 'Add' Button"));
		$("#add-users").attr("href", "#");
	} else {
		$("#remove-users").removeAttr('title');
		$("#add-users").attr("href","?display=userman&action=adduser&directory="+val);
		$("#table-users").bootstrapTable('refresh',{url: window.FreePBX.ajaxurl + '?module=userman&command=getUsers&directory='+$(this).val()});
		$("#table-users").bootstrapTable('hideColumn','auth');
		if(directoryMapValues[val].permissions.addUser) {
			$("#add-users").attr('disabled', false);
			$("#add-users").removeAttr('title');
		} else {
			$("#add-users").attr('disabled', true);
			$("#add-users").attr("title", _("Select Directory to enable 'Add' Button"));
			$("#add-users").attr("href", "#");
		}
		if(directoryMapValues[val].permissions.removeUser) {
			$("#remove-users").addClass("btn-remove");
			// $("#remove-users").removeClass("hidden");
		} else {
			// $("#remove-users").addClass("hidden");
		}
	}
});
$("#directory-groups").change(function() {
	var val = $(this).val();
	$("#remove-groups").attr('disabled', true);
	if(val === '') {
		$("#table-groups").bootstrapTable('refresh',{url: window.FreePBX.ajaxurl + '?module=userman&command=getGroups'});
		$("#table-groups").bootstrapTable('showColumn','auth');
		// $("#remove-groups").removeClass("hidden");
		$("#remove-groups").removeClass("btn-remove");
		$("#remove-groups").attr('disabled', true);
		$("#remove-groups").attr("title", _("Select Directory to enable 'Delete' Button"));
		$("#add-groups").attr('disabled', true);
		$("#add-groups").addClass('addgrpdisable');
		$("#add-groups").attr("title", _("Select Directory to enable 'Add' Button"));
		$("#add-groups").attr("href", "#");
	} else {
		$("#remove-groups").removeAttr('title');
		$("#add-groups").attr("href","?display=userman&action=addgroup&directory="+val);
		$("#table-groups").bootstrapTable('refresh',{url: window.FreePBX.ajaxurl + '?module=userman&command=getGroups&directory='+$(this).val()});
		$("#table-groups").bootstrapTable('hideColumn','auth');
		if(directoryMapValues[val].permissions.addGroup) {
			$("#add-groups").attr('disabled', false);
			$("#add-groups").removeClass('addgrpdisable');
			$("#add-groups").removeAttr('title');
		} else {
			$("#add-groups").attr('disabled', true);
			$("#add-groups").addClass('addgrpdisable');
			$("#add-groups").attr("title", _("Select Directory to enable 'Add' Button"));
			$("#add-groups").attr("href", "#");
		}
		if(directoryMapValues[val].permissions.removeGroup) {
			$("#remove-groups").addClass("btn-remove");
			// $("#remove-groups").removeClass("hidden");
		} else {
			// $("#remove-groups").addClass("hidden");
		}
	}
});
$(document).on('click', "button.btn-remove", function() {
	var type = $(this).data("type"), btn = $(this), section = $(this).data("section");
	var chosen = $("#table-"+section).bootstrapTable("getSelections");
	$(chosen).each(function(){
		deleteExts[type].push(this.id);
	});
	if($("#remove-"+type).prop("disabled") === false){ // <--- Fixe the Delete button issue with Chrome
		fpbxConfirm(
			sprintf(_("Are you sure you wish to delete these %s?"), translations[type]),
			_("Yes"), _("No"),
			function() {
				btn.find("span").text(_("Deleting..."));
				btn.prop("disabled", true);
				$.post( window.FreePBX.ajaxurl, {command: "delete", module: "userman", extensions: deleteExts[type], type: type}, function(data) {
					if(data.status) {
						btn.find("span").text(_("Delete"));
						$("#table-"+section).bootstrapTable('remove', {
							field: "id",
							values: deleteExts[type]
						});
						$("#table-users").bootstrapTable('refresh');
						$("#table-groups").bootstrapTable('refresh');
					} else {
						btn.find("span").text(_("Delete"));
						btn.prop("disabled", true);
						fpbxToast(data.message, '', 'error');
					}
				});
			}
		);
	}
});
$("#table-groups").on("reorder-row.bs.table", function (table,rows) {
	var order = {};
	$.each(rows, function(k, v) {
		order[k] = v.id;
	});
	$.post( window.FreePBX.ajaxurl, {command: "updateGroupSort", module: "userman", sort: JSON.stringify(order)}, function(data) {
		$("#table-groups").bootstrapTable('refresh');
	});
});
$("#table-directories").on("reorder-row.bs.table", function (table,rows) {
	var order = {};
	$.each(rows, function(k, v) {
		order[k] = v.id;
	});
	$.post( window.FreePBX.ajaxurl, {command: "updateDirectorySort", module: "userman", sort: JSON.stringify(order)}, function(data) {
		$("#table-directories").bootstrapTable('refresh');
	});
});
$("table").on("post-body.bs.table", function () {
	$("table .fa-trash-o").off("click");
	$("table .fa-trash-o").click(function() {
		var id = $(this).data("id"), section = $(this).data("section"), type = $(this).parents("table").data("type"), trans = $(this).data("type");
		fpbxConfirm(
			sprintf(_("Are you sure you wish to delete this %s?"), translations[trans]),
			_("Yes"), _("No"),
			function() {
				$.post( window.FreePBX.ajaxurl, {command: "delete", module: "userman", extensions: [id], type: type}, function(data) {
					if(data.status) {
						$("#table-"+section).bootstrapTable('remove', {
							field: "id",
							values: [id.toString()]
						});
					} else {
						fpbxToast(data.message, '', 'error');
					}
				});
			}
		);
	});
});
$("#table-directories").on("post-body.bs.table", function () {
	$(".default-check").click(function() {
		var $this = this;
		var id = $(this).data("id");
		if($(this).data("from") == 'directory') {
			fpbxConfirm(
				_("Are you sure you want to make this directory the system default?"),
				_("Yes"), _("No"),
				function() {
					$.post( window.FreePBX.ajaxurl, {command: "makeDefault", module: "userman", id: id}, function( data ) {
						if(data.status) {
							$(".default-check").removeClass("check");
							$($this).addClass("check");
						}
						fpbxToast(data.message, '', (data.status ? "success" : "error"));
					});
				}
			);
		}
	});
});
$("table").on("page-change.bs.table", function () {
	$(".btn-remove").prop("disabled", true);
	deleteExts.users = [];
	deleteExts.groups = [];
});
$("table").on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table load-success.bs.table load-error.bs.table', function () {
	var toolbar = $(this).data("toolbar"),
		button = $(toolbar).find(".btn-remove"),
		buttone = $(toolbar).find(".btn-send"),
		id = $(this).prop("id"),
		type = $(this).data("type");

	$("#remove-"+type).prop("disabled", false);
	button.prop('disabled', !$("#"+id).bootstrapTable('getSelections').length);
	buttone.prop('disabled', !$("#"+id).bootstrapTable('getSelections').length);
	deleteExts[type] = $.map($("#"+id).bootstrapTable('getSelections'), function (row) {
		if(row.auth in directoryMapValues && !directoryMapValues[row.auth].permissions.removeUser) {
			fpbxToast(_("Deletion is not allowed if a selected item is read-only !!"),_("Alert"),'error');
			$("#remove-"+type).prop("disabled", true);
		}
		return row.id;
  	});
});

$("#submit").click(function(e) {
	e.stopPropagation();
	e.preventDefault();
	var invalid = false;
	$('.fpbx-submit input').map(function(index, e) {
		var conteiner = $(e).parents(".element-container");
		$(conteiner).removeClass("has-error");
		$(conteiner).find(".input-warn").remove();
		if (! this.validity.valid) {
			$(conteiner).addClass("has-error");
			$(e).before('<i class="fa fa-exclamation-triangle input-warn" data-type="input" data-toggle="tooltip" data-placement="left" title="'+_('Required!')+'"></i>');
		}
		if(!this.validity.valid && !invalid) {
			fpbxToast(_("Please fill all missing fields"), '', "error");
			invalid = true;
		}
	});
	if(!invalid) {
		if($("form.fpbx-submit").attr("name") === "directory" || $("form.fpbx-submit").attr("name") === "editT") {
			$(".fpbx-submit").submit();
		} else {
			setLocales(function() {
				$(".fpbx-submit").submit();
			});
		}
	}
	return false;
});

$("#submitsend").click(function(e) {
	e.stopPropagation();
	e.preventDefault();
	var invalid = false;
	$('.fpbx-submit input').map(function() {
		if(!this.validity.valid && !invalid) {
			warnInvalid($(this),_("Please fill all missing fields"));
			invalid = true;
		}
	});
	$("input[name=submittype]").val("guisend");
	if(!invalid) {
		setLocales(function() {
			$(".fpbx-submit").submit();
		});
	}
	return false;
});

function setLocales(callback) {
	if(!$("#editM").length) {
		callback();
	}
	var type = $("form input[name=type]").val(), id = (type == "user") ? $("form input[name=user]").val() : $("form input[name=group]").val();
	var data = {
		command: "setlocales",
		module: "userman",
		timezone: $("#timezone").val(),
		language: $("#language").val(),
		datetimeformat: $("#datetimeformat").val(),
		timeformat: $("#timeformat").val(),
		dateformat: $("#dateformat").val(),
		id: id,
		type: type
	};
	$.post( window.FreePBX.ajaxurl, data, function(data) {
		if(data.status) {
			if(typeof callback === "function") {
				callback();
			}
		}
	});
}

//from http://stackoverflow.com/a/26744533 loads url params to an array
var params={};window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(str,key,value){params[key] = value;});
//Tab and Button stuff
$( document ).ready(function() {
	var hash = (window.location.hash !== "") ? window.location.hash : "users";
	if(hash == '#settings'){
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="submitsend"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$("#action-bar").removeClass("hidden");
	} else if(params.action == 'adduser' || params.action == 'showuser'){
		$('input[name="submitsend"]').removeClass('hidden');
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$('input[name="delete"]').removeClass('hidden');
	}else if(params.action == 'addgroup' || params.action == 'showgroup' || params.action == 'adddirectory' || params.action == 'showdirectory' || params.action == 'adducptemplate' ||  params.action == 'showucptemplate') {
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$('input[name="delete"]').removeClass('hidden');
	} else if(params.action == "showmembers"){
		$('input[name="cancel"]').removeClass('hidden');
		$('input[name="merge"]').removeClass('hidden');
		$('input[name="rebuild"]').removeClass('hidden');
	}
	else {
		$("#action-bar").addClass("hidden");
	}
	if(params.action == 'adducptemplate'){
		$("#tempcreatediv").hide()
	}
	$(".nav-tabs a[href="+hash+"]").tab('show');
	//we should be at the user tab by default so we will show add user.
	//on first load of groups tab disable add button as all directories is selected by default
	$("#add-groups").addClass('addgrpdisable');
});
//this fires when you change tabs
$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
	//Button Related
	switch(e.target.hash){
		case "#directories":
			$("#action-bar").addClass("hidden");
			$('input[name="submit"]').addClass('hidden');
			$('input[name="reset"]').addClass('hidden');
		break;
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
			// onlyOneGroup();
		break;
		case "#ucptemplates":
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
		url: window.FreePBX.ajaxurl,
		data: {
			module:'userman',
			command:'updatePassword',
			id: uid,
			newpass: pass
		},
		type: "POST",
		dataType: "json",
		success: function(data){
			if(data.status){
				button.html(data.message);
			}else{
				button.html(_('Update Password'));
				button.attr("disabled", false);
			}
		},
		error: function(xhr, status, e){
			console.dir(xhr);
			console.log(status);
			console.log(e);
		},
		always: function() {
			button.attr("disabled", false);
		}
	});
});

$('#group_primary').multiselect({
	maxHeight: 300,
	enableFiltering: true,
	enableCaseInsensitiveFiltering: true
});
$('#group_users').multiselect({
	maxHeight: 300,
	includeSelectAllOption: true,
	enableFiltering: true,
	enableCaseInsensitiveFiltering: true,
	selectAllValue: 'select-all-value',
	onChange: function (element, checked) {
		var users = $('#group_users option:selected');
		var selected = [];
		$(users).each(function (index, user) {
			selected.push([$(this).val()]);
		});
		$("#users").val(selected.toString());
	}
});
$('#defaultextension').multiselect({
	maxHeight: 300,
	enableFiltering: true,
	enableCaseInsensitiveFiltering: true
});

// function onlyOneGroup(){
// 	if($("#directory-groups option").length == 2 && $("#directory-groups option:selected" ).text() != ""){
// 		$("#add-groups").removeClass("hidden");
// 	}	
// }

function directoryMap(value, row, index) {
	if (value in directoryMapValues) {
		return directoryMapValues[value].name;
	}
}

function directoryActions(value, row, index) {
	var html = '<a href="?display=userman&amp;action=showdirectory&amp;directory='+row.id+'"><i class="fa fa-edit"></i></a>';
	html += '<a class="clickable"><i class="fa fa-trash-o" data-section="directories" data-type="directory" data-id="'+row.id+'"></i></a>';
	return html;
}

function directoryType(value, row, index) {
	return drivers[row.driver].name;
}

function directoryActive(value, row, index) {
	return (value == 1) ? _('True') : _('False');
}

function userActions(value, row, index) {
	var html = '<a href="?display=userman&amp;action=showuser&amp;user='+row.id+'&amp;directory='+row.auth+'"><i class="fa fa-edit"></i></a>';
	if(row.auth in directoryMapValues && directoryMapValues[row.auth].permissions.changePassword) {
		html += '<a data-toggle="modal" data-pwuid="'+row.id+'" data-target="#setpw" id="pwmlink'+row.id+'" class="clickable"><i class="fa fa-key"></i></a>';
	}

	if(row.auth in directoryMapValues && directoryMapValues[row.auth].permissions.removeUser) {
		html += '<a class="clickable"><i class="fa fa-trash-o" data-section="users" data-type="user"  data-id="'+row.id+'"></i></a>';
	}
	return html;
}

function groupActions(value, row, index) {
	var html = '<a href="?display=userman&amp;action=showgroup&amp;group='+row.id+'&amp;directory='+row.auth+'"><i class="fa fa-edit"></i></a>';

	if(row.local == "1" || directoryMapValues[row.auth].permissions.removeGroup) {
		html += '<a class="clickable"><i class="fa fa-trash-o" data-section="groups" data-type="group"  data-id="'+row.id+'"></i></a>';
	}
	return html;
}

function updateTimeDisplay() {
	var userdtf = $("#datetimeformat").val();
	userdtf = (userdtf !== "") ? userdtf : datetimeformat;
	$("#datetimeformat-now").text(moment().tz(timezone).format(userdtf));

	var usertf = $("#timeformat").val();
	usertf = (usertf !== "") ? usertf : timeformat;
	$("#timeformat-now").text(moment().tz(timezone).format(usertf));

	var userdf = $("#dateformat").val();
	userdf = (userdf !== "") ? userdf : dateformat;
	$("#dateformat-now").text(moment().tz(timezone).format(userdf));
}

if($("#datetimeformat-now").length) {
	updateTimeDisplay();
	setInterval(function() {
		updateTimeDisplay();
	},1000);
	$("#datetimeformat, #timeformat, #dateformat").keydown(function() {
		updateTimeDisplay();
	});
}

function defaultSelector(value, row, index) {
	return '<div class="default-check '+(row.default == "1" ? 'check' : '')+'" data-id="'+row.id+'" data-from = "directory"><i class="fa fa-check" aria-hidden="true"></i></div>';
}

$("#user-side").on("click-row.bs.table", function(row, $element) {
	window.location = "?display=userman&action=showuser&user="+$element.id;
});

$("#group-side").on("click-row.bs.table", function(row, $element) {
	window.location = "?display=userman&action=showgroup&group="+$element.id;
});
$("#browserlang").on("click", function(e){
	e.preventDefault();
	var bl =  browserLocale();
	bl = bl.replace("-","_");
	if(typeof bl === 'undefined'){
		fpbxToast(_("The Browser Language could not be determined"));
	}else{
		$("#language").multiselect('select', bl);
		$("#language").multiselect('refresh');
	}
});
$("#systemlang").on("click", function(e){
	e.preventDefault();
	var sl = fpbx.conf.UIDEFAULTLANG;
	if(typeof sl === 'undefined'){
		fpbxToast(_("The PBX Language is not set"));
	}else{
		$("#language").multiselect('select', sl);
		$("#language").multiselect('refresh');
	}
});
$("#browsertz").on("click", function(e){
	e.preventDefault();
	var btz =  moment.tz.guess();
	if(typeof btz === 'undefined'){
		fpbxToast(_("The Browser Timezone could not be determined"));
	}else{
		$("#timezone").multiselect('select', btz);
		$("#timezone").multiselect('refresh');
	}
});
$("#systemtz").on("click", function(e){
	e.preventDefault();
	var stz = fpbx.conf.PHPTIMEZONE;
	if(typeof stz === 'undefined'){
		fpbxToast(_("The PBX Timezone is not set"));
	}else{
		$("#timezone").multiselect('select', stz);
		$("#timezone").multiselect('refresh');
	}
});

$("#directory-side").on("click-row.bs.table", function(row, $element) {
	window.location = "?display=userman&action=showdirectory&directory="+$element.id;
});

function ucptemplatesActions(value, row, index) {
	var unlockKey = "'" + row.key + "'";
	var html = '<a href="?display=userman&amp;action=showucptemplate&amp;template='+row.id+'" title="Reimport from a user"><i class="fa fa-edit"> </i></a>';
	html += '<a class="clickable" onclick="return redirectToUCP('+row.id+','+unlockKey+')" title="Edit Template"><i class="fa fa-eye"" data-section="ucptemplates" data-type="ucptemplates"  data-id="'+row.id+'"></i></a>';
	html += '<a href="?display=userman&amp;action=showmembers&amp;template='+row.id+'" title="Force Rebuild widgets"><i class="fa fa-refresh" data-section="ucptemplates" data-type="ucptemplates"  data-id="'+row.id+'"></i></a>';
	html += '<a class="clickable" title="delete a template"><i class="fa fa-trash-o" data-section="ucptemplates" data-type="ucptemplates"  data-id="'+row.id+'"></i></a>';
	return html;
}
function rowStyle(row, index){
	var style = (row.hasupdated === "1") ? { css: { background: '#faebcc'} } : { css: { background: 'none'} }
	return style;
}
function rebuildwidgets(id) {
	fpbxConfirm(
		_("Are you sure rebuild all Users widgets associated with this Template?"),
		_("Yes"), _("No"),
		function() {
			$.post(window.FreePBX.ajaxurl, {command: "rebuildtemplate", module: "userman", templateid: id}, function( data ) {
				fpbxToast(data.message, '', (data.status ? "success" : "error"));
			});	
		}
	);
}

function redirectToUCP(id, key) {
	$.post(window.FreePBX.ajaxurl, {command: "redirectUCP", module: "userman", id: id, key: key}, function( data ) {
		if(data.status) {
			if(key == false){
				key = data.key;
			}
			var url = `/ucp/index.php?unlockkey=`+key+'&templateid='+id;
			window.open(url, '_blank');
		} else {
			fpbxToast(data.message, '', 'error');
		}
	});
}
$("#generatetemplatecreator").click(function(e) {
	$.post(window.FreePBX.ajaxurl, {command: "generatetemplatecreator", module: "userman"}, function( data ) {
		fpbxToast(data.message, '', (data.status ? "success" : "error"));
		if(data.status) {
			location.reload();
		}
	});
});
$("#deletetemplatecreator").click(function(e) {
	fpbxConfirm(
		_("Are you sure to delete the Generic User?"),
		_("Yes"), _("No"),
		function() {
			$.post(window.FreePBX.ajaxurl, {command: "deletetemplatecreator", module: "userman"}, function( data ) {
				if(data.status) {
					location.reload();
				}
				fpbxToast(data.message, '', (data.status ? "success" : "error"));
			});
		}
	);
});
$("#merge").click(function(e) {
	$('fieldset#users_allow > span, textarea').each(function(){
		$("#form_usertemplate").append('<input type="hidden" name="users_selected[]" value="'+$(this).data("userid") +'">');
	});
	$("#form_usertemplate").append('<input type="hidden" name="actiontype" id="actiontype" value="merge">');
	$("#form_usertemplate").submit();
});
$("#rebuild").click(function(e) {
	$('fieldset#users_allow > span, textarea').each(function(){
	$("#form_usertemplate").append('<input type="hidden" name="users_selected[]" value="'+$(this).data("userid") +'">');
	});
	$("#form_usertemplate").append('<input type="hidden" name="actiontype" id="actiontype" value="rebuild">');
	$("#form_usertemplate").submit();
});
$("#cancel").click(function(e) {
	e.preventDefault();
	e.stopPropagation();
	window.location = '?display=userman#ucptemplates';
});
$("input[name=createtemp]").change(function() {
	if ($("input[name='createtemp']:checked").val() == 'import') {
		$("#adminextdiv").show()
		$("#tempcreatediv").hide()
			} else {
		$("#adminextdiv").hide()
		$("#tempcreatediv").show()
	}
});
	
//validating edit form 

$("#editM").submit(function (e) {
	e.preventDefault();
	e.stopPropagation();
	var invalid = false;
	if ($("#editM .pwd-error").text().length > 0) {
		invalid = true;
		fpbxToast(_("Password did not match the password polices"), '', 'warning');
		$('#editM').submit(false);
	}
	if(!invalid){
		$('#editM')[0].submit();
	}
});
