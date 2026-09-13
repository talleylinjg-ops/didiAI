<?php
/**
 * 单篇文章模板
 */
get_header();
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 40px;">
  <div style="width:100%;max-width:860px;">
    <?php while (have_posts()) : the_post(); ?>
      <h1 style="font-size:28px;"><?php the_title(); ?></h1>
      <div style="font-size:12px;color:#86909c;margin:12px 0 24px;">
        <?php the_author(); ?> · <?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(get_the_category_list(', ')); ?>
      </div>
      <div class="entry-content" style="font-size:15px;line-height:1.8;color:#1f2329;">
        <?php the_content(); ?>
      </div>
      <div style="margin-top:30px;">
        <?php
          if (comments_open() || get_comments_number()) {
            comments_template();
          }
        ?>
      </div>
    <?php endwhile; ?>
  </div>
</main>
<?php
get_footer();
