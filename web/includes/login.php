<div id="login">
	<? if (isset($c['LOGIN_FORM'])) { ?>
		<form <?=$c['LOGIN_FORM'][presentation\BasePresentation::FORM_PARAMS]?>>
		
			<?php if ($c['LOGIN_FORM'][presentation\BasePresentation::FORM_ELEMENTS]) { ?>
				<table class="inputForm">
					<tr>
						<?php foreach ($c['LOGIN_FORM'][presentation\BasePresentation::FORM_ELEMENTS] as $element) { ?>
							<td>
								<?=$element[presentation\BasePresentation::FORM_LABEL]?>
								<?=$element[presentation\BasePresentation::FORM_INPUT]?>
							</td>
						<?php } ?>
						<td>	
							<?=$c['LOGIN_FORM'][presentation\BasePresentation::FORM_SUBMIT];?>
						</td>
					</tr>					
				</table>
			<?php } ?>
	
		</form>
	<? } ?>
</div>