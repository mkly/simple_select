<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>
<fieldset>
	<legend><?php echo t('Select Options') ?></legend>

	<div class="clearfix">
		<?php echo $form->label('simple-select-values', t('Values')) ?>
		<div class="input">
			<div id="attributeValuesInterface">
				<div id="attributeValuesWrap">
					<?php foreach($options as $option): ?>
						<div class="akSelectValueWrap">
							<div class="rightCol">
								<input type="button" class="valueedit btn" value="<?php echo t('Edit') ?>" />
								<input type="button" class="valuedelete btn" value="<?php echo t('Delete') ?>" />	
							</div><!-- rightCol -->
							<span class="leftCol">
								<span class="akSelectValueDisplay"><?php echo $option->getValue() ?></span>
								<input type="hidden" class="akSelectValueID" name="akSelectValue[old_<?php echo $option->getID() ?>][ID]" value="<?php echo $option->getID() ?>" />
								<input type="text" class="akSelectValueTextField" name="akSelectValue[old_<?php echo $option->getID() ?>][value]" value="<?php echo $option->getValue() ?>" />
							</span><!-- /leftCol -->
							<div class="ccm-spacer">&nbsp;</div>
						</div><!-- /akSelectValueWrap -->
					<?php endforeach; ?>
				</div><!-- attributeValuesWrap -->
				<div id="addAttributeValuesWrap">
					<?php echo $form->text('akSelectValueFieldNew') ?>
					<input id="addAttributeValueButton" type="button" class="btn" value="<?php echo t('Add') ?>" />
				</div><!-- /addAttributeValuesWrap -->
			</div><!-- /attributeValuesInterface -->
		</div><!-- /input -->
	</div>
</fieldset>
