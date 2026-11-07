# shop.daqi.asia 产品发布同步包

把本地已 publish 的 25,150 个产品状态同步到 shop.daqi.asia 源站。

## 包内容
- publish-sync-shop.php  同步脚本（按 ID 白名单，幂等，slug 自动查重生成）
- ids.txt                25,150 个产品 ID（本地库导出）

## 源站执行步骤
1. 把这两个文件上传到 shop.daqi.asia 服务器的 WordPress 根目录下（与 wp-load.php 同级或 scripts/ 目录均可，脚本里按 `__DIR__/../wp-load.php` 寻址，放 wp-content/ 平级目录时无需改动）
2. 在服务器上执行：
   php publish-sync-shop.php ids.txt
   （如 wp-cli 环境：wp eval-file publish-sync-shop.php ids.txt 需去掉 require 行改用 wp 环境，推荐直接 php）
3. 预计耗时 3-5 分钟（500 条/批 SQL 更新），输出 progress 到完成
4. 验证：刷新任意原 draft 产品页（应 200），后台产品列表确认已发布

## 注意
- 脚本只改 post_status / post_name / post_modified，不动其他数据
- slug 撞名自动加 -2 后缀
- 可重复执行（幂等）
