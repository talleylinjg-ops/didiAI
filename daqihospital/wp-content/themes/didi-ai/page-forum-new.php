<?php
/**
 * 论坛发帖页面
 * Template Name: 论坛发帖
 */
if (!is_user_logged_in()) {
  wp_redirect(home_url('/forum'));
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['didi_forum_nonce']) && wp_verify_nonce($_POST['didi_forum_nonce'], 'didi_forum_new')) {
  $title = sanitize_text_field($_POST['topic_title']);
  $content = sanitize_textarea_field($_POST['topic_content']);
  if ($title && $content) {
    $pid = wp_insert_post(array(
      'post_type' => 'forum_topic',
      'post_title' => $title,
      'post_content' => $content,
      'post_status' => 'publish',
      'post_author' => get_current_user_id(),
    ));
    if ($pid) {
      wp_redirect(get_permalink($pid));
      exit;
    }
  }
}
get_header();
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 40px;">
  <div style="width:100%;max-width:720px;">
    <h1 style="font-size:28px;margin-bottom:20px;"><?php echo esc_html(daqi_t('发布新主题', 'New topic')); ?></h1>
    <form method="post" style="display:flex;flex-direction:column;gap:14px;">
      <?php wp_nonce_field('didi_forum_new', 'didi_forum_nonce'); ?>
      <input class="search-input" type="text" name="topic_title" placeholder="<?php echo esc_attr(daqi_t('标题', 'Title')); ?>" required style="font-size:16px;padding:12px 16px;">
      <textarea name="topic_content" placeholder="<?php echo esc_attr(daqi_t('内容', 'Content')); ?>" required style="min-height:180px;font-size:14px;padding:12px 16px;border:1px solid #e5e6eb;border-radius:10px;"></textarea>
      <button class="btn-primary" type="submit"><?php echo esc_html(daqi_t('发布', 'Post')); ?></button>
    </form>
  </div>
</main>
<?php
get_footer();
