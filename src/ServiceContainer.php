<?php

namespace AlexQiu\Sdkit;

use AlexQiu\Sdkit\Exceptions\InvalidArgumentException;
use AlexQiu\Sdkit\Log\LogManager;
use DI\ContainerBuilder;
use DI\Container;
use AlexQiu\Sdkit\Providers\ConfigServiceProvider;
use AlexQiu\Sdkit\Providers\EventDispatcherServiceProvider;
use AlexQiu\Sdkit\Providers\HttpClientServiceProvider;
use AlexQiu\Sdkit\Providers\LogServiceProvider;
use AlexQiu\Sdkit\Providers\RequestServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * ServiceContainer
 *
 * @property Config          $config
 * @property LoggerInterface $logger
 * @author  alex
 * @package AlexQiu\Sdkit\ServiceContainer
 */
class ServiceContainer
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * @var array
     */
    protected $userConfig = [];

    /**
     * 构造函数
     *
     * @param array $config 用户配置
     */
    public function __construct(array $config = [])
    {
        $this->userConfig = $config;

        // 创建容器构建器
        $builder = new ContainerBuilder();

        // 添加定义
        $builder->addDefinitions($this->getDefinitions());

        // 构建容器
        $this->container = $builder->build();

        // 注册服务提供者
        $this->registerProviders($this->getProviders());
    }

    /**
     * @return array
     */
    public function getProviders(): array
    {
        return array_merge(
            [
                ConfigServiceProvider::class,
                LogServiceProvider::class,
                RequestServiceProvider::class,
                HttpClientServiceProvider::class,
                EventDispatcherServiceProvider::class,
            ],
            $this->providers,
        );
    }

    /**
     * 获取完整配置
     *
     * @return array
     */
    public function getConfig(): array
    {
        $base = [
            'http' => [
                'timeout' => 30.0,
            ],
        ];
        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    /**
     * 获取 DI 容器
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * 动态获取服务
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * 注册服务提供者
     *
     * @param array $providers 服务提供者类名数组
     */
    protected function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            (new $provider())->register($this);
        }
    }

    /**
     * 定义基础服务
     *
     * @return array
     */
    protected function getDefinitions(): array
    {
        return [
            'config'               => function () {
                return new Config($this->getConfig());
            },
            LoggerInterface::class => function () {
                // 检查用户配置是否提供了自定义的 LoggerInterface 实现
                if (isset($this->userConfig['logger'])) {
                    $logger = $this->userConfig['logger'];
                    if ($logger instanceof LoggerInterface) {
                        return $logger;
                    }
                    throw new InvalidArgumentException(
                        'Invalid logger provided. Must implement ' . LoggerInterface::class
                    );
                }
                return new LogManager($this);
            }
        ];
    }
}