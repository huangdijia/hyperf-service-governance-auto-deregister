<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-service-governance-auto-deregister.
 *
 * @link     https://github.com/huangdijia/hyperf-service-governance-auto-deregister
 * @document https://github.com/huangdijia/hyperf-service-governance-auto-deregister/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Huangdijia\HyperfServiceGovernance\Register;

use Hyperf\Consul\HealthInterface;

interface ConsulHealth extends HealthInterface
{
}
