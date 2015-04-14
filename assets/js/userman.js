//make devlist heights the same
function dev_list_height() {
	var height = 0;
	$('.device_list').height('auto').each(function(){
		height = $(this).height() > height ? $(this).height() : height;
	}).height(height);
}

function dev_list_item_width() {
	var width = 0;
	$('.device_list > span').each(function(){
		width = $(this).width() > width ? $(this).width() : width;
	});

	$('.device_list > span').width(width);
}
function dev_list_sort() {
	$('.device_list').each(function(){
		var dev_list = $(this);
		var list = dev_list.find('span').sort(function(a, b){
			return $(a).data('ext') > $(b).data('ext') ? 1 : -1;
		})
		$.each(list, function(id, item){
			console.log(item);
			dev_list.append(item);
		})
	});
}

/**
 * limit the amount of devices a page can contain, based on the advanced settings
 * key PAGINGMAXPARTICIPANTS
 * @param string id - id of target
 *
 * @returns bool
 */
function dev_limit(id) {
	if (id == 'notselected_dev') {
		return true;
	}

	//if key isnt set, just return true
	if (typeof fpbx.conf.PAGINGMAXPARTICIPANTS == 'undefined') {
		return true;
	}
	return $('#selected_dev > span').length < fpbx.conf.PAGINGMAXPARTICIPANTS;
}

$("#defaultextension").change(function() {
	if ($(this).val() == "none") {
		return false;
	}
	var ext = $(this).val();
	if (!$(".extension-checkbox[data-extension=\"" + ext + "\"]").is(":checked")) {
		$(".extension-checkbox[data-extension=\"" + ext + "\"]").prop("checked", true).trigger("change");
	}
});

$(".extension-checkbox").change(function() {
	var ext = $("#defaultextension").val();
	if (ext == "none") {
		return false;
	}

	if (($(this).val() == ext) && !$(this).is(":checked")) {
		alert("You Can Not Unselect the Linked Extension");
		$(".extension-checkbox[data-extension=\"" + ext + "\"]").prop("checked", true).trigger("change");
	}
});


//Some glitch in chrome makes the window go to the middle of the screen
window.scrollTo(0, 0);
//from http://stackoverflow.com/a/26744533 loads url params to an array
var params={};window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(str,key,value){params[key] = value;});
//Tab and Button stuff
$( document ).ready(function() {
	$('form[name=page_edit]').submit(function(){
		if (!isInterger($('input[name=pagenbr]').val())) {
			alert('Please enter a valid Paging Extension');
			return false;
		}
	});


	//style devices as buttons
	$('.device_list > span').button();

	//make devices dragable
	$('.device_list').sortable({
		connectWith: '.device_list',
		items: ' > span',
		deactivate: function(){dev_list_height();},
		receive: function(i, ui) {
			//if dev_limit returns false, cancel the move
			dev_limit($(ui.item).parent().attr('id'))
				|| $(ui.sender).sortable('cancel');
		}
	}).disableSelection();

	//set device width so there all the same size
	dev_list_item_width();

	//resize device lists, now that there 'sortabled' and 'buttoned'
	dev_list_height();

	//allow devices to move between lists by double clicking on them
	$('.device_list > span').dblclick(function(e){
		var to = $(this).parent().attr('id') == 'selected_dev'
					? 'notselected_dev'
					: 'selected_dev';

		//dont transfer devices if at limit
		if (!dev_limit(to)) {
			return false;
		}
		$(this).appendTo($('#' + to));
		dev_list_height();
	});

	//add devices to form on submit
	$('#page_opts_form').submit(function(){
		var form = $(this);

		$('#selected_dev > span').each(function(){
			form.append('<input type="hidden" name="pagelist[]" value="'
				+ $(this).attr('data-ext') + '">');
		});

	});

	var hash = (window.location.hash != "") ? window.location.hash : "users";
	if(hash == '#settings'){
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="submitsend"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
	}

	if(params['action'] == 'adduser' || params['action'] == 'showuser'){
		$('input[name="submitsend"]').removeClass('hidden');
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$('input[name="delete"]').removeClass('hidden');
	}else if(params['action'] == 'addgroup' || params['action'] == 'showgroup') {
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');
		$('input[name="delete"]').removeClass('hidden');
	}

	$(".nav-tabs a[href="+hash+"]").tab('show');
	//we should be at the user tab by default so we will show add user.
});
//this fires when you change tabs
$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
	//Button Related
	switch(e.target.text){
		case "Settings":
			$('input[name="submit"]').removeClass('hidden');
			$('input[name="reset"]').removeClass('hidden');
			break;
		case "Users":
			$('input[name="submit"]').addClass('hidden');
			$('input[name="reset"]').addClass('hidden');
			break;
		default:
			return
	}
	//Add hash to url for reloading
	window.location.hash = e.target.hash.replace()
});


$('#action-toggle-all').on("change",function(){
	var tval = $(this).prop('checked');
	$('input[id^="actonthis"]').each(function(){
		$(this).prop('checked', tval);
	});
});

$('input[id^="actonthis"],#action-toggle-all').change(function(){
	if($('input[id^="actonthis"]').is(":checked")){
		$("#delchecked").removeClass("hidden");
	}else{
		$("#delchecked").addClass("hidden");
	}

});
//This does the bulk delete...
$("#delchecked").on("click",function(){
	$('input[id^="actonthis"]').each(function(){
		if($(this).is(":checked")){
			var rowid = $(this).val();
			var row = $('#row'+ rowid);
			$.ajax({
				url: "/admin/ajax.php",
				data: {
					module:'userman',
					command:'delUser',
					id:rowid
				},
				type: "GET",
				dataType: "json",
				success: function(data){
					if(data.status === true){
						row.fadeOut(2000,function(){
							$(this).remove();
						});
					}else{
						warnInvalid(row,data.message);
					}
				},
				error: function(xhr, status, e){
					console.dir(xhr);
					console.log(status);
					console.log(e);
				}
			});
		}
	});
	//Reset ui elements
	//hide the action element in botnav
	$("#delchecked").addClass("hidden");
	//no boxes should be checked but if they are uncheck em.
	$('input[id^="actonthis"]').each(function(){
		$(this).prop('checked', false);
	});
	//Uncheck the "check all" box
	$('#action-toggle-all').prop('checked', false);
});

//Trashcan Action
$('a[id^="del"]').on("click",function(){
	var cmessage = _("Are you sure you want to delete this user?");
	if(!confirm(cmessage)){
		return false;
	}
	var uid = $(this).data('uid');
	var row = $('#row'+uid);
	$.ajax({
		url: "/admin/ajax.php",
		data: {
			module:'userman',
			command:'delUser',
			id:uid
		},
		type: "GET",
		dataType: "json",
		success: function(data){
			if(data.status === true){
				row.fadeOut(2000,function(){
					$(this).remove();
				});
			}else{
				warnInvalid(row,data.message);
			}
		},
		error: function(xhr, status, e){
			console.dir(xhr);
			console.log(status);
			console.log(e);
		}
	});
});

//Making Password Modal work
$('a[id^="pwmlink"]').on("click", function(){
	var pwuid = $(this).data('pwuid');
	$("#pwuid").val(pwuid);
	$("#pwsub").attr("disabled", false);
	$("#pwsub").html(_("Update Password"));
});
$("#pwsub").on("click", function(){
	var button = $(this);
	button.html('Updating');
	button.attr("disabled", true);
	var uid = $("#pwuid").val();
	var pass = $("#password").val();
	$.ajax({
		url: "/admin/ajax.php",
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

$( "form" ).submit(function() {
	if(!this.checkValidity()){
		for(i = 0; i < this.elements.length; i++){
			if(!this.elements[i].validity.valid){
				warnInvalid($(this.elements[i]));
			}
		}
		return false;
	};
});
