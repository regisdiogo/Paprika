<?php if (isset($c[presentation\BasePresentation::OUTPUT_MESSAGE])) { ?>
	<div class="app-message">
		<?=$c[presentation\BasePresentation::OUTPUT_MESSAGE]?>
	</div>
<?php } ?>

<?php if (isset($c[presentation\BasePresentation::EXCEPTION_VALIDATION])) { ?>
	<div class="app-message">
		<ul>
			<?php foreach ($c[presentation\BasePresentation::EXCEPTION_VALIDATION] as $element) { ?>
				<li><?=$element?></li>
			<?php } ?>
		</ul>
	</div>
<?php } ?>
