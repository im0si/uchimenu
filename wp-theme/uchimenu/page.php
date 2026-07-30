<?php
/**
 * 固定ページ
 */
get_header();

while ( have_posts() ) : the_post();
?>
<article class="article-wrap">
	<header class="article-head">
		<h1><?php the_title(); ?></h1>
	</header>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
</article>
<?php
endwhile;
get_footer();
?>
