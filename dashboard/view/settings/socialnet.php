<?php
if( INPUT::formSubmitted() ) {
	require get_dashboard_path('controller/settings/socialnet.php');
}

$value = fn($key) => $_POST[$key] ?? socialnet($key);
?>
<form class="flexbox w70 px20" method="POST" action="<?= URL::current() ?>">
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="whatssapp">WhatsApp</label></span>
	<input id="whatsapp" class="cn_70" type="url" name="whatsapp" value="<?= $value('whatsapp') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="telegram">Telegram</label></span>
	<input id="telegram" class="cn_70" type="url" name="telegram" value="<?= $value('telegram') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="github">Github</label></span>
	<input id="github" class="cn_70" type="url" name="github" value="<?= $value('github') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="linkedin">Linkedin</label></span>
	<input id="linkedin" class="cn_70" type="url" name="linkedin" value="<?= $value('linkedin') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="youtube">YouTube</label></span>
	<input id="youtube" class="cn_70" type="url" name="youtube" value="<?= $value('youtube') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="twitter">Twitter</label></span>
	<input id="twitter" class="cn_70" type="url" name="twitter" value="<?= $value('twitter') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="pinterest">Pinterest</label></span>
	<input id="pinterest" class="cn_70" type="url" name="pinterest" value="<?= $value('pinterest') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="tiktok">TikTok</label></span>
	<input id="tiktok" class="cn_70" type="url" name="tiktok" value="<?= $value('tiktok') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="instagram">Instagram</label></span>
	<input id="instagram" class="cn_70" type="url" name="instagram" value="<?= $value('instagram') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="facebook">Facebook</label></span>
	<input id="facebook" class="cn_70" type="url" name="facebook" value="<?= $value('facebook') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="behance">Behance</label></span>
	<input id="behance" class="cn_70" type="url" name="behance" value="<?= $value('behance') ?>" />
	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="tumblr">Tumblr</label></span>
	<input id="tumblr" class="cn_70" type="url" name="tumblr" value="<?= $value('tumblr') ?>" />
	<hr class="cn_100" />

	<p class="cn_100" style="padding-left: 30%">
	    <button class="btn xlg mt20" name="action">ATUALIZAR</button>
	</p>
</form>