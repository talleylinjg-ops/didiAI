<?php
/**
 * MediaCut 适配层
 *
 * MediaCut 非 OpenAI 兼容：Bearer 鉴权，任务提交 + 轮询 + 结果下载（结果下载同样需要 Bearer）。
 * 本文件把 MediaCut 的能力包装成与主题现有处理器一致的返回结构。
 *
 * 配置来源优先级：后台 didi_ai_config[mediacut] > 环境变量 USER_MEDIACUT_*
 */
if (!defined('ABSPATH')) exit;

/* ================= 配置 ================= */
function didi_ai_mediacut_cfg() {
  $env = array(
    'provider' => 'mediacut',
    'apiKey'   => getenv('USER_MEDIACUT_API_KEY') ?: '',
    'baseUrl'  => getenv('USER_MEDIACUT_BASE_URL') ?: '',
    'model'    => getenv('USER_MEDIACUT_MODEL') ?: '',
  );
  return didi_ai_cfg_merge('mediacut', $env);
}

// 是否为 MediaCut 渠道（兼容前端 didi-media / didi-mediacut 两种取值）
function didi_ai_mediacut_is_provider($provider) {
  $p = strtolower(trim((string) $provider));
  return in_array($p, array('mediacut', 'didi-media', 'didi-mediacut', 'didi_media'), true);
}

// 前端下拉的占位值（非真实上游模型 ID），命中时不作为 model 发送
function didi_ai_mediacut_is_placeholder_model($model) {
  $m = strtolower(trim((string) $model));
  return $m === '' || didi_ai_mediacut_is_provider($m) || $m === 'custom' || $m === '__custom__';
}

function didi_ai_mediacut_model($conf, $params = array()) {
  $model = isset($params['model']) ? trim((string) $params['model']) : '';
  if (!didi_ai_mediacut_is_placeholder_model($model)) return $model;
  if (!empty($conf['model'])) return $conf['model'];
  return 'Qwen/Qwen-Image';
}

/* ================= HTTP ================= */
// 统一请求：$fields 为 null 表示 GET；$multipart=true 时 $fields 可含 CURLFile
function didi_ai_mediacut_http($path, $fields = null, $multipart = false, $timeout = 120, $method = '') {
  $conf = didi_ai_mediacut_cfg();
  if (empty($conf['baseUrl']) || empty($conf['apiKey'])) {
    return new WP_Error('not_configured', 'didi Media 接口未配置，请在后台「didi AI 配置」中填写 mediacut 的 Base URL 与 API Key', array('status' => 503));
  }
  $base = rtrim($conf['baseUrl'], '/');
  $url = preg_match('#^https?://#i', (string) $path) ? $path : ($base . '/' . ltrim($path, '/'));
  $headersOut = array();
  $ch = curl_init($url);
  $opts = array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $conf['apiKey'], 'Accept: application/json, */*'),
    CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$headersOut) {
      $pos = strpos($header, ':');
      if ($pos !== false) {
        $headersOut[strtolower(trim(substr($header, 0, $pos)))] = trim(substr($header, $pos + 1));
      }
      return strlen($header);
    },
  );
  if ($fields === null) {
    if ($method) $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
  } else {
    $opts[CURLOPT_POST] = true;
    if ($method) $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
    if ($multipart) {
      $opts[CURLOPT_POSTFIELDS] = $fields; // PHP 自动设置 multipart 边界
    } else {
      $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
      $opts[CURLOPT_POSTFIELDS] = http_build_query($fields);
    }
  }
  curl_setopt_array($ch, $opts);
  $body = curl_exec($ch);
  $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  $ctype = isset($headersOut['content-type']) ? $headersOut['content-type'] : '';
  curl_close($ch);
  if ($err) return new WP_Error('mc_network', 'didi Media 网络请求失败：' . $err, array('status' => 502));
  $data = json_decode($body, true);
  return array('status' => $status, 'data' => is_array($data) ? $data : null, 'raw' => $body, 'headers' => $headersOut, 'content_type' => $ctype, 'binary' => $data === null);
}

// 提交异步任务，返回 task_id
function didi_ai_mediacut_submit($path, $fields, $multipart = false, $timeout = 120) {
  $res = didi_ai_mediacut_http($path, $fields, $multipart, $timeout);
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) {
    $msg = didi_ai_mediacut_error_message($res);
    return new WP_Error('mc_submit', $msg, array('status' => $res['status'] >= 500 ? 502 : $res['status']));
  }
  $data = is_array($res['data']) ? $res['data'] : array();
  $taskId = isset($data['task_id']) ? $data['task_id'] : (isset($data['taskId']) ? $data['taskId'] : (isset($data['id']) ? $data['id'] : ''));
  if (!$taskId) {
    return new WP_Error('mc_submit', 'didi Media 未返回任务 ID', array('status' => 502));
  }
  return array('task_id' => $taskId, 'status' => isset($data['status']) ? $data['status'] : 'pending', 'raw' => $data);
}

// 单次轮询任务状态
function didi_ai_mediacut_poll_once($taskId) {
  $res = didi_ai_mediacut_http('/api/v1/tasks/' . rawurlencode($taskId), null, false, 30, 'GET');
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) {
    return new WP_Error('mc_poll', didi_ai_mediacut_error_message($res), array('status' => 502));
  }
  $data = is_array($res['data']) ? $res['data'] : array();
  $status = strtolower(isset($data['status']) ? (string) $data['status'] : '');
  return array(
    'status' => $status,
    'result_url' => isset($data['result_url']) ? $data['result_url'] : '',
    'result_kind' => isset($data['result_kind']) ? $data['result_kind'] : '',
    'raw' => $data,
  );
}

// 轮询直到成功/失败/超时；超时返回带 pending 标记的 WP_Error
function didi_ai_mediacut_wait($taskId, $maxSeconds = 120, $interval = 2) {
  if (function_exists('set_time_limit')) @set_time_limit(max(30, (int) $maxSeconds + 30));
  $deadline = time() + max(1, (int) $maxSeconds);
  $last = array('status' => 'pending');
  while (time() < $deadline) {
    sleep($interval);
    $poll = didi_ai_mediacut_poll_once($taskId);
    if (is_wp_error($poll)) return $poll;
    $last = $poll;
    $st = $poll['status'];
    if (in_array($st, array('succeeded', 'success', 'completed', 'done'), true)) return $poll;
    if (in_array($st, array('failed', 'error', 'cancelled', 'canceled'), true)) {
      $msg = isset($poll['raw']['error']) ? $poll['raw']['error'] : (isset($poll['raw']['message']) ? $poll['raw']['message'] : '任务执行失败');
      return new WP_Error('mc_failed', 'didi Media 任务失败：' . $msg, array('status' => 502));
    }
  }
  return new WP_Error('mc_timeout', 'didi Media 任务处理中，请稍后查询结果', array('status' => 202, 'task_id' => $taskId, 'pending' => true, 'last' => $last));
}

function didi_ai_mediacut_error_message($res) {
  $data = is_array($res['data']) ? $res['data'] : array();
  if (isset($data['detail'])) {
    return is_string($data['detail']) ? $data['detail'] : wp_json_encode($data['detail']);
  }
  if (isset($data['message'])) return (string) $data['message'];
  if (isset($data['error'])) return is_string($data['error']) ? $data['error'] : wp_json_encode($data['error']);
  $raw = trim((string) $res['raw']);
  if ($raw !== '' && strlen($raw) < 200) return 'didi Media 返回：' . $raw;
  return 'didi Media 请求失败（HTTP ' . $res['status'] . '）';
}

/* ================= 结果落地 ================= */
function didi_ai_mediacut_ext_for($mime, $kind = '') {
  $mime = strtolower((string) $mime);
  $map = array(
    'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/webp' => 'webp',
    'image/gif' => 'gif', 'image/bmp' => 'bmp', 'image/tiff' => 'tiff',
    'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov',
    'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav',
    'audio/mp4' => 'm4a', 'audio/aac' => 'aac', 'audio/flac' => 'flac', 'audio/ogg' => 'ogg',
  );
  foreach ($map as $m => $ext) {
    if (strpos($mime, $m) === 0) return $ext;
  }
  if ($kind === 'video') return 'mp4';
  if ($kind === 'audio') return 'mp3';
  if ($kind === 'image') return 'png';
  return 'bin';
}

// 将二进制内容保存到 uploads/didi-mediacut，返回公开 URL
function didi_ai_mediacut_save_binary($binary, $mime, $kind = '') {
  if ($binary === false || $binary === null || $binary === '') {
    return new WP_Error('mc_empty', 'didi Media 未返回有效内容', array('status' => 502));
  }
  $up = wp_upload_dir();
  $dir = trailingslashit($up['basedir']) . 'didi-mediacut';
  if (!wp_mkdir_p($dir)) {
    return new WP_Error('mc_dir', '无法创建结果目录', array('status' => 500));
  }
  $ext = didi_ai_mediacut_ext_for($mime, $kind);
  $name = 'mc-' . gmdate('Ymd-His') . '-' . wp_generate_password(8, false, false) . '.' . $ext;
  $path = trailingslashit($dir) . $name;
  if (@file_put_contents($path, $binary) === false) {
    return new WP_Error('mc_write', '结果写入失败', array('status' => 500));
  }
  return array(
    'path' => $path,
    'url' => trailingslashit($up['baseurl']) . 'didi-mediacut/' . $name,
    'mime' => $mime,
  );
}

// 带 Bearer 下载结果并落地，返回公开 URL
function didi_ai_mediacut_download($resultUrl, $kind = '') {
  $path = preg_match('#^https?://#i', (string) $resultUrl) ? $resultUrl : ('/api/v1/result/' . ltrim($resultUrl, '/'));
  $res = didi_ai_mediacut_http($path, null, false, 180, 'GET');
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) {
    return new WP_Error('mc_download', didi_ai_mediacut_error_message($res), array('status' => 502));
  }
  return didi_ai_mediacut_save_binary($res['raw'], $res['content_type'], $kind);
}

// 从任务结果解析结果 URL + 类型，并下载落地
function didi_ai_mediacut_fetch_task_result($taskId, $kindHint = '') {
  $wait = didi_ai_mediacut_wait($taskId, 120, 2);
  if (is_wp_error($wait)) return $wait;
  if (empty($wait['result_url'])) {
    return new WP_Error('mc_no_result', 'didi Media 任务已完成但未返回结果地址', array('status' => 502));
  }
  $kind = $wait['result_kind'] ? $wait['result_kind'] : $kindHint;
  $saved = didi_ai_mediacut_download($wait['result_url'], $kind);
  if (is_wp_error($saved)) return $saved;
  return array('url' => $saved['url'], 'kind' => $kind ? $kind : 'image', 'task_id' => $taskId, 'raw' => $wait['raw']);
}

/* ================= 工具 ================= */
// 取素材临时文件：本站 uploads 资源直接复制到临时文件，避免向自身发起 HTTP 请求（单线程开发服务会死锁）
function didi_ai_mediacut_temp_from_url($url) {
  $up = wp_upload_dir();
  $baseurl = isset($up['baseurl']) ? $up['baseurl'] : '';
  $basedir = isset($up['basedir']) ? $up['basedir'] : '';
  if ($baseurl && $basedir && strpos($url, $baseurl) === 0) {
    $rel = preg_replace('/[?#].*$/', '', ltrim(substr($url, strlen($baseurl)), '/'));
    $src = trailingslashit($basedir) . $rel;
    $srcReal = realpath($src);
    $dirReal = realpath($basedir);
    if ($srcReal && $dirReal && strpos($srcReal, $dirReal) === 0 && is_file($srcReal)) {
      if (!function_exists('wp_tempnam')) require_once ABSPATH . 'wp-admin/includes/file.php';
      $tmp = wp_tempnam(basename($srcReal));
      if ($tmp && @copy($srcReal, $tmp)) return $tmp;
      return new WP_Error('mc_file', '素材读取失败', array('status' => 500));
    }
  }
  if (!function_exists('download_url')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
  }
  return download_url($url);
}

// 远端/本地 URL 转 CURLFile；调用方需负责 unlink($file->getFilename())
function didi_ai_mediacut_file_from_url($url) {
  $tmp = didi_ai_mediacut_temp_from_url($url);
  if (is_wp_error($tmp)) return $tmp;
  $mime = function_exists('mime_content_type') ? @mime_content_type($tmp) : '';
  if (!$mime) $mime = 'application/octet-stream';
  $name = basename(parse_url($url, PHP_URL_PATH));
  if (!$name) $name = 'upload';
  try {
    return new CURLFile($tmp, $mime, $name);
  } catch (Exception $e) {
    @unlink($tmp);
    return new WP_Error('mc_file', '素材处理失败', array('status' => 400));
  }
}

function didi_ai_mediacut_cleanup_file($file) {
  if ($file instanceof CURLFile) {
    $f = $file->getFilename();
    if ($f && file_exists($f)) @unlink($f);
  }
}

function didi_ai_mediacut_size_to_wh($size, $w, $h) {
  $w = (int) $w;
  $h = (int) $h;
  if ($w > 0 && $h > 0) return array($w, $h);
  $size = is_string($size) ? strtolower(trim($size)) : '';
  if (preg_match('/^(\d+)\s*[x×]\s*(\d+)$/', $size, $m)) {
    return array((int) $m[1], (int) $m[2]);
  }
  return array(1024, 1024);
}

/* ================= 业务封装 ================= */
// 文生图：POST /api/v1/ai/t2i
function didi_ai_mediacut_generate_image($params, $prompt) {
  $conf = didi_ai_mediacut_cfg();
  list($w, $h) = didi_ai_mediacut_size_to_wh(isset($params['size']) ? $params['size'] : '', isset($params['width']) ? $params['width'] : 0, isset($params['height']) ? $params['height'] : 0);
  $fields = array('prompt' => $prompt, 'width' => $w, 'height' => $h);
  $model = didi_ai_mediacut_model($conf, $params);
  if ($model) $fields['model'] = $model;
  $submit = didi_ai_mediacut_submit('/api/v1/ai/t2i', $fields, false, 60);
  if (is_wp_error($submit)) return $submit;
  $result = didi_ai_mediacut_fetch_task_result($submit['task_id'], 'image');
  if (is_wp_error($result)) return $result;
  return array('provider' => 'didi-media', 'urls' => array($result['url']), 'taskId' => $submit['task_id'], 'raw' => $result['raw']);
}

// 图生图 / 改图：POST /api/v1/ai/i2i（multipart: file + prompt）
function didi_ai_mediacut_i2i($params, $prompt, $imageUrl) {
  $file = didi_ai_mediacut_file_from_url($imageUrl);
  if (is_wp_error($file)) return $file;
  $fields = array('file' => $file, 'prompt' => $prompt);
  try {
    $submit = didi_ai_mediacut_submit('/api/v1/ai/i2i', $fields, true, 120);
  } finally {
    didi_ai_mediacut_cleanup_file($file);
  }
  if (is_wp_error($submit)) return $submit;
  $result = didi_ai_mediacut_fetch_task_result($submit['task_id'], 'image');
  if (is_wp_error($result)) return $result;
  return array('provider' => 'didi-media', 'urls' => array($result['url']), 'taskId' => $submit['task_id'], 'raw' => $result['raw']);
}

// 视频生成：POST /api/v1/ai/video（form: prompt/duration/motion/width/height）；有图片素材时走 /api/v1/ai/chat（media+text）
function didi_ai_mediacut_video_submit($params, $prompt) {
  $imageUrl = isset($params['imageUrl']) ? trim((string) $params['imageUrl']) : '';
  if ($imageUrl) {
    $file = didi_ai_mediacut_file_from_url($imageUrl);
    if (is_wp_error($file)) return $file;
    $fields = array('media' => $file, 'text' => $prompt);
    try {
      $submit = didi_ai_mediacut_submit('/api/v1/ai/chat', $fields, true, 120);
    } finally {
      didi_ai_mediacut_cleanup_file($file);
    }
  } else {
    $fields = array('prompt' => $prompt);
    $duration = isset($params['duration']) ? (int) $params['duration'] : 5;
    if ($duration <= 0) $duration = 5;
    $fields['duration'] = $duration;
    if (!empty($params['motion'])) $fields['motion'] = sanitize_text_field($params['motion']);
    if (!empty($params['width'])) $fields['width'] = (int) $params['width'];
    if (!empty($params['height'])) $fields['height'] = (int) $params['height'];
    $submit = didi_ai_mediacut_submit('/api/v1/ai/video', $fields, false, 60);
  }
  return $submit;
}

// 语音合成：POST /api/v1/ai/tts
function didi_ai_mediacut_tts($params, $text) {
  $fields = array('text' => $text);
  if (!empty($params['voice'])) $fields['voice'] = sanitize_text_field($params['voice']);
  $submit = didi_ai_mediacut_submit('/api/v1/ai/tts', $fields, false, 60);
  if (is_wp_error($submit)) return $submit;
  $result = didi_ai_mediacut_fetch_task_result($submit['task_id'], 'audio');
  if (is_wp_error($result)) return $result;
  return array('provider' => 'didi-media', 'urls' => array($result['url']), 'taskId' => $submit['task_id'], 'kind' => 'audio', 'raw' => $result['raw']);
}

// 图片编辑：修图（同步二进制）/ 改图 i2i / 抠图 / 增强（异步）
function didi_ai_mediacut_image_edit($params, $mode = 'edit') {
  $mode = in_array($mode, array('edit', 'i2i', 'matting', 'enhance'), true) ? $mode : 'edit';
  $imageUrl = isset($params['imageUrl']) ? trim((string) $params['imageUrl']) : (isset($params['mediaUrl']) ? trim((string) $params['mediaUrl']) : '');
  if (!$imageUrl) return new WP_Error('empty_media', '请先上传图片素材', array('status' => 400));
  $file = didi_ai_mediacut_file_from_url($imageUrl);
  if (is_wp_error($file)) return $file;

  if ($mode === 'edit') {
    $fields = array('file' => $file);
    $paramsJson = didi_ai_mediacut_image_params($params);
    if (!empty($paramsJson)) $fields['params'] = wp_json_encode($paramsJson);
    try {
      $res = didi_ai_mediacut_http('/api/v1/image/edit', $fields, true, 180);
    } finally {
      didi_ai_mediacut_cleanup_file($file);
    }
    if (is_wp_error($res)) return $res;
    if ($res['status'] >= 400) return new WP_Error('mc_edit', didi_ai_mediacut_error_message($res), array('status' => 502));
    $saved = didi_ai_mediacut_save_binary($res['raw'], $res['content_type'], 'image');
    if (is_wp_error($saved)) return $saved;
    return array('provider' => 'didi-media', 'type' => 'image', 'urls' => array($saved['url']), 'raw' => array());
  }

  if ($mode === 'i2i') {
    $prompt = isset($params['prompt']) ? trim((string) $params['prompt']) : '';
    if ($prompt === '') {
      didi_ai_mediacut_cleanup_file($file);
      return new WP_Error('empty_prompt', '改图需要填写修改说明', array('status' => 400));
    }
    $fields = array('file' => $file, 'prompt' => $prompt);
    try {
      $submit = didi_ai_mediacut_submit('/api/v1/ai/i2i', $fields, true, 120);
    } finally {
      didi_ai_mediacut_cleanup_file($file);
    }
    if (is_wp_error($submit)) return $submit;
    $result = didi_ai_mediacut_fetch_task_result($submit['task_id'], 'image');
    if (is_wp_error($result)) return $result;
    return array('provider' => 'didi-media', 'type' => 'image', 'urls' => array($result['url']), 'taskId' => $submit['task_id'], 'raw' => $result['raw']);
  }

  $endpoint = $mode === 'matting' ? '/api/v1/ai/matting' : '/api/v1/ai/enhance';
  try {
    $submit = didi_ai_mediacut_submit($endpoint, array('file' => $file), true, 120);
  } finally {
    didi_ai_mediacut_cleanup_file($file);
  }
  if (is_wp_error($submit)) return $submit;
  $result = didi_ai_mediacut_fetch_task_result($submit['task_id'], 'image');
  if (is_wp_error($result)) return $result;
  return array('provider' => 'didi-media', 'type' => 'image', 'urls' => array($result['url']), 'taskId' => $submit['task_id'], 'raw' => $result['raw']);
}

// 组装修图 params（前端可直接传 editParams JSON 对象，也可传扁平字段）
function didi_ai_mediacut_image_params($params) {
  $out = array();
  if (isset($params['editParams']) && is_array($params['editParams'])) $out = $params['editParams'];
  $map = array('filter', 'crop', 'resize', 'watermark', 'output_format');
  foreach ($map as $k) {
    if (isset($params[$k]) && $params[$k] !== '' && $params[$k] !== null) $out[$k] = $params[$k];
  }
  return $out;
}

// 音频编辑：POST /api/v1/audio/edit（同步二进制）
function didi_ai_mediacut_audio_edit($params) {
  $mediaUrl = isset($params['mediaUrl']) ? trim((string) $params['mediaUrl']) : (isset($params['imageUrl']) ? trim((string) $params['imageUrl']) : '');
  if (!$mediaUrl) return new WP_Error('empty_media', '请先上传音频素材', array('status' => 400));
  $file = didi_ai_mediacut_file_from_url($mediaUrl);
  if (is_wp_error($file)) return $file;
  $editParams = array();
  foreach (array('crop', 'volume', 'denoise', 'output_format') as $k) {
    if (isset($params[$k]) && $params[$k] !== '' && $params[$k] !== null) $editParams[$k] = $params[$k];
  }
  $fields = array('file' => $file);
  if (!empty($editParams)) $fields['params'] = wp_json_encode($editParams);
  try {
    $res = didi_ai_mediacut_http('/api/v1/audio/edit', $fields, true, 180);
  } finally {
    didi_ai_mediacut_cleanup_file($file);
  }
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) return new WP_Error('mc_audio', didi_ai_mediacut_error_message($res), array('status' => 502));
  $saved = didi_ai_mediacut_save_binary($res['raw'], $res['content_type'], 'audio');
  if (is_wp_error($saved)) return $saved;
  return array('provider' => 'didi-media', 'type' => 'audio', 'urls' => array($saved['url']), 'url' => $saved['url'], 'raw' => array());
}

// 语音识别：POST /api/v1/ai/asr
function didi_ai_mediacut_asr($params) {
  $mediaUrl = isset($params['mediaUrl']) ? trim((string) $params['mediaUrl']) : '';
  if (!$mediaUrl) return new WP_Error('empty_media', '请先上传音频素材', array('status' => 400));
  $file = didi_ai_mediacut_file_from_url($mediaUrl);
  if (is_wp_error($file)) return $file;
  try {
    $submit = didi_ai_mediacut_submit('/api/v1/ai/asr', array('file' => $file), true, 120);
  } finally {
    didi_ai_mediacut_cleanup_file($file);
  }
  if (is_wp_error($submit)) return $submit;
  return didi_ai_mediacut_wait($submit['task_id'], 120, 2);
}

/* ================= 剪辑页（VPS 代理）分发 ================= */
// 剪辑页图片/音频 Tab 走 /vps 代理；当 provider 为 didi Media 时改由适配层处理
function didi_ai_mediacut_vps_dispatch($feature, $path, $payload) {
  $pathLower = strtolower((string) $path);
  if ($feature === 'audio' || strpos($pathLower, 'audio/edit') !== false) {
    $res = didi_ai_mediacut_audio_edit($payload);
    $type = 'audio';
  } else {
    $mode = isset($payload['mode']) ? sanitize_key($payload['mode']) : '';
    if (!in_array($mode, array('edit', 'i2i', 'matting', 'enhance'), true)) {
      $mode = strpos($pathLower, 'matting') !== false ? 'matting' : (strpos($pathLower, 'enhance') !== false ? 'enhance' : 'edit');
    }
    $res = didi_ai_mediacut_image_edit($payload, $mode);
    $type = 'image';
  }
  if (is_wp_error($res)) return $res;
  return array(
    'ok' => true,
    'result' => array(
      'provider' => 'didi-media',
      'type' => $type,
      'urls' => isset($res['urls']) ? $res['urls'] : array(),
      'url' => isset($res['url']) ? $res['url'] : '',
      'status' => 'succeeded',
    ),
    'data' => $res,
  );
}

/* ================= 前端轮询任务（视频等长耗时任务） ================= */
function didi_ai_mediacut_task_endpoint($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $taskId = isset($params['taskId']) ? trim((string) $params['taskId']) : (isset($params['task_id']) ? trim((string) $params['task_id']) : '');
  if (!$taskId) return new WP_Error('empty_task', '缺少任务 ID', array('status' => 400));
  $poll = didi_ai_mediacut_poll_once($taskId);
  if (is_wp_error($poll)) return $poll;
  $status = $poll['status'];
  $out = array('ok' => true, 'taskId' => $taskId, 'status' => $status ? $status : 'pending');
  if (in_array($status, array('succeeded', 'success', 'completed', 'done'), true) && !empty($poll['result_url'])) {
    $kind = $poll['result_kind'] ? $poll['result_kind'] : 'video';
    $saved = didi_ai_mediacut_download($poll['result_url'], $kind);
    if (is_wp_error($saved)) return $saved;
    $out['url'] = $saved['url'];
    $out['kind'] = $kind;
  } elseif (in_array($status, array('failed', 'error', 'cancelled', 'canceled'), true)) {
    $out['ok'] = false;
    $out['message'] = isset($poll['raw']['error']) ? $poll['raw']['error'] : (isset($poll['raw']['message']) ? $poll['raw']['message'] : '任务失败');
  }
  return $out;
}
