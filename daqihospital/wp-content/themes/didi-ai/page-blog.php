<?php
/**
 * 博客页面（文章列表 + 左侧分类目录）
 * Template Name: 博客
 */
get_header();
$paged = max(1, get_query_var('paged'));
$cat_slug = isset($_GET['cat']) ? sanitize_title($_GET['cat']) : '';
$tag_slug = isset($_GET['tag']) ? sanitize_title($_GET['tag']) : '';
$q = new WP_Query(array(
  'post_type' => 'post',
  'posts_per_page' => 8,
  'paged' => $paged,
  's' => isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '',
  'category_name' => $cat_slug ? $cat_slug : '',
  'tag' => $tag_slug ? $tag_slug : '',
));
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 40px;">
  <h1 style="font-size:30px;margin-bottom:24px;">博客</h1>
  <div style="display:grid;grid-template-columns:220px 1fr;gap:24px;width:100%;max-width:1080px;align-items:start;">
    <aside style="background:#fff;border:1px solid #e5e6eb;border-radius:12px;padding:16px;position:sticky;top:90px;max-height:calc(100vh - 140px);overflow-y:auto;">
      <?php echo didi_ai_sidebar_categories(home_url('/blog'), $cat_slug, '分类目录', false); ?>
    </aside>
    <div>
      <form method="get" style="display:flex;gap:10px;margin-bottom:20px;">
        <input class="search-input" type="text" name="q" placeholder="搜索文章..." value="<?php echo esc_attr(isset($_GET['q']) ? $_GET['q'] : ''); ?>">
        <?php if ($cat_slug) : ?><input type="hidden" name="cat" value="<?php echo esc_attr($cat_slug); ?>"><?php endif; ?>
        <button class="btn-primary" type="submit">搜索</button>
      </form>
      <?php if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post(); ?>
        <article style="padding:18px 0;border-bottom:1px solid #f2f3f5;">
          <a href="<?php the_permalink(); ?>" style="font-size:18px;font-weight:600;color:#1f2329;text-decoration:none;"><?php the_title(); ?></a>
          <div style="font-size:12px;color:#86909c;margin:6px 0;">
            <?php the_author(); ?> · <?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(get_the_category_list(', ')); ?>
          </div>
          <p style="font-size:14px;color:#4e5969;"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 40)); ?></p>
        </article>
      <?php endwhile;
        $q->the_posts_pagination();
      else : ?>
        <p>暂无文章</p>
      <?php endif; wp_reset_postdata(); ?>
    </div>
  </div>
</main>
<?php
get_footer();