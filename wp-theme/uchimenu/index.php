<?php
/**
 * 記事一覧（ブログ・フォールバック）
 */
get_header();
?>
<div class="wrap">
	<div class="archive-head">
		<span class="kick num">ARTICLES</span>
		<h1>記事一覧</h1>
	</div>
	<div class="grid" style="margin-top:20px">
		<?php
		$i = 0;
		if ( have_posts() ) :
			while ( have_posts() ) : the_post();
				uchimenu_card( $i );
				$i++;
			endwhile;
		else :
			echo '<p style="color:var(--sub)">記事が見つかりませんでした。</p>';
		endif;
		?>
	</div>
	<div class="pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?></div>
</div>
<?php get_footer(); ?>
