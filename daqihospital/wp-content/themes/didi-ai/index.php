<?php
/**
 * 博客列表（文章）模板
 */
get_header();
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 40px;">
  <h1 style="font-size:30px;margin-bottom:24px;">博客</h1>
  <div style="width:100%;max-width:860px;">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article style="padding:18px 0;border-bottom:1px solid #f2f3f5;">
        <a href="<?php the_permalink(); ?>" style="font-size:18px;font-weight:600;color:#1f2329;text-decoration:none;"><?php the_title(); ?></a>
        <div style="font-size:12px;color:#86909c;margin:6px 0;">
          <?php the_author(); ?> · <?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(get_the_category_list(', ')); ?>
        </div>
        <p style="font-size:14px;color:#4e5969;"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 40)); ?></p>
      </article>
    <?php endwhile;
      the_posts_pagination();
    else : ?>
      <p>暂无文章</p>
    <?php endif; ?>
  </div>
</main>
<?php
get_footer();
