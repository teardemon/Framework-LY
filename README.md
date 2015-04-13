README

    Author: LyonWong
    Update: 2014-09-26

# 环境要求

- **Linux**
- Apache | Nginx
- Mysql 5.6
- **PHP 5.5**
- PHPUnit 4.2
- Nodejs, Karma


# 初始配置

## WebServer Rewrite

    # Apache
    RewriteEngine On
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-s
    RewriteRule ^(.*)$ /boot_web.php$1 [QSA]

## config

执行`bin/init -c`使默认配置文件生效

## mklink

cgiroot/resource -> ../application/resource

## 确认检查

- 在浏览器打开站点，可以看到欢迎提示
- 命令行执行 `bin/exe /` 可以看到index-index提示


# 核心系统

## 启动

## 配置

- common 固定配置文件
- local 本地配置文件
- default 默认配置文件
- .conf为解析后实际使用的文件
- init.sh 是shell脚本的配置文件

配置优先级为 local > common > default

修改配置后，执行`bin/init -c`生效

## 路由

- URI按目录层级和Controller对应
- CGI模式启动时，需定义`CTL_PREFIX`作为Controller定位前缀
- e.g.
 * / => \CTL\index-index()
 * /cron/ => \CTL\index-index()
 * /Web/demo => \CTL\demo-index()
 * /Web/demo-api-foo => \CTL\demo-api(foo)
 * /Web/next/ => \CTL\next\index-index()

## 自动加载

## 异常

- 两层结构，异常类(如`coreException`)，和其中定义的异常项(如`AUTOLOAD_FAILED`)
- 用异常状态码的第一个字节表示异常类，后三个字节表示异常项，显示时用`.`分隔
 * 如 `1.3`表示`coreException::CONFIG_NOT_FOUND`
- 异常项的描述，需要在`descriptions`中以格式化字符串的方式额外定义
- 异常将在`boot_cgi | boot_cli`中被捕获

## 输入输出

## 展示

- 所有的展示资源，都应该通过`view`来调用
- 开发环境以当前时间戳为版本号，发布环境版本号从配置文件中获取

## 命令

- exe 按URI调用controller
- init 初始化
 * -c 配置文件
- test 执行PHP单元测试
- test.bat 执行JS单元测试

## 调试

### PHP调试

- 为需要调试的模块定义相应的调试位，必须是2的幂次
- 定义`DEBUG_MODE`作为调试模块的开关，只有当对应位为真时，该项调试信息才会输出
- 特别的，当`DEBUB_MODE`为0时，不会输出调试信息

### WEB调试

可通过`view::debug`开启自动刷新，当自动检测资源文件是否更改并自动刷新页面

## 测试

### PHPUnit

- 目录结构和框架同构

### JSUnit

- 使用karma和jasmine为测试框架

# 规范

## 命名

- 驼峰法则，大小写和命名空间写一致
- 在形式上，用前缀`_`表示非公开的内容


