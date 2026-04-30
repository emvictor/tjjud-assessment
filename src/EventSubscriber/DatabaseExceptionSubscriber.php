<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class DatabaseExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [['onKernelException', 10]],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $message = null;

        $current = $exception;
        while ($current !== null) {
            if ($current instanceof ForeignKeyConstraintViolationException) {
                $message = 'Este registro não pode ser excluído pois existem outros dados vinculados a ele.';
                break;
            }
            if ($current instanceof UniqueConstraintViolationException) {
                $message = 'Já existe um registro com estes dados informados.';
                break;
            }
            $current = $current->getPrevious();
        }

        if ($message) {
            $request = $event->getRequest();
            $session = $request->getSession();

            $session->getFlashBag()->add('danger', $message);

            $referer = $request->headers->get('referer') ?? $request->getBasePath();
            $event->setResponse(new RedirectResponse($referer));
        }
    }
}