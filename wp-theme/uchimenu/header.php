<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-head">
	<div class="wrap head-in">
		<span class="crest">献</span>
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">うちめにゅー<span class="mag">Magazine</span></a>
		<span class="site-tagline"><?php bloginfo( 'description' ); ?></span>
		<a class="head-cta" href="<?php echo uchimenu_app_url(); ?>">🎲 アプリを開く</a>
	</div>
</header>
