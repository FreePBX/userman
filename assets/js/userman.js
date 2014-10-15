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
