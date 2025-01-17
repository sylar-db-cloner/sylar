<?php

declare(strict_types=1);

namespace App\Infrastructure\Docker;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

final class DockerApiLogger implements LoggerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param string $message
     */
    public function log($level, $message, array $context = []): void
    {
        if (array_key_exists('response', $context)) {
            /** @var Request $request */
            $request = $context['request'];
            /** @var Response $response */
            $response = $context['response'];
            try {
                $this->logger->log($level, sprintf(
                    '%s [%s] %s : %s',
                    $request->getMethod(),
                    $response->getStatusCode(),
                    $request->getUri(),
                    $request->getBody(),
                ));
            } catch (NotEncodableValueException $exception) {
                dump($exception->getMessage());
            }
        }
    }

    /**
     * @param string $message
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param string $message
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param string $message
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param string $message
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param string $message
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param string $message
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param string $message
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param string $message
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
