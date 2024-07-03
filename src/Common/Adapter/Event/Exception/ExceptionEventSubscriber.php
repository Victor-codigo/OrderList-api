<?php

declare(strict_types=1);

namespace Common\Adapter\Event\Exception;

use Override;
use Throwable;
use Common\Adapter\Http\Exception\HttpResponseException;
use Common\Domain\Config\AppConfig;
use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;
use Common\Domain\Response\ResponseDto;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    public const ERROR_404_MESSAGE = AppConfig::ERROR_404_MESSAGE;
    public const ERROR_403_MESSAGE = AppConfig::ERROR_403_MESSAGE;
    public const ERROR_500_MESSAGE = AppConfig::ERROR_500_MESSAGE;
    public const ERROR_METHOD_NOT_ALLOWED = AppConfig::ERROR_METHOD_NOT_ALLOWED;

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['__invoke']];
    }

      public function __invoke(ExceptionEvent $event): void
      {
          $exception = $event->getThrowable();
          $response = $this->handleSymfonyExceptions($exception);
          $response ??= $this->handleCustomException($event);
          $event->setResponse($response);
      }

      private function handleSymfonyExceptions(Throwable $exception): Response|null
      {
          if ($exception instanceof NotFoundHttpException) {
              $message = static::ERROR_404_MESSAGE;
              $status = Response::HTTP_NOT_FOUND;
              $responseStatus = RESPONSE_STATUS::OK;
          } elseif ($exception instanceof AccessDeniedHttpException) {
              $message = static::ERROR_403_MESSAGE;
              $status = Response::HTTP_FORBIDDEN;
              $responseStatus = RESPONSE_STATUS::OK;
          } elseif ($exception instanceof DomainInternalErrorException) {
              $message = static::ERROR_500_MESSAGE;
              $status = Response::HTTP_INTERNAL_SERVER_ERROR;
              $responseStatus = RESPONSE_STATUS::OK;
          } elseif ($exception instanceof MethodNotAllowedHttpException) {
              $message = static::ERROR_METHOD_NOT_ALLOWED;
              $status = Response::HTTP_METHOD_NOT_ALLOWED;
              $responseStatus = RESPONSE_STATUS::OK;
          } elseif ($exception instanceof BadRequestHttpException) {
              $message = $exception->getMessage();
              $status = $exception->getStatusCode();
              $responseStatus = RESPONSE_STATUS::ERROR;
          } else {
              return null;
          }

          $response = (new ResponseDto())
            ->setStatus($responseStatus)
            ->setMessage($message);

          return $this->createJsonResponse($response, $status);
      }

      private function handleCustomException(ExceptionEvent $event): Response
      {
          $exception = $event->getThrowable();

          if ($exception instanceof HttpResponseException) {
              return $this->createJsonResponse($exception->getResponseData(), $exception->getStatusCode());
          }

          $customExceptionDto = $this->getCustomExceptionDto($exception);
          $responseException = (new HttpResponseException())
              ->setMessage($exception->getMessage())
              ->setResponseData($customExceptionDto['responseDto'])
              ->setStatusCode($customExceptionDto['statusCode']->value);
          $event->setThrowable($responseException);

          return $this->createJsonResponse($responseException->getResponseData(), $responseException->getStatusCode());
      }

      private function getCustomExceptionDto(Throwable $exception): array
      {
          if ($exception instanceof DomainExceptionOutput) {
              $response = (new ResponseDto())
                  ->setStatus($exception->getStatus())
                  ->setMessage($exception->getMessage())
                  ->setErrors($exception->getErrors());

              return [
                  'responseDto' => $response,
                  'statusCode' => $exception->getHttpStatus(),
              ];
          }

          return $this->getResponseDefault();
      }

      private function getResponseDefault(): array
      {
          $response = (new ResponseDto())
              ->setStatus(RESPONSE_STATUS::ERROR)
              ->setMessage('Internal server error')
              ->setErrors(['internal' => 'Internal server error']);

          return [
              'responseDto' => $response,
              'statusCode' => RESPONSE_STATUS_HTTP::INTERNAL_SERVER_ERROR,
          ];
      }

      private function createJsonResponse(ResponseDto $responseDto, int $statusCode): JsonResponse
      {
          return new JsonResponse($responseDto, $statusCode);
      }
}
