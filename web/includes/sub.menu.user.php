<div id="userSubMenu">
	<div class="contentOutline">
		<ul class="inlineUL">
			<li>
				<a href="<?=\App::getInstance()->findRoute("EntryPresentation", "getList")?>">Seus gastos</a>
			</li>
			<li>
				<a href="<?=\App::getInstance()->findRoute("UserBankPresentation", "getList")?>">Listar suas contas/poupança/investimentos</a>
			</li>
			<li>
				<a href="<?=\App::getInstance()->findRoute("EntryCategoryPresentation", "getList")?>">Listar categorias de gastos</a>
			</li>
		</ul>
	</div>
</div>