<ul id="header-menu" class="inline-ul">
	<?php if (!\core\SessionManager::getInstance()->getUserEntity()) { ?>
		<li>
			<div id="header-menu-sign-up">
				<a href="<?=\App::getInstance()->findRoute("UserPresentation", "create")?>"></a>
			</div>
		</li>
		<li style="border-left: 1px solid #B591B7;">
			<div id="header-menu-sign-in">
				<a href="<?=\App::getInstance()->findRoute("UserPresentation", "login")?>"></a>
			</div>
		</li>
	<?php } else { ?>
		<li>
			<a href="<?=\App::getInstance()->findRoute("UserPresentation", "myPage")?>">Minha página</a>
		</li>
		<li style="border-left: 1px solid #B591B7;">
			<a href="<?=\App::getInstance()->findRoute("UserPresentation", "logout")?>">Sair</a>
		</li>
	<?php } ?>
</ul>