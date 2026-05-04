<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>无权访问</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 32px 40px; border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,.06); text-align: center; }
        h1 { margin: 0 0 12px; font-size: 20px; color: #303133; }
        p { margin: 0 0 20px; color: #909399; font-size: 14px; }
        a { color: #409eff; text-decoration: none; }
    </style>
</head>
<body>
<div class="box">
    <h1>403 无权访问</h1>
    <p>当前账号未授权访问该页面或接口。</p>
    <a href="{{ url('/admin') }}">返回工作台</a>
</div>
</body>
</html>
