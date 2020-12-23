<?php

namespace Huangdijia\HyperfServiceGovernance;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'signal' => [
                'handlers' => [
                    DeregeisterServicesHandler::class => PHP_INT_MIN
                ],
            ]
        ];
    }
}