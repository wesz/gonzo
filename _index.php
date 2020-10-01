<!DOCTYPE html>
<html lang="en" class="vw-fh">
	<head>
		<meta charset="utf-8" />
		<title>Gonzo setup</title>
		<style>
			label { display: block; width: 100%; }
		</style>
	</head>
	<body>
		<h1>Gonzo setup</h1>
		<!--<form method="post">
			
			<fieldset>
				<legend><strong>First time setup</strong></legend>
				<?php
				require_once('gonzo.php');

				if ( ! file_exists(__DIR__.'/.gonzo'))
				{
					$f = @fopen(__DIR__.'/.gonzo', 'a');

					if ($f === false)
					{
						die('Insufficient permissions for "'.__DIR__.'" path. Gonzo is unable to write required files.');
					} else
					{
						fclose($f);

						$dirs = array('view', 'i18n', 'plugin', 'media', 'upload', 'cache');

						foreach ($dirs as $dir)
						{
							$dir = path_dir(self::var('gonzo.'.$dirs.'_dir'));

							if ( ! empty($dir))
							{
								dir_ensure(GONZO_PATH.$dir);
							}
						}

						file_ensure('.htaccess', "Options +FollowSymLinks\nOptions All -Indexes\n\nRewriteEngine On\nRewriteBase /\nRewriteRule ^(?:host)\b - [F,L]\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule (.*)$ ".$_SERVER['PHP_SELF']."/$1 [L,QSA]\n");

						self::export('var-gonzo', self::var('gonzo'));
					}
				}
				
				$gonzo = array_merge(array
				(
					'gonzo' => array
					(
						'name' => 'Gonzo',
						'lang' => 'en',
						'charset' => 'utf-8',
						'timezone' => date_default_timezone_get(),
						'salt' => '',
						'file' => basename($_SERVER['SCRIPT_NAME']),
						'url' => $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']),
						'secure' => ($_SERVER['REQUEST_SCHEME'] == 'https' ? 'on' : 'off')
					)
				), $_POST);

				print_r($gonzo);
				print_r($_POST);
				?>
				<label><span>Name</span><br /><input type="text" name="gonzo[name]" value="<?php echo $gonzo['gonzo']['name']; ?>" /></label>
				<label><span>Language</span><br /><input type="text" name="lang" value="<?php echo $gonzo['lang']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['charset']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['timezone']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['salt']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['file']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['url']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['secure']; ?>" /></label>
				<label><span>Domain</span><br /><input type="text" value="<?php echo $gonzo['name']; ?>" /></label>
				<label><span>.htaccess</span><br /><textarea rows="20" cols="80">Options +FollowSymLinks
Options All -Indexes

RewriteEngine On
RewriteBase /
RewriteRule ^(?:host)\b - [F,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*)$ /gonzo/index.php/$1 [L,QSA]</textarea></label>
				<button type="submit">Setup</button>
			</fieldset>
		</form>-->
	</body>
</html>