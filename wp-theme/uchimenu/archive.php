<?php
/**
 * カテゴリ・アーカイブ一覧
 */
get_header();
?>
<div class="wrap">
	<div class="archive-head">
		<span class="kick num">CATEGORY</span>
		<h1><?php echo esc_html( single_term_title( '', false ) ?: get_the_archive_title() ); ?></h1>
		<?php if ( term_description() ) : ?><p><?php echo wp_kses_post( term_description() ); ?></p><?php endif; ?>
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
			echo '<p style="color:var(--sub)">このカテゴリの記事はまだありません。</p>';
		endif;
		?>
	</div>
	<div class="pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?></div>
</div>
<?php get_footer(); ?>
