<?php

namespace Huangdijia\HyperfServiceGovernance;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Hyperf\ServiceGovernance\ServiceManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;

class DeregeisterServicesHandler implements SignalHandlerInterface
{
    /**
     * @var ConsulAgent
     */
    protected $consulAgent;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $defaultLoggerContext = [
        'component' => 'service-governance-auto-deregister',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->serviceManager = $container->get(ServiceManager::class);
        $this->config = $container->get(ConfigInterface::class);
        $this->consulAgent = $container->get(ConsulAgent::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        if ($signal !== SIGINT) {
            $time = $this->config->get('server.settings.max_wait_time', 3);
            sleep($time);
        }

        $services = $this->serviceManager->all();
        $servers = $this->getServers();

        collect($this->consulAgent->services()->json())
            ->filter(function ($service) use ($services) {
                return in_array($service['Service'], array_keys($services));
            })
            ->filter(function ($service) use ($servers) {
                return in_array($service['Address'] . ':' . $service['Port'], array_map(function ($server) { return $server[0] . ':' . $server[1]; }, $servers));
            })
            ->each(function ($service) {
                $this->consulAgent->deregisterService($service['ID']);
                $this->logger->info(sprintf('Service %s deregistered.', $service['Service']), $this->defaultLoggerContext);
            });
    }

    protected function getServers(): array
    {
        $result = [];
        $servers = $this->config->get('server.servers', []);
        foreach ($servers as $server) {
            if (! isset($server['name'], $server['host'], $server['port'])) {
                continue;
            }
            if (! $server['name']) {
                throw new \InvalidArgumentException('Invalid server name');
            }
            $host = $server['host'];
            if (in_array($host, ['0.0.0.0', 'localhost'])) {
                $host = $this->getInternalIp();
            }
            if (! filter_var($host, FILTER_VALIDATE_IP)) {
                throw new \InvalidArgumentException(sprintf('Invalid host %s', $host));
            }
            $port = $server['port'];
            if (! is_numeric($port) || ($port < 0 || $port > 65535)) {
                throw new \InvalidArgumentException(sprintf('Invalid port %s', $port));
            }
            $port = (int) $port;
            $result[$server['name']] = [$host, $port];
        }
        return $result;
    }

    protected function getInternalIp(): string
    {
        $ips = swoole_get_local_ip();
        if (is_array($ips) && ! empty($ips)) {
            return current($ips);
        }
        /** @var mixed|string $ip */
        $ip = gethostbyname(gethostname());
        if (is_string($ip)) {
            return $ip;
        }
        throw new \RuntimeException('Can not get the internal IP.');
    }
}
