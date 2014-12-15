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


$("#delete").on("click", function(event) {
	if (!confirm("Are you sure you want to delete this user?")) {
		event.preventDefault();
	}
});

//Some glitch in chrome makes the window go to the middle of the screen
window.scrollTo(0, 0);
//from http://stackoverflow.com/a/26744533 loads url params to an array
var params={};window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(str,key,value){params[key] = value;});
//Tab and Button stuff
$( document ).ready(function() {
	var hash = window.location.hash;
	if(hash == '#settings'){
		$('input[name="submit"]').removeClass('hidden');
		$('input[name="reset"]').removeClass('hidden');	
	}
	
	if(params['action'] == 'adduser' || params['action'] == 'showuser' ){
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
$('#editusermodal').on('show.bs.modal', function (e) {
	var uid = $(e.relatedTarget).data('uid');
	$.ajax({
		url: "/admin/ajax.php",
		data: {
			module:'userman',
			command:'getuserfields',
			id:uid
		},
		type: "GET",
		dataType: "json",
		success: function(data){
			$.each(data, function(key, value){
				$('[name='+key+']').val(value);
			});
		},
		error: function(xhr, status, e){
			console.dir(xhr);
			console.log(status);
			console.log(e);
		}
	});
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
	var cmessage = _("Are you sure you want to delete these users?"); 
	if(!confirm(cmessage)){
		return false;
	}
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
