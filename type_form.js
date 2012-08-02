$(function() {
	$('#attributeValuesWrap').sortable();
	$('.valueedit').live('click', function() {
		var $textField = $(this).parent().next().children('input[type=text]');
		var $display = $(this).parent().next().children('.akSelectValueDisplay');
		if($(this).hasClass('editmode')) {
			$textField.hide();
			$display.show();
			$display.text($textField.val());
			$(this).val('Edit').removeClass('editmode');
		} else {
			$textField.show();
			$display.hide();
			$(this).val('Save').addClass('editmode');
		}
	});
	$('.valuedelete').live('click', function() {
		$(this).closest('.akSelectValueWrap').remove();
	});	
	var index = 1;
	$('#addAttributeValueButton').click(function() {
		
		if($('#akSelectValueFieldNew').val() == '') {
			return false;
		}
		$('#attributeValuesWrap')
			.append(
				'<div class="akSelectValueWrap">' +
					'<div class="rightCol">' +
						'<input type="button" class="valueedit btn" value="Edit" />' +
						'<input type="button" class="valuedelete btn" value="Delete" />' +
					'</div><!-- /rightCol -->' +
					'<span class="leftCol">' +
						'<span class="akSelectValueDisplay">' +
							$('#akSelectValueFieldNew').val() +
						'</span>' +
						'<input type="hidden" class="akSelectValueID" name="akSelectValue[new_' + index + '][ID]" value="" />' +
						'<input class="akSelectValueTextField" type="text" name="akSelectValue[new_' + index + '][value]" value="' + $('#akSelectValueFieldNew').val() + '" />' +
					'</span><!-- /leftCol -->' +
					'<div class="ccm-spacer">&nbsp;</div>' +
				'</div><!-- /akSelectValueWrap -->'
			);
		$('#akSelectValueFieldNew').val('');
		index++;
	});
});
