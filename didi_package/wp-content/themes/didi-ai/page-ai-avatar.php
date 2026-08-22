<?php
/**
 * AI 数字人功能页模板
 * Template Name: AI 数字人
 */
get_header();
$section = 'avatar';
$title = '数字人';
$icon = '&#128100;';
$color = '#0aa1a1';
$desc = '数字人口播视频脚本创作';
$welcome = '你好！我是 didi AI 的数字人助手。告诉我口播主题，为你生成完整视频脚本。';
$placeholder = '输入数字人视频需求，如：生成一段 30 秒产品介绍的直播口播脚本...（Enter 发送）';
$footer_text = 'didi AI · AI 数字人（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
?>
<div style="max-width:1080px;margin:0 auto 40px;padding:0 16px;">
  <div class="card" style="margin-top:20px; overflow-x:auto;">
    <h3 style="margin-bottom:12px;">数字人 API 定价</h3>
    <table style="width:100%; border-collapse:collapse; font-size:13px; min-width:640px;">
      <thead>
        <tr style="background:var(--bg-soft); text-align:left;">
          <th style="padding:10px 12px;">分区</th>
          <th style="padding:10px 12px;">厂商</th>
          <th style="padding:10px 12px;">档位</th>
          <th style="padding:10px 12px;">价格</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding:10px 12px;" rowspan="6">国内模型</td>
          <td style="padding:10px 12px;"><b>火山引擎（即梦）</b></td>
          <td style="padding:10px 12px;"><span class="badge">基础档</span></td>
          <td style="padding:10px 12px; font-weight:700;">20 点/条</td>
        </tr>
        <tr><td style="padding:10px 12px;"><b>腾讯智影</b></td><td style="padding:10px 12px;"><span class="badge">基础档</span></td><td style="padding:10px 12px; font-weight:700;">25 点/条</td></tr>
        <tr><td style="padding:10px 12px;"><b>晟诺科讯达</b></td><td style="padding:10px 12px;"><span class="badge">标准档</span></td><td style="padding:10px 12px; font-weight:700;">30 点/条</td></tr>
        <tr><td style="padding:10px 12px;"><b>讯飞虚拟人</b></td><td style="padding:10px 12px;"><span class="badge">标准档</span></td><td style="padding:10px 12px; font-weight:700;">35 点/条</td></tr>
        <tr><td style="padding:10px 12px;"><b>硅基智能</b></td><td style="padding:10px 12px;"><span class="badge">高端档</span></td><td style="padding:10px 12px; font-weight:700;">40 点/条</td></tr>
        <tr><td style="padding:10px 12px;"><b>百度曦灵</b></td><td style="padding:10px 12px;"><span class="badge">高端档</span></td><td style="padding:10px 12px; font-weight:700;">45 点/条</td></tr>
        <tr style="background:var(--bg-soft);">
          <td style="padding:10px 12px;" rowspan="3">海外模型</td>
          <td style="padding:10px 12px;"><b>D-ID</b></td>
          <td style="padding:10px 12px;"><span class="badge">标准档</span></td>
          <td style="padding:10px 12px; font-weight:700;">50 点/条</td>
        </tr>
        <tr><td style="padding:10px 12px;"><b>HeyGen</b></td><td style="padding:10px 12px;"><span class="badge">高端档</span></td><td style="padding:10px 12px; font-weight:700;">60 点/条</td></tr>
        <tr><td style="padding:10px 12px;"><b>Synthesia</b></td><td style="padding:10px 12px;"><span class="badge">旗舰档</span></td><td style="padding:10px 12px; font-weight:700;">70 点/条</td></tr>
        <tr>
          <td style="padding:10px 12px;">自定义</td>
          <td style="padding:10px 12px;"><b>自定义模型</b> <span style="color:var(--danger);font-size:12px;">需会员</span></td>
          <td style="padding:10px 12px;"><span class="badge">会员专属</span></td>
          <td style="padding:10px 12px; font-weight:700;">按量计费</td>
        </tr>
      </tbody>
    </table>
    <p style="font-size:12px;color:var(--text-faint);margin-top:10px;">价格从低到高排序 · ¥1 = 100 点数 · 自定义模型需会员资格（促销期 ¥0.01 开通）</p>
  </div>
</div>
<?php
get_footer();
