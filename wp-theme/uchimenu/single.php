<?php
/**
 * 記事ページ
 */
get_header();

while ( have_posts() ) : the_post();
	$cats = get_the_category();
	$cat  = $cats ? $cats[0] : null;
?>
<article class="article-wrap">
	<header class="article-head">
		<?php if ( $cat ) : ?>
			<a class="tag <?php echo esc_attr( uchimenu_cat_class( $cat->name ) ); ?>"
				href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
		<?php endif; ?>
		<h1><?php the_title(); ?></h1>
		<div class="article-meta">
			<span><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?> 公開</span>
			<?php if ( get_the_modified_date( 'Ymd' ) !== get_the_date( 'Ymd' ) ) : ?>
				<span><?php echo esc_html( get_the_modified_date( 'Y.m.d' ) ); ?> 更新</span>
			<?php endif; ?>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="article-eyecatch"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>
	</header>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
</article>

<div class="wrap">
	<div class="sec-head"><h2>あわせて読みたい</h2><span class="en">RELATED</span></div>
	<div class="grid" style="margin-bottom:50px">
		<?php
		$rel = new WP_Query( array(
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'category__in'        => $cat ? array( $cat->term_id ) : array(),
			'ignore_sticky_posts' => true,
		) );
		$i = 0;
		if ( $rel->have_posts() ) :
			while ( $rel->have_posts() ) : $rel->the_post();
				uchimenu_card( $i );
				$i++;
			endwhile;
			wp_reset_postdata();
		endif;
		?>
	</div>
</div>
<?php
endwhile;
get_footer();
?>
