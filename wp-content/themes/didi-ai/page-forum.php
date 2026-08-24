<?php
/**
 * 论坛页面（主题列表 + 左侧主题目录）
 * Template Name: 论坛
 */
get_header();
$paged = max(1, get_query_var('paged'));
$cat_slug = isset($_GET['cat']) ? sanitize_title($_GET['cat']) : '';
$q = new WP_Query(array(
  'post_type' => 'forum_topic',
  'posts_per_page' => 15,
  'paged' => $paged,
  's' => isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '',
  'category_name' => $cat_slug ? $cat_slug : '',
));
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 40px;">
  <h1 style="font-size:30px;margin-bottom:24px;">论坛</h1>
  <div style="display:grid;grid-template-columns:220px 1fr;gap:24px;width:100%;max-width:1080px;align-items:start;">
    <aside style="background:#fff;border:1px solid #e5e6eb;border-radius:12px;padding:16px;position:sticky;top:90px;max-height:calc(100vh - 140px);overflow-y:auto;">
      <?php echo didi_ai_sidebar_categories(home_url('/forum'), $cat_slug, '主题目录'); ?>
    </aside>
    <div>
      <form method="get" style="display:flex;gap:10px;margin-bottom:20px;">
        <input class="search-input" type="text" name="q" placeholder="搜索主题..." value="<?php echo esc_attr(isset($_GET['q']) ? $_GET['q'] : ''); ?>">
        <?php if ($cat_slug) : ?><input type="hidden" name="cat" value="<?php echo esc_attr($cat_slug); ?>"><?php endif; ?>
        <button class="btn-primary" type="submit">搜索</button>
      </form>
      <?php if (is_user_logged_in()) : ?>
        <a class="btn-primary" href="<?php echo esc_url(home_url('/forum-new')); ?>" style="display:inline-block;margin-bottom:16px;text-decoration:none;">发布新主题</a>
      <?php endif; ?>
      <?php if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post(); ?>
        <article style="padding:16px 0;border-bottom:1px solid #f2f3f5;">
          <a href="<?php the_permalink(); ?>" style="font-size:17px;font-weight:600;color:#1f2329;text-decoration:none;"><?php the_title(); ?></a>
          <div style="font-size:12px;color:#86909c;margin:6px 0;">
            <?php the_author(); ?> · <?php echo esc_html(get_the_date()); ?> · 回复 <?php echo esc_html(get_comments_number()); ?>
          </div>
        </article>
      <?php endwhile;
        $q->the_posts_pagination();
      else : ?>
        <p>暂无主题</p>
      <?php endif; wp_reset_postdata(); ?>
    </div>
  </div>
</main>
<?php
get_footer();