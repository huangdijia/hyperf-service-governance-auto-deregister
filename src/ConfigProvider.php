<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-service-governance-auto-deregister.
 *
 * @link     https://github.com/huangdijia/hyperf-service-governance-auto-deregister
 * @document https://github.com/huangdijia/hyperf-service-governance-auto-deregister/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Huangdijia\HyperfServiceGovernance;

use Huangdijia\HyperfServiceGovernance\Register\ConsulHealth;
use Huangdijia\HyperfServiceGovernance\Register\ConsulHealthFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConsulHealth::class => ConsulHealthFactory::class,
            ],
            'signal' => [
                'handlers' => [
                    \Huangdijia\HyperfServiceGovernance\Handler\DeregeisterServicesHandler::class => PHP_INT_MIN,
                ],
            ],
        ];
    }
}
