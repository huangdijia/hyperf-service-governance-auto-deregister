<?php

namespace Huangdijia\HyperfServiceGovernance;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'signal' => [
                'handlers' => [
                    \Huangdijia\HyperfServiceGovernance\Handler\DeregeisterServicesHandler::class => PHP_INT_MIN
                ],
            ]
        ];
    }
}