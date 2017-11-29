<?php

use App\Infrastructure\Exceptions\Formatters as ExtFormatters;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
use Optimus\Heimdal\Formatters as BaseFormatters;

return [
    'add_cors_headers' => false,

    // Has to be in prioritized order, e.g. highest priority first.
    'formatters' => [
        SymfonyException\UnprocessableEntityHttpException::class => BaseFormatters\UnprocessableEntityHttpExceptionFormatter::class,
        SymfonyException\AccessDeniedHttpException::class => ExtFormatters\AccessDeniedHttpExceptionFormatter::class,
        SymfonyException\UnauthorizedHttpException::class => ExtFormatters\UnauthorizedHttpExceptionFormatter::class,
        SymfonyException\NotFoundHttpException::class => ExtFormatters\NotFoundHttpExceptionFormatter::class,
        SymfonyException\ConflictHttpException::class => ExtFormatters\ConflictHttpExceptionFormatter::class,
        SymfonyException\HttpException::class => BaseFormatters\HttpExceptionFormatter::class,
        Exception::class => BaseFormatters\ExceptionFormatter::class,
    ],

    'response_factory' => \Optimus\Heimdal\ResponseFactory::class,

    'reporters' => [
        /*'sentry' => [
            'class'  => \Optimus\Heimdal\Reporters\SentryReporter::class,
            'config' => [
                'dsn' => '',
                // For extra options see https://docs.sentry.io/clients/php/config/
                // php version and environment are automatically added.
                'sentry_options' => []
            ]
        ]*/
    ],

    'server_error_production' => 'An error occurred.'
];
