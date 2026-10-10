<?php
/**
 * MPT API 开放平台适配层（门户网关）
 *
 * MPT 是非 OpenAI 兼容的短视频生成服务：使用 x-api-key 鉴权，提交主题后轮询任务，
 * 完成态 state=complete，结果通过 preview/download 接口以二进制返回。
 * 本文件把 MPT 包装成与主题现有处理器一致的返回结构。
 *
 * 配置来源优先级：后台 didi_ai_config[mpt] > 环境变量 USER_MPT_*
 */
if (!defined('ABSPATH')) exit;

/* ================= 配置 ================= */
function didi_ai_mpt_cfg() {
  $env = array(
    'provider' => 'mpt',
    'apiKey'   => getenv('USER_MPT_API_KEY') ?: '',
    'baseUrl'  => getenv('USER_MPT_BASE_URL') ?: '',
    'aspect'   => getenv('USER_MPT_ASPECT') ?: '9:16',
    'source'   => getenv('USER_MPT_VIDEO_SOURCE') ?: 'auto',
  );
  return didi_ai_cfg_merge('mpt', $env);
}

// 是否为 MPT 渠道（兼容前端 didi-video / didi-mpt 等取值）
function didi_ai_mpt_is_provider($provider) {
  $p = strtolower(trim((string) $provider));
  return in_array($p, array('mpt', 'didi-video', 'didi-mpt', 'didi_mpt', 'moneyprinter'), true);
}

function didi_ai_mpt_aspect($params, $conf) {
  $aspect = isset($params['aspect']) ? trim((string) $params['aspect']) : '';
  if (in_array($aspect, array('9:16', '16:9', '1:1'), true)) return $aspect;
  if (!empty($conf['aspect']) && in_array($conf['aspect'], array('9:16', '16:9', '1:1'), true)) return $conf['aspect'];
  return '9:16';
}

function didi_ai_mpt_source($params, $conf) {
  $source = isset($params['video_source']) ? trim((string) $params['video_source']) : '';
  if (in_array($source, array('pexels', 'pixabay', 'auto'), true)) return $source;
  if (!empty($conf['source']) && in_array($conf['source'], array('pexels', 'pixabay', 'auto'), true)) return $conf['source'];
  return 'auto';
}

/* ================= HTTP ================= */
// 统一请求：$body 为 null 表示 GET；返回原始响应体与状态码，供二进制下载复用
function didi_ai_mpt_http($path, $body = null, $timeout = 120, $method = '') {
  $conf = didi_ai_mpt_cfg();
  if (empty($conf['baseUrl']) || empty($conf['apiKey'])) {
    return new WP_Error('not_configured', 'MPT 视频接口未配置，请在后台「didi AI 配置」中填写 mpt 的 Base URL 与 API Key', array('status' => 503));
  }
  $base = rtrim($conf['baseUrl'], '/');
  $url = preg_match('#^https?://#i', (string) $path) ? $path : ($base . '/' . ltrim($path, '/'));
  $headersOut = array();
  $ch = curl_init($url);
  $opts = array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_HTTPHEADER => array('x-api-key: ' . $conf['apiKey'], 'Accept: application/json, */*'),
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
    CURLOPT_TCP_NODELAY => true,
    CURLOPT_DNS_CACHE_TIMEOUT => 300,
    CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$headersOut) {
      $pos = strpos($header, ':');
      if ($pos !== false) {
        $headersOut[strtolower(trim(substr($header, 0, $pos)))] = trim(substr($header, $pos + 1));
      }
      return strlen($header);
    },
  );
  if ($body === null) {
    if ($method) $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
  } else {
    $opts[CURLOPT_POST] = true;
    if ($method) $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
    $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
    $opts[CURLOPT_POSTFIELDS] = wp_json_encode($body);
  }
  curl_setopt_array($ch, $opts);
  $raw = curl_exec($ch);
  $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  $ctype = isset($headersOut['content-type']) ? $headersOut['content-type'] : '';
  curl_close($ch);
  if ($err) return new WP_Error('mpt_network', 'MPT 网络请求失败：' . $err, array('status' => 502));
  $data = json_decode($raw, true);
  return array('status' => $status, 'data' => is_array($data) ? $data : null, 'raw' => $raw, 'headers' => $headersOut, 'content_type' => $ctype, 'binary' => $data === null);
}

function didi_ai_mpt_error_message($res) {
  $data = is_array($res['data']) ? $res['data'] : array();
  if (isset($data['detail'])) {
    return is_string($data['detail']) ? $data['detail'] : wp_json_encode($data['detail']);
  }
  if (isset($data['message'])) return (string) $data['message'];
  if (isset($data['error'])) return is_string($data['error']) ? $data['error'] : wp_json_encode($data['error']);
  $raw = trim((string) $res['raw']);
  if ($raw !== '' && strlen($raw) < 200) return 'MPT 返回：' . $raw;
  $status = (int) $res['status'];
  if ($status === 530 || $status >= 520) {
    return didi_ai_is_admin_user()
      ? 'MPT 服务离线（HTTP ' . $status . '）：请确认你的 MPT 服务已启动、域名隧道正常后重试'
      : '免费视频服务暂时不可用，请稍后重试';
  }
  return 'MPT 请求失败（HTTP ' . $status . '）';
}

/* ================= 结果落地 ================= */
function didi_ai_mpt_save_binary($binary, $mime = 'video/mp4') {
  if ($binary === false || $binary === null || $binary === '') {
    return new WP_Error('mpt_empty', 'MPT 未返回有效内容', array('status' => 502));
  }
  $up = wp_upload_dir();
  $dir = trailingslashit($up['basedir']) . 'didi-mpt';
  if (!wp_mkdir_p($dir)) {
    return new WP_Error('mpt_dir', '无法创建结果目录', array('status' => 500));
  }
  $ext = didi_ai_mediacut_ext_for($mime, 'video');
  $name = 'mpt-' . gmdate('Ymd-His') . '-' . wp_generate_password(8, false, false) . '.' . $ext;
  $path = trailingslashit($dir) . $name;
  if (@file_put_contents($path, $binary) === false) {
    return new WP_Error('mpt_write', '结果写入失败', array('status' => 500));
  }
  return array(
    'path' => $path,
    'url' => trailingslashit($up['baseurl']) . 'didi-mpt/' . $name,
    'mime' => $mime,
  );
}

// 提交短视频生成任务，返回 task_id
function didi_ai_mpt_submit($params, $prompt) {
  $conf = didi_ai_mpt_cfg();
  $body = array(
    'video_subject' => $prompt,
    'aspect' => didi_ai_mpt_aspect($params, $conf),
    'video_source' => didi_ai_mpt_source($params, $conf),
  );
  if (!empty($params['video_script'])) $body['video_script'] = sanitize_textarea_field($params['video_script']);
  $res = didi_ai_mpt_http('/api/proxy/v1/videos', $body, 60);
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) {
    return new WP_Error('mpt_submit', didi_ai_mpt_error_message($res), array('status' => $res['status'] >= 500 ? 502 : $res['status']));
  }
  $data = is_array($res['data']) ? $res['data'] : array();
  $taskId = isset($data['task_id']) ? $data['task_id'] : (isset($data['taskId']) ? $data['taskId'] : '');
  if (!$taskId) return new WP_Error('mpt_submit', 'MPT 未返回任务 ID', array('status' => 502));
  return array('task_id' => $taskId, 'status' => isset($data['state']) ? $data['state'] : 'queued', 'raw' => $data);
}

// 单次轮询任务状态
function didi_ai_mpt_poll_once($taskId) {
  $res = didi_ai_mpt_http('/api/proxy/v1/videos/' . rawurlencode($taskId), null, 30, 'GET');
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) {
    return new WP_Error('mpt_poll', didi_ai_mpt_error_message($res), array('status' => 502));
  }
  $data = is_array($res['data']) ? $res['data'] : array();
  $state = strtolower(isset($data['state']) ? (string) $data['state'] : '');
  return array(
    'state' => $state,
    'progress' => isset($data['progress']) ? (int) $data['progress'] : 0,
    'stage' => isset($data['stage']) ? (string) $data['stage'] : '',
    'error' => isset($data['error']) ? $data['error'] : '',
    'script' => isset($data['script']) ? (string) $data['script'] : '',
    'duration' => isset($data['duration']) ? $data['duration'] : null,
    'file_size' => isset($data['file_size']) ? $data['file_size'] : null,
    'raw' => $data,
  );
}

// 下载成片并落地，返回公开 URL
function didi_ai_mpt_download($taskId) {
  $res = didi_ai_mpt_http('/api/proxy/v1/videos/' . rawurlencode($taskId) . '/download', null, 300, 'GET');
  if (is_wp_error($res)) return $res;
  if ($res['status'] >= 400) {
    return new WP_Error('mpt_download', didi_ai_mpt_error_message($res), array('status' => 502));
  }
  $ctype = $res['content_type'] ? $res['content_type'] : 'video/mp4';
  return didi_ai_mpt_save_binary($res['raw'], $ctype);
}

/* ================= 前端轮询任务端点 ================= */
function didi_ai_mpt_task_endpoint($req) {
  if (!is_user_logged_in()) return new WP_Error('login_required', '请先登录', array('status' => 401));
  $params = $req->get_json_params();
  $taskId = isset($params['taskId']) ? trim((string) $params['taskId']) : (isset($params['task_id']) ? trim((string) $params['task_id']) : '');
  if (!$taskId) return new WP_Error('empty_task', '缺少任务 ID', array('status' => 400));
  $poll = didi_ai_mpt_poll_once($taskId);
  if (is_wp_error($poll)) return $poll;
  $out = array(
    'ok' => true,
    'taskId' => $taskId,
    'status' => $poll['state'] ? $poll['state'] : 'queued',
    'progress' => $poll['progress'],
    'stage' => $poll['stage'],
  );
  if ($poll['state'] === 'complete') {
    $saved = didi_ai_mpt_download($taskId);
    if (is_wp_error($saved)) return $saved;
    $out['status'] = 'complete';
    $out['url'] = $saved['url'];
    $out['kind'] = 'video';
    $out['duration'] = $poll['duration'];
    $out['script'] = $poll['script'];
  } elseif ($poll['state'] === 'failed') {
    $out['ok'] = false;
    $out['message'] = $poll['error'] ? (is_string($poll['error']) ? $poll['error'] : wp_json_encode($poll['error'])) : '任务失败';
  }
  return $out;
}
