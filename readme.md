# SDKit

SDKit 是一个 PHP SDK 基础工具包，提供了一系列便于开发的功能模块，例如 HTTP 请求、日志处理、事件处理和异常处理等。  
它旨在作为其他外部 SDK 的基础支持工具库，帮助开发者快速构建功能完善的 SDK。

---

## 特性

- **HTTP 请求支持**：基于 [Guzzle](https://github.com/guzzle/guzzle) 封装常用的 HTTP 请求逻辑。
- **日志处理**：支持多种日志驱动，默认基于 [Monolog](https://github.com/Seldaek/monolog)。
- **事件处理**：内置事件分发机制，兼容 Symfony 的事件分发器。
- **异常处理**：封装常见异常类型，方便集成与扩展。
- **高度可扩展**：基于 [PHP-DI](https://php-di.org/) 实现依赖注入，支持自定义配置。
- **框架兼容性**：依赖 `php-di`，可与以下框架无缝集成：
  - [Laravel](https://laravel.com/) (通过扩展 `ServiceContainer`)
  - [Hyperf](https://hyperf.io/) (支持协程和依赖注入)
  - [Symfony](https://symfony.com/) (与其依赖注入组件完美结合)
  - 其他基于 PSR 标准的框架

---

## 安装

使用 [Composer](https://getcomposer.org/) 安装：

```bash
composer require alex-qiu/sdkit
```

---

## 使用方法

### 1. 创建 ServiceContainer

ServiceContainer 是 SDKit 的核心管理器，负责加载配置并初始化相关组件。

```php
use AlexQiu\Sdkit\ServiceContainer;

$config = [
    'http' => [
        'timeout' => 30.0,
    ],
    'logger' => [
        'default' => 'single',
        'channels' => [
            'single' => [
                'driver' => 'single',
                'path' => 'php://stdout',
                'level' => 'debug',
            ],
        ],
    ],
];

$container = new ServiceContainer($config);
```

### 2. 发起 HTTP 请求

SDKit 提供了封装的 HTTP 客户端，基于 Guzzle 实现。

```php
use AlexQiu\Sdkit\BaseClient;

$client = new BaseClient($container);
$response = $client->httpGet('https://api.example.com/resource');
```

### 3. 使用日志功能

通过配置 logger，可以快速记录日志。

```php
$logger = $container->logger;

$logger->info('This is an informational message.');
$logger->error('This is an error message.');
```

---

## ⚙️ 兼容性

SDKit 使用了现代 PHP 特性和依赖库，并且具备广泛的兼容性：

- **PHP 版本**：支持 `PHP 8.0` 及以上版本。
- **依赖框架**：
  - 由于 SDKit 使用了 [PHP-DI](https://php-di.org/)，可以轻松集成到任何兼容 PSR-11 容器的框架中，例如：
    - **Laravel**（通过依赖注入支持）
    - **Symfony**（使用服务容器）
    - **Hyperf**（使用自定义 Provider）
    - **其他支持 PSR 标准的框架**。

如果您在某些框架中遇到兼容性问题，请通过 Issue 向我们反馈，我们会尽快修复！

---

## ⚙️ 依赖

- **PHP 版本**：支持 PHP 8.0 及以上版本。
- **依赖组件**：
  - [php-di/php-di](https://php-di.org/)：提供灵活的依赖注入机制。
  - [guzzlehttp/guzzle](https://github.com/guzzle/guzzle)：用于高效的 HTTP 请求。
  - [monolog/monolog](https://github.com/Seldaek/monolog)：支持多种日志驱动。
  - [symfony/event-dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html)：用于事件分发。
  - [symfony/http-foundation](https://symfony.com/doc/current/components/http_foundation.html)：提供 HTTP 请求和响应的抽象层。

---

## 🛠️ 贡献

欢迎开发者为 SDKit 做出贡献！我们非常乐意接受您的 **Issue** 或 **Pull Request**。

在贡献之前，请确保您的代码符合以下要求：

1. 遵循 **PSR 标准**。
2. 通过所有单元测试。
3. 提交代码时附带必要的说明。

**贡献步骤：**

1. **Fork 仓库**。
2. 创建功能分支：`git checkout -b feature/my-feature`。
3. 提交更改：`git commit -m "Add my feature"`。
4. 推送分支：`git push origin feature/my-feature`。
5. 提交 **Pull Request** 并等待审核。

我们期待您的参与和支持！ 🙌
