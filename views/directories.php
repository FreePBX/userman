<?php

function genConfFormHTML($form_data = []) {
	$return_data = "";
	$element_container = '
	<div class="element-container $$__ELEMENT_CONTAINER_CLASS__$$">
		<div class="row">
			<div class="col-md-12">
				<div class="">
					<div class="form-group row">
						<div class="col-md-3">
							<label class="control-label" for="$$__NAME__$$">$$__TITLE__$$</label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="$$__NAME__$$"></i>
						</div>
						<div class="col-md-9">
							$$__TYPE_INPUT__$$
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="$$__NAME__$$-help" class="help-block fpbx-help-block">$$__HELP__$$</span>
			</div>
		</div>
	</div>
	';

	$type_input = ['text' 	 		=> '<input type="text" id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>', 'list' 	 		=> '<select id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>$$__LINES__$$</select>', 'list_multiple'	=> '<select id="$$__NAME__$$" class="form-control chosenmultiselect" name="$$__NAME__$$[]" multiple="multiple" $$__OPTIONS__$$>$$__LINES__$$</select>', 'list_line'		=> '<option value="$$__VALUE__$$" $$__SELECTED__$$>$$__TEXT__$$</option>', 'number'		=> '<input type="number" id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>', 'password'		=> '<input type="password" id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>', 'textarea' 		=> '<textarea id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>$$__VALUE__$$</textarea>', 'yn' 			=> '
			<span class="radioset">
				<input type="radio" id="$$__NAME__$$_on" name="$$__NAME__$$" value="$$__VALUE_Y__$$" $$__CHECKED_Y__$$>
				<label for="$$__NAME__$$_on">'._('Yes').'</label>
				<input type="radio" id="$$__NAME__$$_off" name="$$__NAME__$$" value="$$__VALUE_N__$$" $$__CHECKED_N__$$>
				<label for="$$__NAME__$$_off">'._('No').'</label>
			</span>
		'];

	foreach ($form_data as $index => $element)
	{
		if (empty($element['type'])) { continue; }

		$new_input = "";
		switch($element['type'])
		{
			case 'hidden':
				$new_input = sprintf('<input type="hidden" name="%s" value="%s">', $element['name'], $element['value']);
				break;

			case 'fieldset_init':
				$new_input = '<fieldset>';
				if (isset($element['legend']) && ! empty($element['legend'])) {
					$new_input .= sprintf('<legend>%s</legend>', $element['legend']);
				}
				break;

			case 'fieldset_end':
				$new_input = '</fieldset>';
				break;
				
			case 'list':
			case 'list_multiple':
				$tmp_ls_options = "";
				foreach ($element['list'] as $line_option)
				{
					$tmp_option = $type_input['list_line'];
					$tmp_option = str_replace('$$__VALUE__$$', $line_option[$element['keys']['value']], $tmp_option);
					$tmp_option = str_replace('$$__TEXT__$$', $line_option[$element['keys']['text']], $tmp_option);
					if (is_array($element['value']))
					{
						$tmp_option = str_replace('$$__SELECTED__$$', (in_array($line_option[$element['keys']['value']], $element['value'])  ? 'selected' : ''), $tmp_option);
					}
					else
					{
						$tmp_option = str_replace('$$__SELECTED__$$', ($element['value'] == $line_option[$element['keys']['value']] ? 'selected' : ''), $tmp_option);
					}
					$tmp_ls_options .= $tmp_option;
					unset($tmp_option);
				}

			case 'text':
			case 'number':
			case 'password':
			case 'textarea':
				$new_input = $element_container;
				if (isset($type_input[$element['type']]))
				{
					$new_input = str_replace('$$__TYPE_INPUT__$$', $type_input[$element['type']], $new_input);
				}

				if (! empty($tmp_ls_options)) {
					$new_input = str_replace('$$__LINES__$$', $tmp_ls_options, $new_input);
				}

				if (isset($element['index']) && $element['index']) {
					$new_input = str_replace('$$__OPTIONS__$$', sprintf('tabindex="%s" $$__OPTIONS__$$', $index), $new_input);
				}

				if (isset($element['opts'])) {
					foreach ($element['opts'] as $key => $val)
					{
						$new_input = str_replace('$$__OPTIONS__$$', sprintf('%s="%s" $$__OPTIONS__$$', $key, $val), $new_input);
					}
				}

				if (! empty($element['default'])) {
					if ($element['type'] != 'list') {
						$new_input = str_replace('$$__OPTIONS__$$', 'placeholder="$$__DEFAULT__$$" $$__OPTIONS__$$', $new_input);
					}
					$new_input = str_replace('$$__OPTIONS__$$', 'data-default="$$__DEFAULT__$$" $$__OPTIONS__$$', $new_input);
				}

				if (isset($element['required']) && $element['required']) {
					$new_input = str_replace('$$__OPTIONS__$$', 'required $$__OPTIONS__$$', $new_input);
				}
				break;

			case 'radioset_yn':
				$new_input = $element_container;
				$tmp_radioset = $type_input['yn'];

				$tmp_values_y = $element['values']['y'] ?? 'Y';
				$tmp_values_n = $element['values']['n'] ?? 'N';

				$tmp_radioset = str_replace('$$__CHECKED_Y__$$', $element['value'] == $tmp_values_y ? 'checked' : '', $tmp_radioset);
				$tmp_radioset = str_replace('$$__CHECKED_N__$$', $element['value'] == $tmp_values_n ? 'checked' : '', $tmp_radioset);
				
				$tmp_radioset = str_replace('$$__VALUE_Y__$$', $tmp_values_y, $tmp_radioset);
				$tmp_radioset = str_replace('$$__VALUE_N__$$', $tmp_values_n, $tmp_radioset);

				$new_input = str_replace('$$__TYPE_INPUT__$$', $tmp_radioset, $new_input);
				unset($tmp_radioset);
				break;

			case 'raw':
				$new_input = $element_container;
				$new_input = str_replace('$$__TYPE_INPUT__$$', $element['value'], $new_input);
				break;
			
			default:
				continue 2;
		}

		// NAME
		if (isset($element['name'])) {
			$new_input = str_replace('$$__NAME__$$', $element['name'] ?? '', $new_input ?? '');
		}

		// TTITLE
		if (isset($element['title'])) {
			$new_input = str_replace('$$__TITLE__$$', $element['title'] ?? '', $new_input) ?? '';
		}
		
		// DEFAULT
		if (! empty($element['default'])) {
			$new_input = str_replace('$$__DEFAULT__$$', $element['default'] ?? '', $new_input ?? '');
		}

		// VALUE
		if (isset($element['value'])) {
			$new_input = str_replace('$$__VALUE__$$', (is_array($element['value'])? '': ($element['value'] ?? '')), $new_input);
		}

		// HELP
		if (isset($element['help'])) {
			if (is_array($element['help']))
			{
				$help_text = "";
				foreach ($element['help'] as $line)
				{
					switch($line['type'])
					{
						case "text":
							$help_text .= $line['value'];
							break;

						case "list":
							foreach ($line['list'] as $list_line)
							{
								$help_text_line = $line['tamplate'];
								foreach ($list_line as $l_key => $l_val)
								{
									if (in_array($l_key, $line['keys'])) {
										$help_text_line = str_replace( sprintf('$$__%s__$$', strtoupper((string) $l_key)), $l_val, (string) $help_text_line);
									}
								}
								$help_text .= $help_text_line;
								unset($help_text_line);
							}
							break;
					}
				}
				$new_input = str_replace('$$__HELP__$$', $help_text, $new_input);
				unset($help_text);
			}
			else { $new_input = str_replace('$$__HELP__$$', $element['help'], $new_input); }
		}

		// CLASS
		if (isset($element['class'])) {
			$new_input = str_replace('$$__ELEMENT_CONTAINER_CLASS__$$', $element['class'], $new_input);
		}
		
		// Clean Up
		foreach (['TYPE_INPUT', 'OPTIONS', 'VALUE', 'HELP', 'LINES', 'ELEMENT_CONTAINER_CLASS', 'DEFAULT'] as $item)
		{
			$new_input = str_replace( sprintf('$$__%s__$$', $item), "", $new_input);
		}

		$return_data .= $new_input;
	}

	return $return_data;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showdirectory'){
	$heading = '<h1>' . _("Edit Directory") . '</h1>';
}else{
	$heading = '<h1>' . _("Add Directory") . '</h1>';
}
$formaction = 'config.php?display=userman#directories';

echo $heading;
if(!isset($brand)) $brand = '';
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<div class="display full-border">
					<form autocomplete="on" class="fpbx-submit" id="directory" name="directory" action="<?php echo $formaction; ?>" method="post" onsubmit="return">
						<input type="hidden" name="type" value="directory">
						<input type="hidden" name="submittype" value="gui">
						<input type="hidden" name="id" value="<?php echo !empty($config['id']) ? $config['id'] : ''?>">
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="">
										<div class="form-group row">
											<div class="col-md-3">
												<label class="control-label" for="authtype"><?php echo _("Directory Type")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="authtype"></i>
											</div>
											<div class="col-md-9">
												<?php if(empty($config['driver'])) { ?>
													<select id="authtype" name="authtype" class="form-control">
														<?php foreach($auths as $rawname => $auth) {?>
															<option value="<?php echo $rawname?>"><?php echo $auth['name']?></option>
														<?php } ?>
													</select>
												<?php } else {?>
													<input type="hidden" id="authtype" name="authtype" value="<?php echo $config['driver']?>">
													<?php echo $auths[$config['driver']]['name']?>
												<?php }?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="authtype-help" class="help-block fpbx-help-block"><?php echo sprintf(_("The authentication engine to use"),$brand)?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="">
										<div class="form-group row">
											<div class="col-md-3">
												<label class="control-label" for="name"><?php echo _("Directory Name")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="name"></i>
											</div>
											<div class="col-md-9">
												<input class="form-control" id="name" name="name" value="<?php echo !empty($config['name']) ? $config['name'] : ''?>">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="name-help" class="help-block fpbx-help-block"><?php echo _("The directory name")?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="">
										<div class="form-group row">
											<div class="col-md-3">
												<label class="control-label" for="enable"><?php echo _("Enable Directory")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="enable"></i>
											</div>
											<div class="col-md-9 radioset">
												<input type="radio" id="enable1" name="enable" value="1" <?php echo $config['active'] ? 'checked' : ''?>>
												<label for="enable1"><?php echo _("Yes")?></label>
												<input type="radio" id="enable2" name="enable" value="0" <?php echo !$config['active'] ? 'checked' : ''?>>
												<label for="enable2"><?php echo _("No")?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="enable-help" class="help-block fpbx-help-block"><?php echo sprintf(_("May this user log in to the %s Administration Pages?"),$brand)?></span>
								</div>
							</div>
						</div>
						<div class="element-container" id="sync-container">
							<div class="row">
								<div class="col-md-12">
									<div class="">
										<div class="form-group row">
											<div class="col-md-3">
												<label class="control-label" for="sync"><?php echo _("Synchronize")?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="sync"></i>
											</div>
											<div class="col-md-9">
												<select name="sync" id="sync" class="form-control">
													<option value=""><?php echo _("Never")?></option>
													<option value="*/30 * * * *" <?php echo isset($config['config']['sync']) && $config['config']['sync'] == '*/30 * * * *' ? 'selected' : ''?>><?php echo _("30 Minutes")?></option>
													<option value="0 * * * *" <?php echo !isset($config['config']['sync']) || (isset($config['config']['sync']) && $config['config']['sync'] == '0 * * * *') ? 'selected' : ''?>><?php echo _("1 Hour")?></option>
													<option value="0 */6 * * *" <?php echo isset($config['config']['sync']) && $config['config']['sync'] == '0 */6 * * *' ? 'selected' : ''?>><?php echo _("6 Hours")?></option>
													<option value="0 0 * * *" <?php echo isset($config['config']['sync']) && $config['config']['sync'] == '0 0 * * *' ? 'selected' : ''?>><?php echo _("1 Day")?></option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="sync-help" class="help-block fpbx-help-block"><?php echo sprintf(_("This setting only applies to authentication engines other than the %s Internal Directory. For the %s Internal Directory this setting will be ignored."),$brand,$brand)?></span>
								</div>
							</div>
						</div>
						<fieldset>
							<legend><?php echo _('Directory Settings')?></legend>
							<?php 
							foreach($auths as $rawname => $auth)
							{
								echo sprintf('<div id="%s-auth-settings" class="auth-settings d-none">%s</div>', $rawname, genConfFormHTML($auth['config']['data']));
							}
							?>
						<fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	var val = $("#authtype").val();
	$("#" + val + "-auth-settings").removeClass("d-none");
	if(val == "Freepbx") {
		$("#sync-container").addClass("d-none");
	} else {
		$("#sync-container").removeClass("d-none");
	}

	$("#authtype").change(function() {
		var val = $(this).val();
		if(val == "Freepbx") {
			$("#sync-container").addClass("d-none");
		} else {
			$("#sync-container").removeClass("d-none");
		}
		$(".auth-settings").addClass("d-none");
		$("#" + val + "-auth-settings").removeClass("d-none");
		$(".fpbx-submit input[type=text]:hidden").prop("disabled",true);
		$(".fpbx-submit input:visible").prop("disabled",false);
	});
	$(".fpbx-submit input[type=text]:hidden").prop("disabled",true);
	$(".fpbx-submit input:visible").prop("disabled",false);
</script>
