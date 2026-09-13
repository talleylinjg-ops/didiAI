<?php
/**
 * didi AI 主题功能
 */
if (!defined('ABSPATH')) exit;

add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

register_nav_menus(array(
  'primary' => '主导航',
));

function didi_ai_enqueue() {
  wp_enqueue_style('didi-ai', get_stylesheet_uri(), array(), filemtime(get_stylesheet_directory() . '/style.css'));
  wp_enqueue_script('didi-ai-common', get_template_directory_uri() . '/js/ai-common.js', array(), filemtime(get_template_directory() . '/js/ai-common.js'), false);
  wp_enqueue_script('didi-i18n', get_template_directory_uri() . '/js/i18n.js', array(), filemtime(get_template_directory() . '/js/i18n.js'), false);
}
add_action('wp_enqueue_scripts', 'didi_ai_enqueue');

function didi_ai_menu_items() {
  return array(
    'blog'    => array('url' => home_url('/blog'), 'label' => '博客'),
    'forum'   => array('url' => home_url('/forum'), 'label' => '论坛'),
    'recharge'=> array('url' => home_url('/recharge'), 'label' => '充值'),
    'admin'   => array('url' => admin_url(), 'label' => 'Admin'),
  );
}

// 注册论坛自定义文章类型
function didi_ai_forum_post_type() {
  register_post_type('forum_topic', array(
    'labels' => array(
      'name' => '论坛主题',
      'singular_name' => '论坛主题',
      'add_new_item' => '发布新主题',
    ),
    'public' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-format-chat',
    'rewrite' => array('slug' => 'forum'),
    'supports' => array('title', 'editor', 'author', 'comments', 'excerpt'),
    'taxonomies' => array('category'),
  ));
}
add_action('init', 'didi_ai_forum_post_type');

// 菜单栏（博客/论坛/充值/Admin）输出
function didi_ai_nav_links() {
  $links = didi_ai_menu_items();
  $out = '';
  foreach ($links as $k => $v) {
    $out .= '<a href="' . esc_url($v['url']) . '">' . esc_html($v['label']) . '</a>';
  }
  return $out;
}

/* ================= AI 配置（USER_* 占位环境变量） ================= */
/* ================= 配置管理（后台设置页优先，环境变量回退） ================= */
function didi_ai_cfg_get($section) {
  $all = get_option('didi_ai_config', array());
  $c = isset($all[$section]) ? $all[$section] : array();
  return array(
    'provider' => isset($c['provider']) ? trim($c['provider']) : '',
    'apiKey'   => isset($c['apiKey']) ? trim($c['apiKey']) : '',
    'baseUrl'  => isset($c['baseUrl']) ? trim($c['baseUrl']) : '',
    'model'    => isset($c['model']) ? trim($c['model']) : '',
  );
}

function didi_ai_cfg_merge($section, $env_defaults) {
  $db = didi_ai_cfg_get($section);
  return array(
    'provider' => $db['provider'] ?: (isset($env_defaults['provider']) ? $env_defaults['provider'] : ''),
    'apiKey'   => $db['apiKey'] ?: (isset($env_defaults['apiKey']) ? $env_defaults['apiKey'] : ''),
    'baseUrl'  => $db['baseUrl'] ?: (isset($env_defaults['baseUrl']) ? $env_defaults['baseUrl'] : ''),
    'model'    => $db['model'] ?: (isset($env_defaults['model']) ? $env_defaults['model'] : ''),
  );
}

function didi_ai_llm_cfg($section) {
  $llm_db = didi_ai_cfg_get('llm');
  $env = getenv('USER_LLM_API_KEY');
  $llm_sections = array('llm', 'code', 'work', 'music', 'write', 'ppt', 'avatar', 'voice', 'file');
  $defaults = array();
  foreach ($llm_sections as $s) {
    $P = strtoupper($s);
    $defaults[$s] = array(
      'baseUrl' => getenv('USER_' . $P . '_BASE_URL') ?: getenv('USER_LLM_BASE_URL') ?: ($llm_db['baseUrl'] ?: 'https://api.deepseek.com/v1'),
      'apiKey'  => getenv('USER_' . $P . '_API_KEY') ?: $env ?: $llm_db['apiKey'],
      'model'   => getenv('USER_' . $P . '_MODEL') ?: getenv('USER_LLM_MODEL') ?: ($llm_db['model'] ?: 'deepseek-chat'),
    );
  }
  if (!isset($defaults[$section])) $section = 'llm';
  return didi_ai_cfg_merge($section, $defaults[$section]);
}

function didi_ai_video_cfg($tier = 'domestic') {
  $env = $tier === 'intl' ? array(
    'provider' => getenv('USER_VIDEO_INTL_PROVIDER') ?: 'runway',
    'apiKey'   => getenv('USER_VIDEO_INTL_API_KEY') ?: getenv('USER_VIDEO_API_KEY') ?: '',
    'baseUrl'  => getenv('USER_VIDEO_INTL_BASE_URL') ?: getenv('USER_VIDEO_BASE_URL') ?: '',
    'model'    => getenv('USER_VIDEO_INTL_MODEL') ?: 'runway-gen3',
  ) : array(
    'provider' => getenv('USER_VIDEO_PROVIDER') ?: 'kling',
    'apiKey'   => getenv('USER_VIDEO_API_KEY') ?: '',
    'baseUrl'  => getenv('USER_VIDEO_BASE_URL') ?: '',
    'model'    => getenv('USER_VIDEO_MODEL') ?: 'kling-v1-6',
  );
  return didi_ai_cfg_merge($tier === 'intl' ? 'video_intl' : 'video', $env);
}

function didi_ai_animate_cfg() {
  $env = array(
    'provider' => getenv('USER_ANIMATE_PROVIDER') ?: 'animatediff',
    'apiKey'   => getenv('USER_ANIMATE_API_KEY') ?: '',
    'baseUrl'  => getenv('USER_ANIMATE_BASE_URL') ?: '',
    'model'    => getenv('USER_ANIMATE_MODEL') ?: 'animate_diff',
  );
  return didi_ai_cfg_merge('animate', $env);
}

function didi_ai_image_cfg() {
  $env = array(
    'provider' => getenv('USER_IMAGE_PROVIDER') ?: 'openai',
    'apiKey'   => getenv('USER_IMAGE_API_KEY') ?: getenv('USER_LLM_API_KEY') ?: '',
    'baseUrl'  => getenv('USER_IMAGE_BASE_URL') ?: '',
    'model'    => getenv('USER_IMAGE_MODEL') ?: 'dall-e-3',
  );
  return didi_ai_cfg_merge('image', $env);
}

function didi_ai_edit_cfg($type) {
  $type = in_array($type, array('video', 'image', 'animate')) ? $type : 'video';
  $env = array(
    'video' => array(
      'provider' => getenv('USER_EDIT_VIDEO_PROVIDER') ?: 'kling',
      'apiKey'   => getenv('USER_EDIT_VIDEO_API_KEY') ?: getenv('USER_VIDEO_API_KEY') ?: '',
      'baseUrl'  => getenv('USER_EDIT_VIDEO_BASE_URL') ?: getenv('USER_VIDEO_BASE_URL') ?: '',
      'model'    => getenv('USER_EDIT_VIDEO_MODEL') ?: getenv('USER_VIDEO_MODEL') ?: 'kling-v1-6',
    ),
    'image' => array(
      'provider' => getenv('USER_EDIT_IMAGE_PROVIDER') ?: 'openai',
      'apiKey'   => getenv('USER_EDIT_IMAGE_API_KEY') ?: getenv('USER_IMAGE_API_KEY') ?: getenv('USER_LLM_API_KEY') ?: '',
      'baseUrl'  => getenv('USER_EDIT_IMAGE_BASE_URL') ?: getenv('USER_IMAGE_BASE_URL') ?: '',
      'model'    => getenv('USER_EDIT_IMAGE_MODEL') ?: getenv('USER_IMAGE_MODEL') ?: 'gpt-image-1',
    ),
    'animate' => array(
      'provider' => getenv('USER_EDIT_ANIMATE_PROVIDER') ?: 'runway',
      'apiKey'   => getenv('USER_EDIT_ANIMATE_API_KEY') ?: getenv('USER_ANIMATE_API_KEY') ?: '',
      'baseUrl'  => getenv('USER_EDIT_ANIMATE_BASE_URL') ?: getenv('USER_ANIMATE_BASE_URL') ?: '',
      'model'    => getenv('USER_EDIT_ANIMATE_MODEL') ?: getenv('USER_ANIMATE_MODEL') ?: 'runway-gen3',
    ),
  );
  return didi_ai_cfg_merge('edit_' . $type, $env[$type]);
}

/* ================= 后台配置管理页 ================= */
function didi_ai_admin_menu() {
  add_menu_page('didi AI 配置', 'didi AI 配置', 'manage_options', 'didi-ai-config', 'didi_ai_admin_page', 'dashicons-admin-generic', 99);
}
add_action('admin_menu', 'didi_ai_admin_menu');

function didi_ai_admin_page() {
  if (isset($_POST['didi_cfg_save']) && check_admin_referer('didi_cfg_save')) {
    $sections = array('llm', 'code', 'work', 'music', 'write', 'ppt', 'avatar', 'voice', 'file', 'video', 'video_intl', 'animate', 'image', 'edit_video', 'edit_image', 'edit_animate');
    $all = array();
    foreach ($sections as $s) {
      if (isset($_POST['cfg'][$s])) {
        $all[$s] = array(
          'provider' => isset($_POST['cfg'][$s]['provider']) ? sanitize_text_field($_POST['cfg'][$s]['provider']) : '',
          'apiKey'   => isset($_POST['cfg'][$s]['apiKey']) ? sanitize_text_field($_POST['cfg'][$s]['apiKey']) : '',
          'baseUrl'  => isset($_POST['cfg'][$s]['baseUrl']) ? sanitize_text_field($_POST['cfg'][$s]['baseUrl']) : '',
          'model'    => isset($_POST['cfg'][$s]['model']) ? sanitize_text_field($_POST['cfg'][$s]['model']) : '',
        );
      }
    }
    update_option('didi_ai_config', $all, false);
    echo '<div class="notice notice-success"><p>配置已保存。</p></div>';
  }
  $all = get_option('didi_ai_config', array());
  $groups = array(
    'llm'         => '提问（Kimi K3）',
    'code'        => '代码（通义千问 Qwen3.8-Max）',
    'work'        => '工作（通义千问 Qwen3.8-Max）',
    'music'       => '音频（音潮 V4.0）',
    'write'       => '写作（Kimi K3）',
    'ppt'         => 'PPT（Kimi K3）',
    'avatar'      => '数字人（硅基智能 guiji）',
    'voice'       => '语音（腾讯混元 hunyuan-turbo）',
    'file'        => '文件（OpenAI GPT-4o）',
    'video'       => '视频 · 国内低价档（可灵 1.6 / 即梦）',
    'video_intl'  => '视频 · 国际高端档（Runway Gen-3）',
    'animate'     => '动漫（AnimateDiff）',
    'image'       => '图片（即梦 / OpenAI）',
    'edit_video'  => '剪辑 · 视频编辑（可灵）',
    'edit_image'  => '剪辑 · 图片编辑（即梦 / OpenAI）',
    'edit_animate'=> '剪辑 · 动画编辑（Runway）',
  );
  $providers = array('deepseek' => 'DeepSeek', 'kling' => '可灵 Kling', 'jimeng' => '即梦 Jimeng', 'runway' => 'Runway', 'animatediff' => 'AnimateDiff', 'openai' => 'OpenAI', 'sd' => 'Stable Diffusion');
  ?>
  <div class="wrap">
    <h1>didi AI 模型配置</h1>
    <p>在此填写各功能的模型接入信息。留空的字段会回退到服务端环境变量。Key 保存在数据库，不会显示明文回显。</p>
    <form method="post">
      <?php wp_nonce_field('didi_cfg_save'); ?>
      <table class="form-table" role="presentation">
        <?php foreach ($groups as $s => $label):
          $c = didi_ai_cfg_get($s);
        ?>
        <tr>
          <th scope="row" style="width:180px;vertical-align:top;"><strong><?php echo esc_html($label); ?></strong><br><span style="color:#777;font-weight:400;font-size:12px;">[<?php echo esc_html($s); ?>]</span></th>
          <td style="display:flex;gap:10px;flex-wrap:wrap;">
            <label>Provider<br>
              <select name="cfg[<?php echo esc_attr($s); ?>][provider]" style="min-width:140px;">
                <option value="">（沿用默认）</option>
                <?php foreach ($providers as $pv => $pn): ?>
                  <option value="<?php echo esc_attr($pv); ?>" <?php selected($c['provider'], $pv); ?>><?php echo esc_html($pn); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>API Key<br>
              <input type="password" name="cfg[<?php echo esc_attr($s); ?>][apiKey]" value="<?php echo esc_attr($c['apiKey']); ?>" style="min-width:220px;" autocomplete="new-password">
            </label>
            <label>Base URL<br>
              <input type="text" name="cfg[<?php echo esc_attr($s); ?>][baseUrl]" value="<?php echo esc_attr($c['baseUrl']); ?>" placeholder="https://api.xxx.com/v1" style="min-width:240px;">
            </label>
            <label>Model<br>
              <input type="text" name="cfg[<?php echo esc_attr($s); ?>][model]" value="<?php echo esc_attr($c['model']); ?>" placeholder="模型名称" style="min-width:160px;">
            </label>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <p class="submit"><input type="submit" name="didi_cfg_save" class="button-primary" value="保存配置"></p>
    </form>
    <h2>定价表（点数）</h2>
    <table class="widefat striped" style="max-width:640px;">
      <thead><tr><th>功能</th><th>模型</th><th>单位</th><th>单价</th></tr></thead>
      <tbody>
      <?php foreach (didi_ai_pricing() as $p): ?>
        <tr><td><?php echo esc_html($p['name']); ?></td><td><?php echo esc_html($p['model']); ?></td><td><?php echo esc_html($p['unit']); ?></td><td><?php echo esc_html($p['price']); ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php
}

/* ================= AI REST API ================= */
function didi_ai_register_routes() {
  register_rest_route('didi/v1', '/chat/stream', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_chat_stream',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/image', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_generate_image',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/video', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_generate_video',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/animate', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_generate_animate',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/history', array(
    'methods' => 'GET',
    'callback' => 'didi_ai_history',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/edit', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_edit',
    'permission_callback' => '__return_true',
  ));
}
add_action('rest_api_init', 'didi_ai_register_routes');

function didi_ai_post_json($url, $headers, $body) {
  $ch = curl_init($url);
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_HTTPHEADER => array_merge(array('Content-Type: application/json'), $headers),
    CURLOPT_POSTFIELDS => json_encode($body),
  ));
  $text = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
  if ($err) throw new Exception('网络请求失败: ' . $err);
  $data = json_decode($text, true);
  return array('status' => $status, 'data' => $data, 'raw' => $text);
}

function didi_ai_chat_stream($req) {
  $params = $req->get_json_params();
  $messages = isset($params['messages']) ? $params['messages'] : null;
  $section = isset($params['section']) ? $params['section'] : 'llm';
  if (!is_array($messages) || count($messages) === 0) {
    return new WP_Error('empty_messages', '消息不能为空', array('status' => 400));
  }
  // 配额预扣：llm=>提问，code=>代码，work=>工作
  $feature = $section === 'code' ? 'code' : ($section === 'work' ? 'work' : 'chat');
  // 发起请求即记录使用（无论后续 API 是否成功），保证使用记录可追踪
  if (is_user_logged_in()) {
    didi_ai_record_usage($feature, didi_ai_note_from_messages($messages));
  }
  $conf = didi_ai_llm_cfg($section);
  if (empty($conf['apiKey'])) {
    return new WP_Error('not_configured', '大模型接口未配置，请在服务端环境变量中填写 USER_LLM_* 系列配置', array('status' => 503));
  }
  $hold = 10;
  $charge = didi_ai_charge_hold($feature, $hold);
  if (!$charge['ok']) {
    return new WP_Error('no_quota', $charge['error'], array('status' => 402));
  }
  $base = rtrim($conf['baseUrl'], '/');
  $headers = array('Authorization: Bearer ' . $conf['apiKey']);
  $model = (isset($params['model']) && trim($params['model']) !== '') ? trim($params['model']) : $conf['model'];
  $customFlag = !empty($params['custom']);
  if ($customFlag && is_user_logged_in()) {
    $level = didi_ai_normalize_level(get_user_meta(get_current_user_id(), 'didi_membership', true));
    if ($level === 'free') {
      update_user_meta(get_current_user_id(), 'didi_membership', 'free');
    }
  }
  $body = array('model' => $model, 'messages' => $messages, 'temperature' => 0.7, 'stream' => true, 'stream_options' => array('include_usage' => true));

  $ch = curl_init($base . '/chat/completions');
  $chunks = '';
  $gotAnswer = false;
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 300,
    CURLOPT_HTTPHEADER => array_merge(array('Content-Type: application/json'), $headers),
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$chunks, &$gotAnswer) {
      $chunks .= $chunk;
      // 统一 OpenAI 兼容流格式：将 choices[0].delta.content / reasoning_content 转换为 delta 字段
      foreach (explode("\n", $chunk) as $line) {
        $line = trim($line);
        if (strpos($line, 'data:') !== 0) continue;
        $payload = trim(substr($line, 5));
        if ($payload === '[DONE]') { echo "data: [DONE]\n\n"; continue; }
        $o = json_decode($payload, true);
        if (is_array($o) && isset($o['choices'][0]['delta'])) {
          $text = isset($o['choices'][0]['delta']['content']) ? $o['choices'][0]['delta']['content'] : '';
          if ($text === '') {
            $text = isset($o['choices'][0]['delta']['reasoning_content']) ? $o['choices'][0]['delta']['reasoning_content'] : '';
          }
          if ($text !== '') {
            $gotAnswer = true;
            echo 'data: ' . json_encode(array('delta' => $text)) . "\n\n";
            continue;
          }
        }
        echo $line . "\n";
      }
      if (ob_get_level() > 0) { ob_flush(); }
      flush();
      return strlen($chunk);
    },
  ));
  curl_exec($ch);
  $err = curl_error($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  // 解析流中的 usage（OpenAI 兼容最后块含 usage.total_tokens）
  $tokens = 0;
  $raw = explode("\n", $chunks);
  foreach ($raw as $line) {
    $line = trim($line);
    if (strpos($line, 'data:') !== 0) continue;
    $payload = trim(substr($line, 5));
    if ($payload === '[DONE]') continue;
    $o = json_decode($payload, true);
    if (is_array($o) && isset($o['usage']['total_tokens'])) {
      $tokens = (int) $o['usage']['total_tokens'];
    }
  }
  $settle = didi_ai_settle_chat($feature, $tokens, $hold, $gotAnswer && !$err && (int) $httpCode < 400);

  // 追加结算信息到流末尾
  echo 'data: ' . json_encode(array('meta' => array(
    'feature' => $feature,
    'tokens' => $settle['tokens'],
    'cost' => $settle['cost'],
    'refund' => $settle['refund'],
    'balance' => $settle['balance'],
  ))) . "\n\n";
  if ($err) {
    echo 'data: ' . json_encode(array('error' => '网络请求失败: ' . $err)) . "\n\n";
  }
  echo "data: [DONE]\n\n";
  exit;
}

function didi_ai_generate_image($req) {
  $params = $req->get_json_params();
  $prompt = isset($params['prompt']) ? trim($params['prompt']) : '';
  if (!$prompt) return new WP_Error('empty_prompt', '提示词不能为空', array('status' => 400));
  $imageUrl = isset($params['imageUrl']) ? esc_url_raw(trim($params['imageUrl'])) : '';

  // 图生图：有参考图时走编辑端点
  if ($imageUrl) {
    $edit_req = new WP_REST_Request('POST', '/didi/v1/edit');
    $edit_req->set_body_params(array(
      'type' => 'image',
      'prompt' => $prompt,
      'provider' => isset($params['provider']) ? $params['provider'] : 'openai',
      'imageUrl' => $imageUrl,
      'size' => isset($params['size']) ? $params['size'] : '1024x1024',
    ));
    $edit = didi_ai_edit($edit_req);
    if (is_wp_error($edit)) return $edit;
    return array('provider' => isset($edit['provider']) ? $edit['provider'] : 'edit', 'urls' => $edit['urls'], 'raw' => isset($edit['raw']) ? $edit['raw'] : array());
  }

  $conf = didi_ai_image_cfg();
  if (empty($conf['apiKey'])) {
    return new WP_Error('not_configured', '图片接口未配置，请在服务端环境变量中填写 USER_IMAGE_* 系列配置', array('status' => 503));
  }
  $charge = didi_ai_charge('image', $prompt);
  if (!$charge['ok']) {
    return new WP_Error('no_quota', $charge['error'], array('status' => 402));
  }
  $size = isset($params['size']) ? $params['size'] : '1024x1024';
  $provider = isset($params['provider']) ? strtolower($params['provider']) : $conf['provider'];
  $base = rtrim($conf['baseUrl'] ? $conf['baseUrl'] : 'https://api.openai.com/v1', '/');
  $model = (isset($params['model']) && trim($params['model']) !== '') ? sanitize_text_field($params['model']) : ($conf['model'] ? $conf['model'] : ($provider === 'modelscope' ? 'Flux.1-schnell' : ($provider === 'jimeng' ? getenv('USER_IMAGE_JIMENG_MODEL') ?: 'doubao-seedream' : 'dall-e-3')));
  if ($provider === 'modelscope') $model = getenv('USER_IMAGE_MODELSCOPE_MODEL') ?: 'Flux.1-schnell';
  if ($provider === 'gemini') $model = getenv('USER_IMAGE_GEMINI_MODEL') ?: 'gemini-2.5-flash-image';
  $body = array(
    'model' => $model,
    'prompt' => $prompt,
    'n' => 1,
    'size' => $size,
  );
  try {
    $res = didi_ai_post_json($base . '/images/generations', array('Authorization: Bearer ' . $conf['apiKey']), $body);
    $urls = array();
    if (isset($res['data']['data']) && is_array($res['data']['data'])) {
      foreach ($res['data']['data'] as $d) {
        if (!empty($d['url'])) $urls[] = $d['url'];
        elseif (!empty($d['b64_json'])) $urls[] = 'data:image/png;base64,' . $d['b64_json'];
      }
    }
    if (empty($urls)) throw new Exception('图片生成接口未返回结果');
    return array('provider' => $provider, 'urls' => $urls, 'raw' => $res['data']);
  } catch (Exception $e) {
    didi_ai_refund('image');
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  }
}

function didi_ai_generate_video($req) {
  $params = $req->get_json_params();
  $prompt = isset($params['prompt']) ? trim($params['prompt']) : '';
  if (!$prompt) return new WP_Error('empty_prompt', '提示词不能为空', array('status' => 400));
  $tier = isset($params['tier']) ? sanitize_key($params['tier']) : 'standard';
  $tier = in_array($tier, array('standard', 'premium', 'economy', 'intl')) ? $tier : 'standard';
  $isIntl = $tier === 'intl';
  $tierKey = $isIntl ? 'intl' : 'domestic';
  $tierName = $isIntl ? '国际高端' : ($tier === 'premium' ? '高端' : ($tier === 'economy' ? '经济' : '标准'));
  $conf = didi_ai_video_cfg($tierKey);
  if (empty($conf['apiKey']) || empty($conf['baseUrl'])) {
    $env_key = $isIntl ? 'USER_VIDEO_INTL_*' : 'USER_VIDEO_*';
    return new WP_Error('not_configured', $tierName . '档视频接口未配置，请在服务端环境变量或后台配置中填写 ' . $env_key . ' 系列配置', array('status' => 503));
  }
  $feature = $isIntl ? 'video_intl' : 'video';
  $charge = didi_ai_charge($feature, $prompt);
  if (!$charge['ok']) {
    return new WP_Error('no_quota', $charge['error'], array('status' => 402));
  }
  $duration = isset($params['duration']) ? (int) $params['duration'] : 5;
  $provider = isset($params['provider']) ? strtolower($params['provider']) : $conf['provider'];
  $provider = in_array($provider, array('kling', 'seedance', 'jimeng', 'runway', 'modelscope', 'openai', 'custom')) ? $provider : $conf['provider'];
  $base = rtrim($conf['baseUrl'], '/');
  $model = (isset($params['model']) && trim($params['model']) !== '') ? sanitize_text_field($params['model']) : $conf['model'];
  if ($provider === 'seedance') {
    $model = getenv('USER_VIDEO_SEEDANCE_MODEL') ?: $conf['model'];
  } elseif ($provider === 'jimeng') {
    $model = getenv('USER_VIDEO_JIMENG_MODEL') ?: $conf['model'];
  } elseif ($provider === 'modelscope') {
    $model = getenv('USER_VIDEO_MODELSCOPE_MODEL') ?: 'Wan2.1-I2V-14B-480P';
  }
  $body = array(
    'model' => $model,
    'prompt' => $prompt,
    'duration' => $duration,
  );
  if ($provider === 'kling') $body['mode'] = 'std';
  if ($provider === 'runway') {
    $body['text'] = $prompt;
    $body['ratio'] = '1280:768';
    $body['duration'] = $duration > 5 ? 10 : 5;
    unset($body['prompt']);
  }
  try {
    $res = didi_ai_post_json($base . '/videos/generations', array('Authorization: Bearer ' . $conf['apiKey']), $body);
    $data = is_array($res['data']) ? $res['data'] : array();
    return array(
      'provider' => $provider,
      'tier' => $tier,
      'taskId' => isset($data['id']) ? $data['id'] : (isset($data['task_id']) ? $data['task_id'] : (isset($data['request_id']) ? $data['request_id'] : '')),
      'status' => isset($data['status']) ? $data['status'] : (isset($data['task_status']) ? $data['task_status'] : 'submitted'),
      'raw' => $data,
    );
  } catch (Exception $e) {
    didi_ai_refund($isIntl ? 'video_intl' : 'video');
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  }
}

function didi_ai_generate_animate($req) {
  $params = $req->get_json_params();
  $prompt = isset($params['prompt']) ? trim($params['prompt']) : '';
  if (!$prompt) return new WP_Error('empty_prompt', '提示词不能为空', array('status' => 400));
  $conf = didi_ai_animate_cfg();
  if (empty($conf['apiKey']) || empty($conf['baseUrl'])) {
    return new WP_Error('not_configured', '动画接口未配置，请在服务端环境变量中填写 USER_ANIMATE_* 系列配置', array('status' => 503));
  }
  $charge = didi_ai_charge('animate', $prompt);
  if (!$charge['ok']) {
    return new WP_Error('no_quota', $charge['error'], array('status' => 402));
  }
  $provider = isset($params['provider']) ? strtolower($params['provider']) : $conf['provider'];
  $base = rtrim($conf['baseUrl'], '/');
  $model = (isset($params['model']) && trim($params['model']) !== '') ? sanitize_text_field($params['model']) : $conf['model'];
  $body = array(
    'model' => $model,
    'prompt' => $prompt,
    'frames' => isset($params['frames']) ? (int) $params['frames'] : 16,
    'width' => isset($params['width']) ? (int) $params['width'] : 512,
    'height' => isset($params['height']) ? (int) $params['height'] : 512,
  );
  $imageUrl = isset($params['imageUrl']) ? esc_url_raw(trim($params['imageUrl'])) : '';
  if ($imageUrl) $body['init_image'] = $imageUrl;
  try {
    $res = didi_ai_post_json($base . '/animate/generations', array('Authorization: Bearer ' . $conf['apiKey']), $body);
    $data = is_array($res['data']) ? $res['data'] : array();
    return array(
      'provider' => $provider,
      'taskId' => isset($data['id']) ? $data['id'] : (isset($data['task_id']) ? $data['task_id'] : ''),
      'status' => isset($data['status']) ? $data['status'] : 'submitted',
      'raw' => $data,
    );
  } catch (Exception $e) {
    didi_ai_refund('animate');
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  }
}

function didi_ai_usage_module($feature) {
  if (strpos($feature, 'edit_') === 0) return 'edit';
  $map = array(
    'chat' => 'ask',
    'code' => 'code',
    'work' => 'work',
    'image' => 'image',
    'video' => 'video',
    'video_intl' => 'video',
    'animate' => 'animate',
    'edit' => 'edit',
  );
  return isset($map[$feature]) ? $map[$feature] : 'ask';
}

// 使用记录 note 规范化（压缩空白并截断至 50 字）
function didi_ai_note_short($text) {
  $text = trim((string) $text);
  $text = preg_replace('/\s+/u', ' ', $text);
  if (function_exists('mb_substr') && function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > 50) {
    $text = mb_substr($text, 0, 50, 'UTF-8') . '…';
  }
  return $text;
}

function didi_ai_record_usage($feature, $note = '') {
  if (!is_user_logged_in()) return;
  $uid = get_current_user_id();
  $module = didi_ai_usage_module($feature);
  $usage = get_user_meta($uid, 'didi_usage', true);
  if (!is_array($usage)) $usage = array();
  if (!isset($usage[$module]) || !is_array($usage[$module])) $usage[$module] = array('count' => 0, 'last' => 0, 'logs' => array());
  $usage[$module]['count'] = (int) $usage[$module]['count'] + 1;
  $usage[$module]['last'] = time();
  if ($note !== '') {
    $logs = (isset($usage[$module]['logs']) && is_array($usage[$module]['logs'])) ? $usage[$module]['logs'] : array();
    array_unshift($logs, array('t' => time(), 'note' => $note));
    if (count($logs) > 20) $logs = array_slice($logs, 0, 20);
    $usage[$module]['logs'] = $logs;
  }
  update_user_meta($uid, 'didi_usage', $usage);
}

// 消费流水：type = recharge(充值) / membership(开通会员) / spend(消费) / refund(退款) / bonus(奖励)
function didi_ai_billing_log($type, $feature, $cost, $balance, $note = '') {
  if (!is_user_logged_in()) return;
  $uid = get_current_user_id();
  $logs = get_user_meta($uid, 'didi_billing', true);
  if (!is_array($logs)) $logs = array();
  array_unshift($logs, array('t' => time(), 'type' => $type, 'feature' => $feature, 'cost' => (float) $cost, 'balance' => (float) $balance, 'note' => $note));
  if (count($logs) > 200) $logs = array_slice($logs, 0, 200);
  update_user_meta($uid, 'didi_billing', $logs);
}

// 最近消费记录（供会员中心展示）
function didi_ai_billing_items($limit = 50) {
  if (!is_user_logged_in()) return array();
  $logs = get_user_meta(get_current_user_id(), 'didi_billing', true);
  if (!is_array($logs)) $logs = array();
  return array_slice($logs, 0, max(1, min((int) $limit, 200)));
}

function didi_ai_note_from_messages($messages) {
  if (!is_array($messages)) return '';
  for ($i = count($messages) - 1; $i >= 0; $i--) {
    $m = $messages[$i];
    if (isset($m['role']) && $m['role'] === 'user' && !empty($m['content'])) {
      $note = is_string($m['content']) ? trim($m['content']) : '';
      $note = preg_replace('/\s+/u', ' ', $note);
      if (function_exists('mb_substr') && mb_strlen($note, 'UTF-8') > 50) {
        $note = mb_substr($note, 0, 50, 'UTF-8') . '…';
      }
      return $note;
    }
  }
  return '';
}

function didi_ai_history($req) {
  if (!is_user_logged_in()) return array('items' => array());
  $uid = get_current_user_id();
  $usage = get_user_meta($uid, 'didi_usage', true);
  if (!is_array($usage)) $usage = array();
  $modules = array(
    'code'    => array('label' => '代码', 'icon' => '&#128187;', 'url' => '/ai-code'),
    'work'    => array('label' => '工作', 'icon' => '&#128202;', 'url' => '/ai-work'),
    'video'   => array('label' => '视频', 'icon' => '&#128250;', 'url' => '/ai-video'),
    'animate' => array('label' => '动画', 'icon' => '&#128126;', 'url' => '/ai-animate'),
    'image'   => array('label' => '图片', 'icon' => '&#127912;', 'url' => '/ai-image'),
    'edit'    => array('label' => '剪辑', 'icon' => '&#9998;', 'url' => '/ai-edit'),
  );
  $items = array();
  foreach ($modules as $key => $m) {
    $u = isset($usage[$key]) ? $usage[$key] : array('count' => 0, 'last' => 0);
    $items[] = array(
      'key'   => $key,
      'label' => $m['label'],
      'icon'  => $m['icon'],
      'url'   => home_url($m['url']),
      'count' => (int) $u['count'],
      'last'  => (int) $u['last'],
      'logs'  => (isset($u['logs']) && is_array($u['logs'])) ? array_values($u['logs']) : array(),
    );
  }
  return array('items' => $items);
}

/* ============ ChatCut 剪辑规则库（复刻 OpenChatCut/Agent 工作流：话术拆分、删减口癖停顿、字幕、节奏、转场） ============ */
function didi_ai_chatcut_rules() {
  return array(
    'transcribe'   => '先转写：把素材音视频转为带时间码的逐句文字稿，识别说话人，作为后续剪辑的索引。',
    'split'        => '按话术拆分：以语义完整的句子为单位切分片段，每个切点在句首或句末，避免切断词语与气息。',
    'trim'         => '删减冗余：批量清除口癖（嗯/啊/那个）、长停顿（>0.8s）、重复表达与无效开场，删点精确到帧。',
    'reorder'      => '重组素材：按叙事/节奏目标调整片段顺序，删除与主题无关的镜头，保证逻辑连贯。',
    'subtitle'     => '生成字幕：每句字幕不超过 20 字，时间码对齐话音起点，分屏字幕强调关键词，自动校正错别字。',
    'pace'         => '控节奏：口播片剪到 1.2~1.5 倍速紧凑感，留 0.3s 缓冲呼吸点，知识/探店片保持信息密度不赶。',
    'transition'   => '加转场：相邻主题跳切用叠化或擦除，观点转折用硬切，同一场景长镜头内部不加转场。',
    'audio'        => '铺声音：按氛围选背景乐，人声段压低 BGM 至 -18dB 以下，结尾淡出，必要时补环境音。',
    'style'        => '风格化：按目标平台统一色彩与滤镜（信息流高饱和/访谈自然光），关键镜头做增强。',
    'export'       => '导出校验：核对片长、字幕同步、音画对齐，检查片头片尾无空帧再导出。',
  );
}

function didi_ai_chatcut_prompt($type, $userPrompt) {
  $rules = didi_ai_chatcut_rules();
  $focus = $type === 'image' ? array('subtitle', 'style') : array('transcribe', 'split', 'trim', 'reorder', 'subtitle', 'pace', 'transition', 'audio', 'style', 'export');
  $lib = '';
  foreach ($focus as $k) {
    if (isset($rules[$k])) $lib .= "\n- " . $rules[$k];
  }
  return "你是专业视频剪辑 Agent，复刻 OpenChatCut 工作流。遵循以下剪辑规则执行本次任务：{$lib}\n\n用户指令：{$userPrompt}\n\n请直接执行剪辑动作并输出结果，不要复述规则。";
}

/* ================= AI 编辑代理端点（可灵图生视频 / 即梦图片编辑 / OpenAI images edit / Runway） ================= */
function didi_ai_edit($req) {
  $params = $req->get_json_params();
  $type = isset($params['type']) ? sanitize_key($params['type']) : 'video';
  $type = in_array($type, array('video', 'image', 'animate')) ? $type : 'video';
  $prompt = isset($params['prompt']) ? trim($params['prompt']) : '';
  if ($prompt) $prompt = didi_ai_chatcut_prompt($type, $prompt);
  $provider = isset($params['provider']) ? strtolower(sanitize_key($params['provider'])) : '';
  $imageUrl = isset($params['imageUrl']) ? esc_url_raw(trim($params['imageUrl'])) : '';

  if (!$prompt) return new WP_Error('empty_prompt', '编辑指令不能为空', array('status' => 400));
  if (!$imageUrl) return new WP_Error('empty_media', '请上传原始图片或视频素材', array('status' => 400));

  $conf = didi_ai_edit_cfg($type);
  if (empty($conf['apiKey']) || empty($conf['baseUrl'])) {
    return new WP_Error('not_configured',
      $type === 'video' ? '视频编辑接口未配置：请在服务端环境变量填写 USER_EDIT_VIDEO_* 系列（可灵 Kling 图生视频 / 视频编辑）'
      : ($type === 'image' ? '图片编辑接口未配置：请在服务端环境变量填写 USER_EDIT_IMAGE_* 系列（即梦 / OpenAI images edit）'
      : '动画编辑接口未配置：请在服务端环境变量填写 USER_EDIT_ANIMATE_* 系列（Runway）'),
      array('status' => 503));
  }
  $provider = $provider ? $provider : $conf['provider'];
  // 按 provider 定价扣费：可灵30 / 即梦25 / Runway80 / OpenAI25
  $editFeature = $provider === 'runway' ? 'edit_runway' : ($provider === 'kling' ? 'edit_kling' : ($provider === 'jimeng' ? 'edit_jimeng' : ($provider === 'openai' ? 'edit_openai' : 'edit')));
  $charge = didi_ai_charge($editFeature, $prompt);
  if (!$charge['ok']) {
    return new WP_Error('no_quota', $charge['error'], array('status' => 402));
  }

  $base = rtrim($conf['baseUrl'], '/');
  $model = (isset($params['model']) && trim($params['model']) !== '') ? sanitize_text_field($params['model']) : $conf['model'];

  // 下载素材为临时文件
  $tmp = download_url($imageUrl);
  if (is_wp_error($tmp)) {
    didi_ai_refund($editFeature);
    return new WP_Error('media_fetch', '素材下载失败', array('status' => 400));
  }
  $mime = mime_content_type($tmp);
  $isImage = $type === 'image' || (strpos($mime, 'image/') === 0 && $type !== 'video');

  try {
    if ($type === 'image') {
      // 即梦 / OpenAI images edit：multipart form
      $body = array(
        'model' => $model,
        'prompt' => $prompt,
        'n' => 1,
        'size' => isset($params['size']) ? sanitize_text_field($params['size']) : '1024x1024',
        'image' => new CURLFile($tmp, $mime, basename($imageUrl)),
      );
      $res = didi_ai_post_form($base . '/images/edits', array('Authorization: Bearer ' . $conf['apiKey']), $body);
      $urls = array();
      if (isset($res['data']['data']) && is_array($res['data']['data'])) {
        foreach ($res['data']['data'] as $d) {
          if (!empty($d['url'])) $urls[] = $d['url'];
          elseif (!empty($d['b64_json'])) $urls[] = 'data:image/png;base64,' . $d['b64_json'];
        }
      }
      if (empty($urls)) throw new Exception('图片编辑接口未返回结果');
      return array('provider' => $provider, 'type' => 'image', 'urls' => $urls, 'raw' => $res['data']);
    }

    if ($type === 'video') {
      // 可灵 Kling 图生视频 / 视频编辑
      $body = array(
        'model' => $model,
        'prompt' => $prompt,
        'duration' => isset($params['duration']) ? (int) $params['duration'] : 5,
      );
      if ($provider === 'kling') {
        $body['image'] = $imageUrl;           // 图生视频
        $body['mode'] = 'std';
      } elseif ($provider === 'jimeng') {
        $body['prompt'] = $prompt;
        $body['source'] = $imageUrl;
      } else {
        $body['promptImage'] = $imageUrl;     // 通用 OpenAI 兼容
      }
      $res = didi_ai_post_json($base . '/videos/edits', array('Authorization: Bearer ' . $conf['apiKey']), $body);
      $data = is_array($res['data']) ? $res['data'] : array();
      return array(
        'provider' => $provider,
        'type' => 'video',
        'taskId' => isset($data['id']) ? $data['id'] : (isset($data['task_id']) ? $data['task_id'] : ''),
        'status' => isset($data['status']) ? $data['status'] : 'submitted',
        'raw' => $data,
      );
    }

    // animate（Runway 视频重绘 / 风格化）
    $body = array(
      'model' => $model,
      'prompt' => $prompt,
      'init_image' => $imageUrl,
    );
    $res = didi_ai_post_json($base . '/videos/edits', array('Authorization: Bearer ' . $conf['apiKey']), $body);
    $data = is_array($res['data']) ? $res['data'] : array();
    return array(
      'provider' => $provider,
      'type' => 'animate',
      'taskId' => isset($data['id']) ? $data['id'] : (isset($data['task_id']) ? $data['task_id'] : ''),
      'status' => isset($data['status']) ? $data['status'] : 'submitted',
      'raw' => $data,
    );
  } catch (Exception $e) {
    didi_ai_refund($editFeature);
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  } finally {
    if (file_exists($tmp)) @unlink($tmp);
  }
}

function didi_ai_post_form($url, $headers, $fields) {
  $ch = curl_init($url);
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 180,
    CURLOPT_HTTPHEADER => array_merge(array('Content-Type: multipart/form-data'), $headers),
    CURLOPT_POSTFIELDS => $fields,
  ));
  $text = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
  if ($err) throw new Exception('网络请求失败: ' . $err);
  $data = json_decode($text, true);
  return array('status' => $status, 'data' => $data, 'raw' => $text);
}

/* ================= 访客权限控制 ================= */
// 隐藏 ADMIN 后台入口：禁用前台管理工具条；非管理员在前台看不到后台链接
add_filter('show_admin_bar', '__return_false');
function didi_ai_guest_access_control() {
  if (is_user_logged_in()) return;
  if (is_admin()) return;

  $path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
  $path = '/' . ltrim($path, '/');
  $path = rtrim($path, '/');   // 归一化：/login/ -> /login
  if ($path === '') $path = '/';

  // 访客（未登录）公开页面：首页、博客、论坛、充值、会员、登录注册、WP 基础接口
  // 以及搜索引擎 / 生成式引擎必需的文件（robots / sitemap / llms）
  $public = array('/blog', '/forum', '/recharge', '/member', '/login', '/register', '/wp-login.php', '/wp-admin', '/wp-json', '/feed', '/wp-cron.php', '/xmlrpc.php', '/robots.txt', '/llms.txt', '/sitemap.xml', '/wp-sitemap.xml', '/wp-sitemap');

  if ($path === '/') return;
  foreach ($public as $a) {
    if (strpos($path, $a) === 0) return;
  }

  // 其余（AI 创作 /ai-*、发帖 /forum-new 等）需登录
  wp_redirect(home_url('/login'));
  exit;
}
add_action('template_redirect', 'didi_ai_guest_access_control', 1);

/* ================= 首页 embed 模式（iframe 内嵌功能页） ================= */
// 移除 WP 内核 oEmbed 对 ?embed=1 的拦截，让主题自己的 embed 逻辑生效
add_filter('query_vars', function ($vars) {
  return array_diff($vars, array('embed'));
});
function didi_ai_is_embed() {
  return isset($_GET['embed']) && $_GET['embed'] === '1';
}
add_filter('body_class', function ($classes) {
  if (didi_ai_is_embed()) $classes[] = 'didi-embed';
  return $classes;
});
add_action('wp_head', function () {
  if (is_user_logged_in()) {
    echo '<script>window.didiRestNonce=' . wp_json_encode(wp_create_nonce('wp_rest')) . ';</script>';
  }
  if (didi_ai_is_embed()) {
    echo '<style>
      body.didi-embed header, body.didi-embed footer, body.didi-embed .lang-box { display:none !important; }
      body.didi-embed .ai-page { padding-top:20px; }
      body.didi-embed .codex-page { height:100vh; }
      body.didi-embed .codex-page .page-header { padding-top:0; }
      body.didi-embed .codex-layout { height:calc(100vh - 150px); }
      body.didi-embed main { padding-top:20px; }
    </style>';
  }
});

/* ================= SEO / GEO（搜索引擎 + 生成式引擎优化） ================= */
// 由主题统一输出 canonical，移除内核重复版本；移除 WordPress 版本号暴露
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_generator');

// 站点核心关键词（含国内/全球模型名，便于生成式引擎检索）
function didi_ai_seo_keywords() {
  return 'didi AI,AI 工具台,AI 对话,AI 提问,AI 代码,AI 写作,AI PPT,AI 生成图片,AI 生成视频,数字人,AI 动画,AI 音频,AI 剪辑,Kimi,通义千问,可灵,即梦,Seedream,Veo,GPT Image,国产大模型';
}

// 当前页面 SEO 上下文
function didi_ai_seo_context() {
  $home = untrailingslashit(home_url('/'));
  $site = get_bloginfo('name');
  if (!$site) $site = 'didi AI';
  $ctx = array(
    'site'   => $site,
    'home'   => $home,
    'title'  => $site . ' · 一站式 AI 工具台',
    'desc'   => 'didi AI 聚合国内与全球最强 AI 模型，提供 AI 对话提问、代码生成、办公写作、PPT、图片生成、视频生成、数字人、动画、音频与智能剪辑，一个工作台完成全部内容生产。',
    'url'    => $home . '/',
    'type'   => 'website',
    'robots' => 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1',
    'is_tool' => false,
    'private' => false,
  );

  if (is_front_page()) {
    $ctx['title'] = 'didi AI · 一站式 AI 工具台（对话/代码/写作/图片/视频/数字人/剪辑）';
    $ctx['url']   = $home . '/';
  } elseif (is_singular()) {
    $ctx['title'] = get_the_title() . ' · ' . $site;
    $raw = has_excerpt() ? get_the_excerpt() : get_the_content();
    $raw = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($raw)));
    $ctx['desc'] = $raw !== '' ? mb_substr($raw, 0, 150) : ($ctx['title'] . '，didi AI 一站式 AI 工具台。');
    $ctx['url']  = get_permalink();
    $ctx['type'] = 'article';
    $slug = get_post_field('post_name');
    $ctx['is_tool'] = (get_post_type() === 'page' && strpos((string)$slug, 'ai-') === 0);
  } elseif (is_category() || is_tag() || is_tax()) {
    $term = get_queried_object();
    $tname = ($term && !is_wp_error($term)) ? $term->name : '';
    $ctx['title'] = $tname . ' · ' . $site . ' 内容聚合';
    $ctx['desc']  = 'didi AI 关于「' . $tname . '」的内容聚合，汇集 AI 模型能力、应用实践与行业观察。';
    $link = get_term_link($term);
    if (!is_wp_error($link)) $ctx['url'] = $link;
  } elseif (is_archive()) {
    $ctx['title'] = wp_strip_all_tags(get_the_archive_title()) . ' · ' . $site;
    $ctx['desc']  = 'didi AI 博客与论坛的内容归档，涵盖 AI 应用、模型测评与行业实践。';
    $ctx['url']   = get_pagenum_link(1);
  } elseif (is_search()) {
    $ctx['title']  = '搜索：' . get_search_query() . ' · ' . $site;
    $ctx['desc']   = '在 didi AI 中搜索相关内容。';
    $ctx['url']    = get_search_link();
    $ctx['robots'] = 'noindex,follow';
  }

  // 登录 / 注册 / 会员 / 充值等私密页不收录
  if (is_page_template(array('page-login.php', 'page-member.php', 'page-recharge.php'))) {
    $ctx['robots'] = 'noindex,nofollow';
    $ctx['private'] = true;
  }
  if (didi_ai_is_embed()) {
    $ctx['robots'] = 'noindex,nofollow';
    $ctx['private'] = true;
  }
  return $ctx;
}

// 结构化数据（JSON-LD）
function didi_ai_seo_jsonld($c) {
  $home   = $c['home'] . '/';
  $org_id = $home . '#organization';
  $web_id = $home . '#website';
  $graph  = array(
    array(
      '@type' => 'Organization',
      '@id'   => $org_id,
      'name'  => $c['site'],
      'url'   => $home,
      'description' => 'didi AI 是聚合国内与全球最强 AI 模型的一站式在线工具台。',
    ),
    array(
      '@type' => 'WebSite',
      '@id'   => $web_id,
      'url'   => $home,
      'name'  => $c['site'],
      'inLanguage' => 'zh-CN',
      'publisher'  => array('@id' => $org_id),
      'potentialAction' => array(
        '@type' => 'SearchAction',
        'target' => array('@type' => 'EntryPoint', 'urlTemplate' => $home . '?s={search_term_string}'),
        'query-input' => 'required name=search_term_string',
      ),
    ),
    array(
      '@type' => 'WebPage',
      '@id'   => $c['url'] . '#webpage',
      'url'   => $c['url'],
      'name'  => $c['title'],
      'description' => $c['desc'],
      'isPartOf' => array('@id' => $web_id),
      'inLanguage' => 'zh-CN',
    ),
  );
  if (!empty($c['is_tool'])) {
    $graph[] = array(
      '@type' => 'SoftwareApplication',
      'name'  => $c['title'],
      'url'   => $c['url'],
      'applicationCategory' => 'AIApplication',
      'operatingSystem' => 'Web',
      'description' => $c['desc'],
      'offers' => array('@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'CNY'),
    );
  }
  echo '<script type="application/ld+json">' . wp_json_encode(
    array('@context' => 'https://schema.org', '@graph' => $graph),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ) . '</script>' . "\n";
}

// 输出 SEO meta（在 head 最前）
add_action('wp_head', function () {
  if (is_admin() || is_feed()) return;
  $c = didi_ai_seo_context();
  $icon = get_site_icon_url();

  if (!empty($c['url']) && !is_wp_error($c['url'])) {
    echo '<link rel="canonical" href="' . esc_url($c['url']) . '">' . "\n";
  }
  echo '<meta name="robots" content="' . esc_attr($c['robots']) . '">' . "\n";
  echo '<meta name="description" content="' . esc_attr($c['desc']) . '">' . "\n";
  echo '<meta name="keywords" content="' . esc_attr(didi_ai_seo_keywords()) . '">' . "\n";
  echo '<meta name="author" content="' . esc_attr($c['site']) . '">' . "\n";

  echo '<meta property="og:site_name" content="' . esc_attr($c['site']) . '">' . "\n";
  echo '<meta property="og:type" content="' . esc_attr($c['type']) . '">' . "\n";
  echo '<meta property="og:title" content="' . esc_attr($c['title']) . '">' . "\n";
  echo '<meta property="og:description" content="' . esc_attr($c['desc']) . '">' . "\n";
  if (!empty($c['url']) && !is_wp_error($c['url'])) {
    echo '<meta property="og:url" content="' . esc_url($c['url']) . '">' . "\n";
  }
  echo '<meta property="og:locale" content="zh_CN">' . "\n";
  if ($icon) echo '<meta property="og:image" content="' . esc_url($icon) . '">' . "\n";

  echo '<meta name="twitter:card" content="' . ($icon ? 'summary_large_image' : 'summary') . '">' . "\n";
  echo '<meta name="twitter:title" content="' . esc_attr($c['title']) . '">' . "\n";
  echo '<meta name="twitter:description" content="' . esc_attr($c['desc']) . '">' . "\n";
  if ($icon) echo '<meta name="twitter:image" content="' . esc_url($icon) . '">' . "\n";

  didi_ai_seo_jsonld($c);
}, 1);

// robots.txt：放行内容、屏蔽后台与私密页，并指向 sitemap
add_filter('robots_txt', function ($output, $public) {
  $home = untrailingslashit(home_url('/'));
  $lines = array(
    'User-agent: *',
    'Allow: /',
    'Disallow: /wp-admin/',
    'Disallow: /wp-login.php',
    'Disallow: /member',
    'Disallow: /recharge',
    'Disallow: /forum-new',
    'Disallow: /?s=',
    'Disallow: /*?s=',
    'Allow: /wp-admin/admin-ajax.php',
    '',
    '# 生成式引擎可读取的站点摘要',
    'Sitemap: ' . $home . '/wp-sitemap.xml',
  );
  return implode("\n", $lines) . "\n";
}, 10, 2);

// llms.txt：为生成式引擎（GEO）提供站点结构化摘要
add_action('template_redirect', function () {
  $path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
  if (!preg_match('#/llms\.txt$#', $path)) return;
  header('Content-Type: text/plain; charset=utf-8');
  header('X-Robots-Tag: all');
  $home = untrailingslashit(home_url('/'));
  $tools = array(
    array('AI 提问（多模型对话）', '/ai-chat',  '聚合国内与全球最强对话模型，支持语音、文件与图片输入。'),
    array('AI 代码',             '/ai-code',  '代码生成、解释与多文件工程编辑。'),
    array('AI 工作台',           '/ai-work',  '文档撰写、总结、方案与数据整理。'),
    array('AI 图片生成',         '/ai-image', '即梦 Seedream、GPT Image、Nano Banana 等多模型出图。'),
    array('AI 视频生成',         '/ai-video', '可灵、Veo、Runway 等文生视频与图生视频。'),
    array('AI 数字人',           '/ai-avatar','硅基智能、HeyGen 等数字人视频合成。'),
    array('AI 动画',             '/ai-animate','可灵、Veo、AnimateDiff 图生动画。'),
    array('AI 音频',             '/ai-music', '音潮、Suno 等音乐与音频生成。'),
    array('AI 写作',             '/ai-write', '长文、公文、营销文案创作。'),
    array('AI PPT',              '/ai-ppt',   '一键生成演示文稿大纲与页面。'),
    array('AI 智能剪辑',         '/ai-edit',  '视频、动画、图片、音频的 AI 剪辑。'),
  );
  $out  = "# didi AI\n\n";
  $out .= "> didi AI 是聚合国内与全球最强 AI 模型的一站式在线工具台，覆盖对话、代码、写作、PPT、图片、视频、数字人、动画、音频与智能剪辑。\n\n";
  $out .= "## 核心工具\n";
  foreach ($tools as $t) {
    $out .= '- [' . $t[0] . '](' . $home . $t[1] . ')：' . $t[2] . "\n";
  }
  $out .= "\n## 内容\n";
  $out .= '- [博客](' . $home . '/blog)：AI 模型能力、应用实践与行业观察。' . "\n";
  $out .= '- [论坛](' . $home . '/forum)：AI 使用经验与技巧交流。' . "\n";
  $out .= "\n## 说明\n";
  $out .= "- 站点语言：简体中文\n- 主要栏目：AI 工具台、博客、论坛\n- Sitemap：" . $home . "/wp-sitemap.xml\n";
  echo $out;
  exit;
}, 0);


/* ================= 侧边栏分类目录（博客/论坛共用，$show_hot 控制是否显示全球行业热点） ================= */
function didi_ai_sidebar_categories($base_url, $active_slug = '', $title = '分类目录', $show_hot = true) {
  $html = '<h3 style="font-size:14px;color:#4e5969;margin-bottom:12px;">' . esc_html($title) . '</h3>';

  // 精选分类
  $featured = get_categories(array('taxonomy' => 'category', 'slug' => array('international', 'featured'), 'hide_empty' => true));
  foreach ($featured as $c) {
    $active = ($active_slug === $c->slug);
    $html .= '<a href="' . esc_url(add_query_arg(array('cat' => $c->slug), $base_url)) . '"'
      . ' style="display:flex;justify-content:space-between;align-items:center;padding:9px 12px;border-radius:8px;margin-bottom:4px;font-size:14px;color:' . ($active ? '#1668dc' : '#4e5969') . ';background:' . ($active ? '#eef4ff' : 'transparent') . ';font-weight:' . ($active ? '600' : '400') . ';text-decoration:none;">'
      . '<span>' . esc_html($c->name) . '</span>'
      . '<span style="font-size:12px;color:#86909c;">' . esc_html($c->count) . '</span>'
      . '</a>';
  }

  // 博客侧：显示热门标签（替代全球行业热点）
  if (!$show_hot) {
    $tags = get_terms(array('taxonomy' => 'post_tag', 'orderby' => 'count', 'order' => 'DESC', 'number' => 30, 'hide_empty' => true));
    if (!is_wp_error($tags) && !empty($tags)) {
      $html .= '<div style="font-size:12px;color:#86909c;padding:14px 12px 8px;border-top:1px solid #f2f3f5;margin-top:10px;">热门标签</div>';
      $html .= '<div style="padding:6px 12px;display:flex;flex-wrap:wrap;gap:6px;">';
      foreach ($tags as $t) {
        $html .= '<a href="' . esc_url(add_query_arg(array('tag' => $t->slug), $base_url)) . '"'
          . ' style="font-size:12px;color:#4e5969;background:#f2f3f5;padding:4px 10px;border-radius:20px;text-decoration:none;">'
          . esc_html($t->name) . ' <span style="color:#86909c;">' . $t->count . '</span></a>';
      }
      $html .= '</div>';
    }
    return $html;
  }

  // 全球行业热点（父分类，仅论坛）
  $parent = get_term_by('slug', 'national-hot', 'category');
  if ($parent) {
    $html .= '<div style="font-size:12px;color:#86909c;padding:14px 12px 8px;border-top:1px solid #f2f3f5;margin-top:10px;">全球行业热点</div>';

    $per_page = 20;
    $cpage = isset($_GET['cpage']) ? max(1, intval($_GET['cpage'])) : 1;
    $kids = get_terms(array('taxonomy' => 'category', 'child_of' => $parent->term_id, 'hide_empty' => false));
    $kids = is_wp_error($kids) ? array() : $kids;
    usort($kids, function($a, $b) { return $b->count - $a->count; });

    $total = count($kids);
    $pages = max(1, (int) ceil($total / $per_page));
    $cpage = min($cpage, $pages);
    $slice = array_slice($kids, ($cpage - 1) * $per_page, $per_page);

    foreach ($slice as $c) {
      $active = ($active_slug === $c->slug);
      $html .= '<a href="' . esc_url(add_query_arg(array('cat' => $c->slug), $base_url)) . '"'
        . ' style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:8px;margin-bottom:2px;font-size:13px;color:' . ($active ? '#1668dc' : '#4e5969') . ';background:' . ($active ? '#eef4ff' : 'transparent') . ';font-weight:' . ($active ? '600' : '400') . ';text-decoration:none;">'
        . '<span>' . esc_html($c->name) . '</span>'
        . '<span style="font-size:12px;color:#86909c;">' . esc_html($c->count) . '</span>'
        . '</a>';
    }

    if ($pages > 1) {
      $html .= '<div style="display:flex;justify-content:center;gap:4px;padding-top:10px;">';
      for ($p = 1; $p <= $pages; $p++) {
        if ($p === $cpage) {
          $html .= '<span style="font-size:12px;color:#1668dc;padding:2px 6px;">' . $p . '</span>';
        } else {
          $html .= '<a href="' . esc_url(add_query_arg(array('cpage' => $p), $base_url)) . '" style="font-size:12px;color:#86909c;padding:2px 6px;text-decoration:none;">' . $p . '</a>';
        }
      }
      $html .= '</div>';
    }
  }

  return $html;
}

/* ================= 配额中心（点数余额 + 会员档位 + 定价表） ================= */

// 定价表：各功能/模型的点数单价
function didi_ai_pricing() {
  return array(
    // 对话 / 代码 / 工作 通用 LLM（计费主键：chat）
    'chat'          => array('name' => '提问',               'unit' => '点数/千token', 'price' => 1, 'model' => 'Kimi K3'),
    'code'          => array('name' => '代码',               'unit' => '点数/千token', 'price' => 1, 'model' => 'Qwen3.8-Max'),
    'work'          => array('name' => '工作',               'unit' => '点数/千token', 'price' => 1, 'model' => 'Qwen3.8-Max'),
    // 国内对话模型
    'llm_ds'        => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.5, 'model' => 'DeepSeek'),
    'llm_qwen'      => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.6, 'model' => '通义千问'),
    'llm_kimi'      => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.6, 'model' => 'Kimi'),
    'llm_glm'       => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.7, 'model' => '智谱 GLM'),
    'llm_doubao'    => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.7, 'model' => '豆包'),
    'llm_hunyuan'   => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.7, 'model' => '腾讯混元'),
    'llm_ernie'     => array('name' => '提问 · 国内',        'unit' => '点数/次',      'price' => 0.7, 'model' => '文心 ERNIE'),
    // 海外对话模型
    'llm_gpt4omini' => array('name' => '提问 · 海外',        'unit' => '点数/次',      'price' => 1,   'model' => 'GPT-4o mini'),
    'llm_gpt4o'     => array('name' => '提问 · 海外',        'unit' => '点数/次',      'price' => 3,   'model' => 'GPT-4o'),
    'llm_gemini'    => array('name' => '提问 · 海外',        'unit' => '点数/次',      'price' => 3,   'model' => 'Gemini 1.5 Pro'),
    'llm_claude'    => array('name' => '提问 · 海外',        'unit' => '点数/次',      'price' => 4,   'model' => 'Claude 3.5 Sonnet'),
    // 图片
    'image'         => array('name' => '图片',               'unit' => '点数/张',      'price' => 5,   'model' => '即梦 / OpenAI'),
    'image_ms'      => array('name' => '图片 · 基础',        'unit' => '点数/张',      'price' => 2,   'model' => 'ModelScope'),
    'image_jimeng'  => array('name' => '图片 · 标准',        'unit' => '点数/张',      'price' => 20,  'model' => '即梦 Jimeng'),
    'image_gemini'  => array('name' => '图片 · 高端',        'unit' => '点数/张',      'price' => 50,  'model' => 'Gemini Banna'),
    'image_openai'  => array('name' => '图片 · 高端',        'unit' => '点数/张',      'price' => 60,  'model' => 'OpenAI'),
    // 视频
    'video'         => array('name' => '视频 · 国内低价档',  'unit' => '点数/条',      'price' => 30,  'model' => '可灵 1.6 / SEEDANCE / 即梦'),
    'video_intl'    => array('name' => '视频 · 国际高端档',  'unit' => '点数/条',      'price' => 80,  'model' => 'Runway Gen-3'),
    'video_ms'      => array('name' => '视频 · 经济',        'unit' => '点数/条',      'price' => 5,   'model' => 'ModelScope'),
    'video_openai'  => array('name' => '视频 · 高端',        'unit' => '点数/条',      'price' => 80,  'model' => 'OpenAI'),
    'video_runway'  => array('name' => '视频 · 国际高端',    'unit' => '点数/条',      'price' => 80,  'model' => 'Runway Gen-3'),
    // 动画
    'animate'       => array('name' => '动漫',               'unit' => '点数/条',      'price' => 30,  'model' => 'AnimateDiff'),
    'animate_lite'  => array('name' => '动漫 · 经济',        'unit' => '点数/条',      'price' => 20,  'model' => 'AnimateDiff-Lite'),
    // 剪辑
    'edit'          => array('name' => '剪辑（默认）',       'unit' => '点数/次',      'price' => 25,  'model' => '可灵 / 即梦 / Runway'),
    'edit_kling'    => array('name' => '剪辑 · 可灵',        'unit' => '点数/次',      'price' => 30,  'model' => '可灵 Kling'),
    'edit_jimeng'   => array('name' => '剪辑 · 即梦',        'unit' => '点数/次',      'price' => 30,  'model' => '即梦 Jimeng'),
    'edit_openai'   => array('name' => '剪辑 · OpenAI',      'unit' => '点数/次',      'price' => 80,  'model' => 'OpenAI / Runway'),
    'edit_vps'      => array('name' => '剪辑 · 图片/音频',   'unit' => '点数/次',      'price' => 10,  'model' => 'VPS 图片 / 音频 / 轻剪辑'),
    // 数字人
    'avatar_huoshan'=> array('name' => '数字人 · 基础',      'unit' => '点数/条',      'price' => 20,  'model' => '火山引擎（即梦）'),
    'avatar_tencent'=> array('name' => '数字人 · 基础',      'unit' => '点数/条',      'price' => 25,  'model' => '腾讯智影'),
    'avatar_sheng'  => array('name' => '数字人 · 标准',      'unit' => '点数/条',      'price' => 30,  'model' => '晟诺科讯达'),
    'avatar_ifly'   => array('name' => '数字人 · 标准',      'unit' => '点数/条',      'price' => 35,  'model' => '讯飞虚拟人'),
    'avatar_guiji'  => array('name' => '数字人 · 高端',      'unit' => '点数/条',      'price' => 40,  'model' => '硅基智能'),
    'avatar_baidu'  => array('name' => '数字人 · 高端',      'unit' => '点数/条',      'price' => 45,  'model' => '百度曦灵'),
    'avatar_did'    => array('name' => '数字人 · 海外',      'unit' => '点数/条',      'price' => 50,  'model' => 'D-ID'),
    'avatar_heygen' => array('name' => '数字人 · 海外',      'unit' => '点数/条',      'price' => 60,  'model' => 'HeyGen'),
    'avatar_synth'  => array('name' => '数字人 · 海外',      'unit' => '点数/条',      'price' => 70,  'model' => 'Synthesia'),
  );
}

// 会员档位定义：全站统一三级（免费会员 / 月卡会员 / 年卡会员），最低档免费注册即用
function didi_ai_memberships() {
  return array(
    'free'  => array('name' => '免费会员', 'color' => '#14b8a6', 'monthly' => 0,   'daily' => array('chat' => 50, 'code' => 50, 'work' => 50, 'image' => 5, 'video' => 1, 'video_intl' => 0, 'animate' => 1, 'edit' => 3)),
    'month' => array('name' => '月卡会员', 'color' => '#1668dc', 'monthly' => 3000, 'daily' => array('chat' => 200, 'code' => 200, 'work' => 200, 'image' => 50, 'video' => 10, 'video_intl' => 3, 'animate' => 10, 'edit' => 30)),
    'year'  => array('name' => '年卡会员', 'color' => '#f7ba1e', 'monthly' => 10000,'daily' => array('chat' => 1000, 'code' => 1000, 'work' => 1000, 'image' => 200, 'video' => 50, 'video_intl' => 15, 'animate' => 50, 'edit' => 100)),
  );
}

// 合法会员档位
function didi_ai_membership_levels() {
  return array('free', 'month', 'year');
}

// 档位归一化：兼容旧档位（trial→free、silver→month、gold→year），非法值一律 free
function didi_ai_normalize_level($level) {
  $map = array('trial' => 'free', 'silver' => 'month', 'gold' => 'year');
  if (isset($map[$level])) return $map[$level];
  return in_array($level, array('free', 'month', 'year'), true) ? $level : 'free';
}

// 当前用户会员等级与余额
function didi_ai_user_quota() {
  if (!is_user_logged_in()) {
    return array('logged_in' => false, 'membership' => 'free', 'membership_name' => '免费会员', 'balance' => 0, 'daily_used' => array(), 'daily_limit' => array());
  }
  $uid = get_current_user_id();
  if (!get_user_meta($uid, 'didi_balance', true) && !get_user_meta($uid, 'didi_membership', true)) {
    didi_ai_init_quota($uid);
  }
  $level = didi_ai_normalize_level(get_user_meta($uid, 'didi_membership', true));
  $ms = didi_ai_memberships();
  $balance = (float) get_user_meta($uid, 'didi_balance', true);
  $date = gmdate('Y-m-d');
  $daily = get_user_meta($uid, 'didi_daily_' . $date, true);
  $daily = is_array($daily) ? $daily : array();
  return array(
    'logged_in' => true,
    'membership' => $level,
    'membership_name' => $ms[$level]['name'],
    'membership_color' => $ms[$level]['color'],
    'balance' => $balance,
    'daily_used' => $daily,
    'daily_limit' => $ms[$level]['daily'],
  );
}

// 新用户初始化（注册时赠送初始点数）
function didi_ai_init_quota($user_id) {
  if (!get_user_meta($user_id, 'didi_balance', true)) {
    update_user_meta($user_id, 'didi_balance', 100);
  }
  if (!get_user_meta($user_id, 'didi_membership', true)) {
    update_user_meta($user_id, 'didi_membership', 'free');
  }
}
add_action('user_register', 'didi_ai_init_quota');

// 扣费检查：功能 => (ok, error, cost)
function didi_ai_charge($feature, $note = '') {
  if (!is_user_logged_in()) {
    return array('ok' => false, 'error' => '请先登录', 'cost' => 0);
  }
  $uid = get_current_user_id();
  $pricing = didi_ai_pricing();
  if (!isset($pricing[$feature])) $feature = 'chat';
  $cost = (float) $pricing[$feature]['price'];

  // 免费用户：视频/动漫不开放
  $level = didi_ai_normalize_level(get_user_meta($uid, 'didi_membership', true));
  $ms = didi_ai_memberships();
  $dailyLimit = $ms[$level]['daily'];

  // 每日额度
  $date = gmdate('Y-m-d');
  $daily = get_user_meta($uid, 'didi_daily_' . $date, true);
  $daily = is_array($daily) ? $daily : array();
  $dailyKey = (strpos($feature, 'edit_') === 0) ? 'edit' : $feature;
  $used = isset($daily[$dailyKey]) ? (int) $daily[$dailyKey] : 0;
  // 会员（月卡/年卡）不受每日额度限制，按点数余额自由使用
  if ($level === 'free') {
    if ($dailyLimit[$dailyKey] > 0 && $used >= $dailyLimit[$dailyKey]) {
      return array('ok' => false, 'error' => '今日额度已用完，请明日再试或升级会员', 'cost' => $cost);
    }
    if ($dailyLimit[$dailyKey] === 0) {
      return array('ok' => false, 'error' => '当前会员等级暂不支持此功能，请升级会员', 'cost' => $cost);
    }
  }

  // 余额检查
  $balance = (float) get_user_meta($uid, 'didi_balance', true);
  if ($balance < $cost) {
    return array('ok' => false, 'error' => '点数余额不足，请充值（当前余额 ' . $balance . ' 点，本次需要 ' . $cost . ' 点）', 'cost' => $cost);
  }

  // 扣费
  update_user_meta($uid, 'didi_balance', $balance - $cost);
  $daily[$dailyKey] = $used + 1;
  update_user_meta($uid, 'didi_daily_' . $date, $daily);
  didi_ai_record_usage($feature, $note);
  didi_ai_billing_log('spend', $feature, $cost, $balance - $cost, $note);

  return array('ok' => true, 'error' => '', 'cost' => $cost, 'balance' => $balance - $cost);
}

// 失败退款：功能调用失败时退回已扣点数并回滚每日次数（配合 didi_ai_charge 使用）
function didi_ai_refund($feature) {
  if (!is_user_logged_in()) return;
  $uid = get_current_user_id();
  $pricing = didi_ai_pricing();
  if (!isset($pricing[$feature])) $feature = 'chat';
  $cost = (float) $pricing[$feature]['price'];
  $balance = (float) get_user_meta($uid, 'didi_balance', true);
  $balance += $cost;
  update_user_meta($uid, 'didi_balance', $balance);
  $date = gmdate('Y-m-d');
  $dailyKey = (strpos($feature, 'edit_') === 0) ? 'edit' : $feature;
  $daily = get_user_meta($uid, 'didi_daily_' . $date, true);
  if (is_array($daily) && isset($daily[$dailyKey]) && $daily[$dailyKey] > 0) {
    $daily[$dailyKey] = (int) $daily[$dailyKey] - 1;
    update_user_meta($uid, 'didi_daily_' . $date, $daily);
  }
}

// 对话类预扣：单次请求先扣 hold 点数，流结束按实际 token 结算退款
function didi_ai_charge_hold($feature, $hold = 10) {
  if (!is_user_logged_in()) {
    return array('ok' => false, 'error' => '请先登录', 'hold' => $hold);
  }
  $uid = get_current_user_id();
  $pricing = didi_ai_pricing();
  if (!isset($pricing[$feature])) $feature = 'chat';

  // 每日额度
  $level = didi_ai_normalize_level(get_user_meta($uid, 'didi_membership', true));
  $ms = didi_ai_memberships();
  $dailyLimit = $ms[$level]['daily'];
  $date = gmdate('Y-m-d');
  $daily = get_user_meta($uid, 'didi_daily_' . $date, true);
  $daily = is_array($daily) ? $daily : array();
  $used = isset($daily[$feature]) ? (int) $daily[$feature] : 0;
  // 会员（月卡/年卡）不受每日额度限制，按点数余额自由使用
  if ($level === 'free') {
    if ($dailyLimit[$feature] > 0 && $used >= $dailyLimit[$feature]) {
      return array('ok' => false, 'error' => '今日额度已用完，请明日再试或升级会员', 'hold' => $hold);
    }
    if ($dailyLimit[$feature] === 0) {
      return array('ok' => false, 'error' => '当前会员等级暂不支持此功能，请升级会员', 'hold' => $hold);
    }
  }

  $balance = (float) get_user_meta($uid, 'didi_balance', true);
  if ($balance < $hold) {
    return array('ok' => false, 'error' => '点数余额不足，请充值（当前余额 ' . $balance . ' 点，单次对话至少需 ' . $hold . ' 点）', 'hold' => $hold);
  }
  update_user_meta($uid, 'didi_balance', $balance - $hold);
  $daily[$feature] = $used + 1;
  update_user_meta($uid, 'didi_daily_' . $date, $daily);
  return array('ok' => true, 'error' => '', 'hold' => $hold, 'balance' => $balance - $hold);
}

// 对话结算：按 total_tokens 计算实际费用（每千token price 点），退回差额
// $answered=false 表示未产生回答（请求失败/无内容），全额退回 hold，不扣点
function didi_ai_settle_chat($feature, $total_tokens, $hold = 10, $answered = true) {
  if (!is_user_logged_in()) {
    return array('tokens' => $total_tokens, 'cost' => 0, 'refund' => 0, 'balance' => 0);
  }
  $uid = get_current_user_id();
  $balance = (float) get_user_meta($uid, 'didi_balance', true);
  if (!$answered) {
    $balance += $hold;
    update_user_meta($uid, 'didi_balance', $balance);
    // 回滚每日使用次数（未回答不占用今日额度）
    $date = gmdate('Y-m-d');
    $daily = get_user_meta($uid, 'didi_daily_' . $date, true);
    if (is_array($daily) && isset($daily[$feature]) && $daily[$feature] > 0) {
      $daily[$feature] = (int) $daily[$feature] - 1;
      update_user_meta($uid, 'didi_daily_' . $date, $daily);
    }
    return array('tokens' => 0, 'cost' => 0, 'refund' => $hold, 'balance' => $balance);
  }
  $pricing = didi_ai_pricing();
  if (!isset($pricing[$feature])) $feature = 'chat';
  $price = (float) $pricing[$feature]['price']; // 每千 token
  $actual = (int) ceil(max($total_tokens, 0) / 1000) * $price;
  if ($actual < 1) $actual = 1;
  if ($actual > $hold) $actual = $hold; // 单次封顶 hold
  $refund = $hold - $actual;
  didi_ai_billing_log('spend', $feature, $actual, $balance, '对话结算（' . $total_tokens . ' tokens）');
  if ($refund > 0) {
    $balance += $refund;
    update_user_meta($uid, 'didi_balance', $balance);
    didi_ai_billing_log('refund', $feature, $refund, $balance, '结算退款（预扣 ' . $hold . '，实扣 ' . $actual . '）');
  }
  return array('tokens' => (int) $total_tokens, 'cost' => $actual, 'refund' => $refund, 'balance' => $balance);
}

// REST：查询我的配额
function didi_ai_quota_endpoint($req) {
  $q = didi_ai_user_quota();
  $q['pricing'] = didi_ai_pricing();
  return $q;
}

// REST：修改密码（wp_set_password 会使当前会话失效，前端提示重新登录）
function didi_ai_change_password_endpoint($req) {
  if (!is_user_logged_in()) {
    return new WP_REST_Response(array('ok' => false, 'message' => '请先登录'), 401);
  }
  $old = (string) $req->get_param('old_password');
  $new = (string) $req->get_param('new_password');
  if ($old === '' || $new === '') {
    return new WP_REST_Response(array('ok' => false, 'message' => '请填写当前密码与新密码'), 400);
  }
  if (strlen($new) < 6) {
    return new WP_REST_Response(array('ok' => false, 'message' => '新密码至少 6 位'), 400);
  }
  $uid = get_current_user_id();
  $user = get_userdata($uid);
  if (!$user || !wp_check_password($old, $user->user_pass, $uid)) {
    return new WP_REST_Response(array('ok' => false, 'message' => '当前密码不正确'), 400);
  }
  wp_set_password($new, $uid);
  didi_ai_billing_log('bonus', 'password', 0, (float) get_user_meta($uid, 'didi_balance', true), '修改登录密码');
  return array('ok' => true, 'message' => '密码修改成功，请重新登录');
}

// REST：消费记录
function didi_ai_billing_endpoint($req) {
  if (!is_user_logged_in()) {
    return new WP_REST_Response(array('ok' => false, 'message' => '请先登录'), 401);
  }
  $limit = (int) $req->get_param('limit');
  return array('ok' => true, 'items' => didi_ai_billing_items($limit));
}
// REST：充值（模拟，正式需接支付）
function didi_ai_recharge_endpoint($req) {
  if (!is_user_logged_in()) {
    return new WP_REST_Response(array('ok' => false, 'message' => '请先登录'), 401);
  }
  $method = sanitize_text_field($req->get_param('method') ?: '支付宝');
  $plan = sanitize_key($req->get_param('plan') ?: '');
  $uid = get_current_user_id();
  $balance = (float) get_user_meta($uid, 'didi_balance', true);
  $curLevel = didi_ai_normalize_level(get_user_meta($uid, 'didi_membership', true));

  // 月卡 / 年卡会员开通：原价充值（¥9.99 / ¥99.99），免费会员无需充值即可使用
  $membershipPlans = array(
    'month' => array('level' => 'month', 'amount' => 9.99, 'days' => 30,  'label' => '月卡'),
    'year'  => array('level' => 'year',  'amount' => 99.99, 'days' => 365, 'label' => '年卡'),
  );
  if (isset($membershipPlans[$plan])) {
    $mp = $membershipPlans[$plan];
    if ($curLevel === $mp['level']) {
      return new WP_REST_Response(array('ok' => false, 'message' => '您已是' . $mp['label'] . '会员，无需重复开通'), 400);
    }
    if ($curLevel !== 'free') {
      $curName = didi_ai_memberships()[$curLevel]['name'];
      return new WP_REST_Response(array('ok' => false, 'message' => '您当前是' . $curName . '，如需更换请先联系客服'), 400);
    }
    $points = (int) round($mp['amount'] * 100);
    update_user_meta($uid, 'didi_membership', $mp['level']);
    update_user_meta($uid, 'didi_membership_plan', $mp['level']);
    update_user_meta($uid, 'didi_membership_expire', date('Y-m-d H:i:s', time() + $mp['days'] * 86400));
    update_user_meta($uid, 'didi_balance', $balance + $points);
    didi_ai_billing_log('membership', $mp['level'], $points, $balance + $points, '开通' . $mp['label'] . '会员：¥' . number_format($mp['amount'], 2) . '（' . $mp['days'] . ' 天）');
    return array('ok' => true, 'membership' => $mp['level'], 'balance' => $balance + $points, 'added' => $points, 'method' => $method, 'note' => $mp['label'] . '会员开通成功：¥' . number_format($mp['amount'], 2) . '（' . $mp['days'] . ' 天）');
  }

  $amount = (float) $req->get_param('amount');
  if ($amount <= 0) {
    return new WP_REST_Response(array('ok' => false, 'message' => '金额无效'), 400);
  }
  $points = $amount * 100; // ¥1 = 100 点
  update_user_meta($uid, 'didi_balance', $balance + $points);
  didi_ai_billing_log('recharge', 'recharge', $points, $balance + $points, '充值 ¥' . $amount . '（' . $method . '）');
  return array('ok' => true, 'balance' => $balance + $points, 'added' => $points, 'method' => $method, 'note' => '当前为模拟充值，接入真实支付后金额将直接入账');
}

// REST：修改用户资料（昵称 / 邮箱）
function didi_ai_update_profile_endpoint($req) {
  if (!is_user_logged_in()) {
    return new WP_REST_Response(array('ok' => false, 'message' => '请先登录'), 401);
  }
  $uid = get_current_user_id();
  $nickname = sanitize_text_field($req->get_param('nickname'));
  $email = sanitize_email($req->get_param('email'));
  if ($nickname === '') {
    return new WP_REST_Response(array('ok' => false, 'message' => '昵称不能为空'), 400);
  }
  if (!is_email($email)) {
    return new WP_REST_Response(array('ok' => false, 'message' => '邮箱格式不正确'), 400);
  }
  $existing = email_exists($email);
  if ($existing && (int) $existing !== (int) $uid) {
    return new WP_REST_Response(array('ok' => false, 'message' => '该邮箱已被其他账号使用'), 400);
  }
  $data = array('ID' => $uid, 'display_name' => $nickname);
  if ($email !== wp_get_current_user()->user_email) {
    $data['user_email'] = $email;
  }
  $res = wp_update_user($data);
  if (is_wp_error($res)) {
    return new WP_REST_Response(array('ok' => false, 'message' => '保存失败：' . $res->get_error_message()), 400);
  }
  didi_ai_billing_log('bonus', 'profile', 0, (float) get_user_meta($uid, 'didi_balance', true), '修改用户资料');
  return array('ok' => true, 'message' => '资料修改成功');
}

// 图片上传端点：登录用户上传图片到媒体库，返回 URL
function didi_ai_upload_image_endpoint($req) {
  if (!is_user_logged_in()) {
    return new WP_REST_Response(array('ok' => false, 'message' => '请先登录'), 401);
  }
  $files = $req->get_file_params();
  if (empty($files['file'])) {
    return new WP_REST_Response(array('ok' => false, 'message' => '请选择图片文件'), 400);
  }
  if (isset($files['file']['error']) && (int) $files['file']['error'] === UPLOAD_ERR_INI_SIZE) {
    return new WP_REST_Response(array('ok' => false, 'message' => '图片过大：服务器单次上传上限约 ' . ini_get('upload_max_filesize') . '，请压缩图片后重试'), 400);
  }
  if (isset($files['file']['error']) && (int) $files['file']['error'] !== UPLOAD_ERR_OK) {
    return new WP_REST_Response(array('ok' => false, 'message' => '图片上传失败，请重新选择后再试'), 400);
  }
  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';
  require_once ABSPATH . 'wp-admin/includes/image.php';
  $attach_id = media_handle_upload('file', 0);
  if (is_wp_error($attach_id)) {
    return new WP_REST_Response(array('ok' => false, 'message' => '上传失败：' . $attach_id->get_error_message()), 400);
  }
  $url = wp_get_attachment_url($attach_id);
  return array('ok' => true, 'id' => $attach_id, 'url' => $url);
}

/* ================= 轻剪辑 · 自带 KEY 免费图生视频（阿里云百炼 DashScope wan-i2v） ================= */
function didi_ai_lite_encrypt($plain) {
  if (!$plain) return '';
  $key = wp_salt('auth');
  $iv = openssl_random_pseudo_bytes(16);
  $cipher = openssl_encrypt($plain, 'aes-256-cbc', $key, 0, $iv);
  return ($cipher === false) ? '' : base64_encode($iv . $cipher);
}
function didi_ai_lite_decrypt($stored) {
  if (!$stored) return '';
  $raw = base64_decode($stored);
  if ($raw === false || strlen($raw) < 17) return '';
  $iv = substr($raw, 0, 16);
  $cipher = substr($raw, 16);
  $plain = openssl_decrypt($cipher, 'aes-256-cbc', wp_salt('auth'), 0, $iv);
  return ($plain === false) ? '' : $plain;
}
function didi_ai_lite_save_key($key) {
  $uid = get_current_user_id();
  if (!$uid) return false;
  return update_user_meta($uid, 'didi_lite_dashscope_key', didi_ai_lite_encrypt($key));
}
function didi_ai_lite_get_key() {
  $uid = get_current_user_id();
  if (!$uid) return '';
  return didi_ai_lite_decrypt(get_user_meta($uid, 'didi_lite_dashscope_key', true));
}
function didi_ai_lite_get_json($url, $headers) {
  $ch = curl_init($url);
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_HTTPHEADER => array_merge(array('Content-Type: application/json'), $headers),
  ));
  $text = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
  if ($err) throw new Exception('网络请求失败: ' . $err);
  $data = json_decode($text, true);
  return array('status' => $status, 'data' => $data, 'raw' => $text);
}
function didi_ai_lite_generate($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $prompt = isset($params['prompt']) ? trim($params['prompt']) : '';
  $imageUrl = isset($params['imageUrl']) ? esc_url_raw(trim($params['imageUrl'])) : '';
  $apiKey = isset($params['apiKey']) ? trim($params['apiKey']) : '';
  if (!$prompt) return new WP_Error('empty_prompt', '请输入图片描述', array('status' => 400));
  if (!$imageUrl) return new WP_Error('empty_media', '请先上传图片素材', array('status' => 400));
  if (!$apiKey) $apiKey = didi_ai_lite_get_key();
  if (!$apiKey) return new WP_Error('no_key', '请填写你的阿里云百炼 API Key（免费体验，实名即送 50 秒）', array('status' => 400));
  didi_ai_lite_save_key($apiKey);
  $model = getenv('USER_LITE_MODEL') ?: 'wan2.5-i2v-preview';
  $base = rtrim(getenv('USER_LITE_BASE_URL') ?: 'https://dashscope.aliyuncs.com', '/');
  $url = $base . '/api/v1/services/aigc/video-generation/video-synthesis';
  $body = array(
    'model' => $model,
    'input' => array('prompt' => $prompt, 'img_url' => $imageUrl),
    'parameters' => array('resolution' => '480P', 'prompt_extend' => true),
  );
  try {
    $res = didi_ai_post_json($url, array('Authorization: Bearer ' . $apiKey, 'X-DashScope-Async: enable'), $body);
    $data = is_array($res['data']) ? $res['data'] : array();
    $taskId = isset($data['output']['task_id']) ? $data['output']['task_id'] : '';
    if (!$taskId && $res['status'] >= 400) {
      $msg = isset($data['message']) ? $data['message'] : (isset($data['output']['message']) ? $data['output']['message'] : '任务提交失败');
      return new WP_Error('dashscope_error', $msg, array('status' => $res['status'] ? $res['status'] : 500));
    }
    return array('ok' => true, 'taskId' => $taskId, 'raw' => $data);
  } catch (Exception $e) {
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  }
}
function didi_ai_lite_status($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $taskId = isset($params['taskId']) ? sanitize_text_field($params['taskId']) : '';
  if (!$taskId) return new WP_Error('empty_task', '缺少任务 ID', array('status' => 400));
  $apiKey = didi_ai_lite_get_key();
  if (!$apiKey) return new WP_Error('no_key', '未找到 API Key', array('status' => 400));
  $base = rtrim(getenv('USER_LITE_BASE_URL') ?: 'https://dashscope.aliyuncs.com', '/');
  try {
    $res = didi_ai_lite_get_json($base . '/api/v1/tasks/' . rawurlencode($taskId), array('Authorization: Bearer ' . $apiKey));
    $output = isset($res['data']['output']) ? $res['data']['output'] : array();
    return array('ok' => true, 'status' => isset($output['task_status']) ? $output['task_status'] : 'RUNNING', 'url' => isset($output['video_url']) ? $output['video_url'] : '', 'raw' => $res['data']);
  } catch (Exception $e) {
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  }
}

function didi_ai_vps_proxy($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $feature = isset($params['feature']) ? sanitize_key($params['feature']) : 'image';
  $feature = in_array($feature, array('image', 'audio', 'lite')) ? $feature : 'image';
  $action = isset($params['action']) ? sanitize_key($params['action']) : 'generate';
  $path = isset($params['path']) ? trim($params['path']) : '';
  if (!$path) return new WP_Error('empty_path', '缺少 VPS 接口路径', array('status' => 400));
  $base = rtrim(getenv('USER_VPS_BASE_URL') ?: '', '/');
  if (!$base) {
    return new WP_Error('not_configured', 'VPS 剪辑服务未配置：请在服务端环境变量中填写 USER_VPS_BASE_URL 指向你的剪辑服务地址', array('status' => 503));
  }
  $payload = isset($params['data']) ? $params['data'] : array();
  if (!is_array($payload)) $payload = array();
  $url = $base . '/' . ltrim($path, '/');
  $headers = array();
  $vpsKey = getenv('USER_VPS_API_KEY') ?: '';
  if ($vpsKey) $headers[] = 'Authorization: Bearer ' . $vpsKey;
  try {
    $res = didi_ai_post_json($url, $headers, $payload);
    return array('ok' => true, 'status' => $res['status'], 'data' => $res['data'], 'raw' => $res['raw']);
  } catch (Exception $e) {
    return new WP_Error('vps_error', $e->getMessage(), array('status' => 502));
  }
}

function didi_ai_register_quota_routes() {
  register_rest_route('didi/v1', '/quota', array(
    'methods' => 'GET',
    'callback' => 'didi_ai_quota_endpoint',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/recharge', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_recharge_endpoint',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/upload', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_upload_image_endpoint',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/lite', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_lite_generate',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/lite/status', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_lite_status',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/vps', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_vps_proxy',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/change-password', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_change_password_endpoint',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/update-profile', array(
    'methods' => 'POST',
    'callback' => 'didi_ai_update_profile_endpoint',
    'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/billing', array(
    'methods' => 'GET',
    'callback' => 'didi_ai_billing_endpoint',
    'permission_callback' => '__return_true',
  ));
}
add_action('rest_api_init', 'didi_ai_register_quota_routes');

/* 登录成功后跳转：/login 前台会员登录 → 前台首页；wp-login.php 后台入口登录 → 停留后台 */
add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
  if (is_wp_error($user) || !($user instanceof WP_User)) {
    return $redirect_to;
  }
  $referer = wp_get_referer();
  if ($referer && strpos($referer, '/login') !== false && strpos($referer, 'wp-login') === false) {
    return home_url('/');
  }
  return $redirect_to;
}, 10, 3);

/* ================= CODE 项目工作台（创建项目 / 多轮任务 / 自动写代码） =================
 * 存储：wp-content/uploads/didi-projects/{uid}/{slug}/
 *   - meta.json   项目元信息
 *   - history.json 多轮任务对话历史
 *   - *.code      模型生成的文件（内容为文本）
 * 交互：用户建项目 → 输入需求 → 模型基于现有文件续写/生成多文件 → 页面展示文件树与代码
 */
function didi_codex_root() {
  $up = wp_upload_dir();
  return trailingslashit($up['basedir']) . 'didi-projects';
}
// 在根目录放置访问保护，禁止 Web 直接读取项目文件
function didi_codex_harden_root() {
  $root = didi_codex_root();
  if (!is_dir($root)) wp_mkdir_p($root);
  if (!file_exists($root . '/index.html')) @file_put_contents($root . '/index.html', '');
  if (!file_exists($root . '/.htaccess')) @file_put_contents($root . '/.htaccess', "Require all denied\n");
}
function didi_codex_user_dir($uid = 0) {
  $uid = (int) ($uid ? $uid : get_current_user_id());
  if ($uid <= 0) return '';
  return didi_codex_root() . '/' . $uid;
}
function didi_codex_project_dir($slug, $uid = 0) {
  $slug = sanitize_file_name($slug);
  if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $slug)) return '';
  $dir = didi_codex_user_dir($uid);
  if (!$dir) return '';
  return $dir . '/' . $slug;
}
function didi_codex_new_slug() {
  return 'p-' . bin2hex(random_bytes(5));
}
function didi_codex_safe_relpath($raw) {
  $raw = (string) $raw;
  $raw = str_replace('\\', '/', $raw);
  $raw = trim($raw, '/');
  if ($raw === '') return '';
  $parts = explode('/', $raw);
  foreach ($parts as $seg) {
    if ($seg === '' || $seg === '.' || $seg === '..') return '';
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $seg)) return '';
  }
  return implode('/', $parts);
}
// 列出某项目目录下的代码文件（排除内部 json）
function didi_codex_files($dir) {
  if (!is_dir($dir)) return array();
  $out = array();
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $name = $f->getFilename();
    if ($name === 'meta.json' || $name === 'history.json') continue;
    $path = $f->getPathname();
    $rel = ltrim(str_replace($dir, '', $path), '/');
    $out[] = array('path' => $rel, 'size' => (int) $f->getSize(), 'mtime' => $f->getMTime());
  }
  usort($out, function ($a, $b) { return strcmp($a['path'], $b['path']); });
  return $out;
}
function didi_codex_read_json($file, $default = array()) {
  if (!file_exists($file)) return $default;
  $data = json_decode((string) file_get_contents($file), true);
  return is_array($data) ? $data : $default;
}
function didi_codex_write_json($file, $data) {
  if (!@file_put_contents($file, wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) return false;
  return true;
}

// 创建项目
function didi_codex_create_project($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
  $desc = isset($params['desc']) ? sanitize_textarea_field($params['desc']) : '';
  if ($name === '') return new WP_Error('empty_name', '请填写项目名称', array('status' => 400));
  didi_codex_harden_root();
  $uid = get_current_user_id();
  $udir = didi_codex_user_dir($uid);
  if ($udir && !is_dir($udir)) wp_mkdir_p($udir);
  $slug = didi_codex_new_slug();
  $dir = didi_codex_project_dir($slug, $uid);
  wp_mkdir_p($dir);
  $now = current_time('mysql');
  didi_codex_write_json($dir . '/meta.json', array(
    'slug' => $slug, 'name' => $name, 'desc' => $desc, 'created' => $now, 'updated' => $now,
  ));
  didi_codex_write_json($dir . '/history.json', array());
  didi_ai_record_usage('code', '[' . $slug . '] 新建项目：' . didi_ai_note_short($name));
  return didi_codex_project_detail($slug, $uid);
}

// 项目列表
function didi_codex_list_projects($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $udir = didi_codex_user_dir();
  if (!$udir || !is_dir($udir)) return array('projects' => array());
  $out = array();
  foreach (scandir($udir) as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $m = didi_codex_read_json($udir . '/' . $entry . '/meta.json');
    if (empty($m['name'])) continue;
    $files = didi_codex_files($udir . '/' . $entry);
    $out[] = array(
      'slug' => $entry,
      'name' => isset($m['name']) ? $m['name'] : $entry,
      'desc' => isset($m['desc']) ? $m['desc'] : '',
      'created' => isset($m['created']) ? $m['created'] : '',
      'updated' => isset($m['updated']) ? $m['updated'] : '',
      'files' => count($files),
    );
  }
  usort($out, function ($a, $b) { return strcmp($b['updated'], $a['updated']); });
  return array('projects' => $out);
}

// 项目详情（含文件树与任务历史）
function didi_codex_project_detail($slug, $uid = 0) {
  if (!$uid) $uid = get_current_user_id();
  $dir = didi_codex_project_dir($slug, $uid);
  if (!$dir || !is_dir($dir)) return new WP_Error('not_found', '项目不存在', array('status' => 404));
  $meta = didi_codex_read_json($dir . '/meta.json');
  $history = didi_codex_read_json($dir . '/history.json');
  $meta['slug'] = $slug;
  $meta['files'] = didi_codex_files($dir);
  $meta['history'] = $history;
  return $meta;
}
function didi_codex_get_project($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $slug = sanitize_text_field($req->get_param('slug'));
  return didi_codex_project_detail($slug);
}

// 读取文件内容
function didi_codex_read_file_endpoint($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $slug = sanitize_text_field($req->get_param('slug'));
  $rel = didi_codex_safe_relpath($req->get_param('path'));
  if ($rel === '') return new WP_Error('bad_path', '无效文件路径', array('status' => 400));
  $dir = didi_codex_project_dir($slug);
  if (!$dir || !is_dir($dir)) return new WP_Error('not_found', '项目不存在', array('status' => 404));
  $file = $dir . '/' . $rel;
  if (!file_exists($file)) return new WP_Error('not_found', '文件不存在', array('status' => 404));
  $content = (string) file_get_contents($file);
  $mime = wp_check_filetype($rel);
  return array('ok' => true, 'path' => $rel, 'content' => $content, 'language' => didi_codex_lang($rel));
}

// 由文件名推断语言（供编辑器配色）
function didi_codex_lang($path) {
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  $map = array('py' => 'python', 'js' => 'javascript', 'ts' => 'typescript', 'html' => 'html', 'htm' => 'html', 'css' => 'css', 'php' => 'php', 'java' => 'java', 'c' => 'c', 'h' => 'c', 'cpp' => 'cpp', 'hpp' => 'cpp', 'go' => 'go', 'rs' => 'rust', 'rb' => 'ruby', 'sql' => 'sql', 'json' => 'json', 'xml' => 'xml', 'yml' => 'yaml', 'yaml' => 'yaml', 'md' => 'markdown', 'sh' => 'shell', 'bash' => 'shell', 'vue' => 'vue', 'jsx' => 'jsx', 'tsx' => 'tsx', 'txt' => 'plaintext', 'log' => 'plaintext', 'csv' => 'plaintext');
  return isset($map[$ext]) ? $map[$ext] : 'plaintext';
}

// 保存单个文件（用户在编辑器里修改后保存）
function didi_codex_save_file_endpoint($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $slug = sanitize_text_field(isset($params['slug']) ? $params['slug'] : '');
  $rel = didi_codex_safe_relpath(isset($params['path']) ? $params['path'] : '');
  $content = isset($params['content']) ? (string) $params['content'] : '';
  if ($rel === '') return new WP_Error('bad_path', '无效文件路径', array('status' => 400));
  $dir = didi_codex_project_dir($slug);
  if (!$dir || !is_dir($dir)) return new WP_Error('not_found', '项目不存在', array('status' => 404));
  if (strlen($content) > 300000) return new WP_Error('too_large', '文件过大', array('status' => 400));
  $parts = explode('/', $rel);
  $sub = $dir;
  for ($i = 0; $i < count($parts) - 1; $i++) {
    $sub .= '/' . $parts[$i];
    if (!is_dir($sub)) wp_mkdir_p($sub);
  }
  if (!@file_put_contents($dir . '/' . $rel, $content)) {
    return new WP_Error('write_failed', '文件写入失败', array('status' => 500));
  }
  $meta = didi_codex_read_json($dir . '/meta.json');
  $meta['updated'] = current_time('mysql');
  didi_codex_write_json($dir . '/meta.json', $meta);
  return array('ok' => true);
}

// 执行一次任务：读取现有文件作为上下文，调用模型生成/续写多文件
function didi_codex_run_task($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $slug = sanitize_text_field(isset($params['slug']) ? $params['slug'] : '');
  $message = isset($params['message']) ? trim((string) $params['message']) : '';
  $dir = didi_codex_project_dir($slug);
  if (!$dir || !is_dir($dir)) return new WP_Error('not_found', '项目不存在', array('status' => 404));
  if ($message === '') return new WP_Error('empty_message', '请输入任务描述', array('status' => 400));

  $conf = didi_ai_llm_cfg('code');
  if (empty($conf['apiKey'])) {
    return new WP_Error('not_configured', '大模型接口未配置，请在服务端环境变量中填写 USER_LLM_* 系列配置', array('status' => 503));
  }
  $feature = 'code';
  $hold = 60;
  $charge = didi_ai_charge_hold($feature, $hold);
  if (!$charge['ok']) return new WP_Error('no_quota', $charge['error'], array('status' => 402));
  didi_ai_record_usage('code', '[' . $slug . '] ' . didi_ai_note_short($message));

  $meta = didi_codex_read_json($dir . '/meta.json');
  $history = didi_codex_read_json($dir . '/history.json');

  // 拼装当前代码文件内容到上下文（最多约 8 个文件，单文件截断，避免超长）
  $files = didi_codex_files($dir);
  $codeBlock = '';
  $shown = 0;
  foreach ($files as $f) {
    if ($shown >= 8) { $codeBlock .= "\n（其余文件省略…）\n"; break; }
    $content = (string) @file_get_contents($dir . '/' . $f['path']);
    if (strlen($content) > 16000) $content = substr($content, 0, 16000) . "\n…(截断)";
    $codeBlock .= "\n===== 文件：" . $f['path'] . " =====\n" . $content . "\n";
    $shown++;
  }

  // 历史摘要（保留最近 6 轮，仅保留消息文本，避免把整段代码带入造成重复上下文）
  $recent = array_slice($history, -6);
  $historyText = '';
  foreach ($recent as $h) {
    $role = ($h['role'] === 'assistant') ? 'AI' : '用户';
    $content = isset($h['content']) ? (string) $h['content'] : '';
    if (strlen($content) > 600) $content = substr($content, 0, 600) . '…';
    $historyText .= "\n[" . $role . "] " . $content . "\n";
  }

  $system = "你是一个专业的多文件编码智能体，工作模式类似 CODEX：根据用户需求在项目里创建或修改多个代码文件。\n"
    . "项目名称：" . (isset($meta['name']) ? $meta['name'] : '') . "\n"
    . "项目简介：" . (isset($meta['desc']) ? $meta['desc'] : '') . "\n"
    . "项目当前已有文件：" . ($codeBlock !== '' ? $codeBlock : "（暂无文件，全新项目）") . "\n"
    . ($historyText !== '' ? "此前的对话摘要：" . $historyText . "\n" : '')
    . "要求：\n"
    . "1. 依据用户最新需求，确定需要新建或修改的文件，输出完成、可运行、无省略的完整文件内容。\n"
    . "2. 只输出一个 JSON 对象，不要包含任何额外文字或 Markdown 代码块围栏，结构如下：\n"
    . "{\n"
    . "  \"plan\": \"简述你将做什么\",\n"
    . "  \"summary\": \"给用户看的完成说明\",\n"
    . "  \"files\": [\n"
    . "    {\"path\": \"src/main.py\", \"content\": \"完整文件内容\"}\n"
    . "  ]\n"
    . "}\n"
    . "3. files 中的 path 为项目内相对路径（允许子目录，禁止使用绝对路径或 ../）。需要修改的已有文件给出其完整新内容；无需改动的文件不要列出。\n"
    . "4. 优先使用与项目简介匹配的成熟技术栈，代码要完整健壮。";

  $base = rtrim($conf['baseUrl'], '/');
  $body = array(
    'model' => isset($params['model']) && trim($params['model']) !== '' ? trim($params['model']) : $conf['model'],
    'messages' => array(
      array('role' => 'system', 'content' => $system),
      array('role' => 'user', 'content' => $message),
    ),
    'temperature' => 0.3,
    'stream' => false,
    'max_tokens' => 16000,
    'response_format' => array('type' => 'json_object'),
  );

  $answered = false;
  $tokens = 0;
  $result = array('plan' => '', 'summary' => '', 'files' => array());
  try {
    $res = didi_ai_post_json($base . '/chat/completions', array('Authorization: Bearer ' . $conf['apiKey']), $body);
    $data = is_array($res['data']) ? $res['data'] : array();
    if ($res['status'] >= 400) {
      $errMsg = isset($data['error']['message']) ? $data['error']['message'] : ('模型调用失败 (' . $res['status'] . ')');
      if (strpos($errMsg, 'response_format') !== false || $res['status'] === 400) {
        unset($body['response_format']);
        $body['max_tokens'] = 16000;
        $res = didi_ai_post_json($base . '/chat/completions', array('Authorization: Bearer ' . $conf['apiKey']), $body);
        $data = is_array($res['data']) ? $res['data'] : array();
      }
    }
    if ($res['status'] >= 400) {
      throw new Exception(isset($data['error']['message']) ? $data['error']['message'] : ('模型调用失败 (' . $res['status'] . ')'));
    }
    $content = isset($data['choices'][0]['message']['content']) ? (string) $data['choices'][0]['message']['content'] : '';
    $tokens = isset($data['usage']['total_tokens']) ? (int) $data['usage']['total_tokens'] : 0;
    if (trim($content) !== '') {
      $decoded = json_decode(trim($content), true);
      if (!is_array($decoded)) {
        // 兼容内容被代码块包裹
        if (preg_match('/\{[\s\S]*\}/', $content, $m)) $decoded = json_decode($m[0], true);
      }
      if (is_array($decoded)) {
        $result['plan'] = isset($decoded['plan']) ? (string) $decoded['plan'] : '';
        $result['summary'] = isset($decoded['summary']) ? (string) $decoded['summary'] : '';
        if (isset($decoded['files']) && is_array($decoded['files'])) $result['files'] = $decoded['files'];
        $answered = true;
      } else {
        $result['summary'] = trim($content);
        $answered = true;
      }
    }
  } catch (Exception $e) {
    didi_ai_settle_chat($feature, 0, $hold, false);
    return new WP_Error('ai_error', $e->getMessage(), array('status' => 500));
  }

  // 落盘生成的文件
  $written = array();
  foreach ($result['files'] as $f) {
    if (!is_array($f)) continue;
    $rel = didi_codex_safe_relpath(isset($f['path']) ? $f['path'] : '');
    $content = isset($f['content']) ? (string) $f['content'] : '';
    if ($rel === '' || $content === '') continue;
    if (strlen($content) > 300000) continue;
    $parts = explode('/', $rel);
    $sub = $dir;
    $okSub = true;
    for ($i = 0; $i < count($parts) - 1; $i++) {
      $sub .= '/' . $parts[$i];
      if (!is_dir($sub) && !wp_mkdir_p($sub)) { $okSub = false; break; }
    }
    if (!$okSub) continue;
    if (!@file_put_contents($dir . '/' . $rel, $content)) continue;
    $written[] = $rel;
  }

  // 记录历史
  $history[] = array('role' => 'user', 'content' => $message, 't' => time());
  $assistantNote = ($result['plan'] !== '' ? '计划：' . $result['plan'] . "\n" : '') . ($result['summary'] !== '' ? $result['summary'] : '已完成。');
  if ($written) $assistantNote .= "\n已生成/更新文件：" . implode('、', $written);
  $history[] = array('role' => 'assistant', 'content' => $assistantNote, 't' => time());
  if (count($history) > 200) $history = array_slice($history, -200);
  didi_codex_write_json($dir . '/history.json', $history);

  $meta['updated'] = current_time('mysql');
  didi_codex_write_json($dir . '/meta.json', $meta);

  $settle = didi_ai_settle_chat($feature, $tokens, $hold, $answered);
  return array(
    'ok' => true,
    'slug' => $slug,
    'plan' => $result['plan'],
    'summary' => $result['summary'],
    'written' => $written,
    'files' => didi_codex_files($dir),
    'history' => $history,
    'balance' => $settle['balance'],
    'cost' => $settle['cost'],
  );
}

// 删除整个项目（需用户确认；配合前端删除按钮使用）
function didi_codex_delete_project($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $slug = sanitize_text_field($req->get_param('slug'));
  $dir = didi_codex_project_dir($slug);
  if (!$dir || !is_dir($dir)) return new WP_Error('not_found', '项目不存在', array('status' => 404));
  // 只允许删除属于当前用户根目录下的项目目录
  $udir = didi_codex_user_dir();
  if (strpos($dir, rtrim($udir, '/') . '/') !== 0) return new WP_Error('forbidden', '无权删除该项目', array('status' => 403));
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($it as $f) {
    $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
  }
  @rmdir($dir);
  return array('ok' => true);
}

function didi_codex_register_routes() {
  register_rest_route('didi/v1', '/codex/projects', array(
    'methods' => 'GET', 'callback' => 'didi_codex_list_projects', 'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/codex/projects', array(
    'methods' => 'POST', 'callback' => 'didi_codex_create_project', 'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/codex/project', array(
    'methods' => 'GET', 'callback' => 'didi_codex_get_project', 'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/codex/project', array(
    'methods' => 'DELETE', 'callback' => 'didi_codex_delete_project', 'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/codex/task', array(
    'methods' => 'POST', 'callback' => 'didi_codex_run_task', 'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/codex/file', array(
    'methods' => 'GET', 'callback' => 'didi_codex_read_file_endpoint', 'permission_callback' => '__return_true',
  ));
  register_rest_route('didi/v1', '/codex/file', array(
    'methods' => 'POST', 'callback' => 'didi_codex_save_file_endpoint', 'permission_callback' => '__return_true',
  ));
}
add_action('rest_api_init', 'didi_codex_register_routes');
