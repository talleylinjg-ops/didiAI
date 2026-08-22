<?php
/**
 * 独立页面模板
 */
get_header();
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 40px;">
  <div style="width:100%;max-width:860px;">
    <?php while (have_posts()) : the_post(); ?>
      <h1 style="font-size:28px;"><?php the_title(); ?></h1>
      <div class="entry-content" style="font-size:15px;line-height:1.8;color:#1f2329;margin-top:18px;">
        <?php the_content(); ?>
      </div>
    <?php endwhile; ?>
  </div>
</main>
<?php
get_footer();
