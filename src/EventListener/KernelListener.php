<?php

namespace AndreasBurg\CapBundle\EventListener;

use AndreasBurg\CapBundle\Attribute\Cap;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerAttributeEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

class KernelListener
{
  public function __construct(
    private readonly HttpClientInterface $client,
    private readonly Environment $twig,
  ) {
    //
  }

  #[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS . '.' . Cap::class)]
  public function onKernelArguments(ControllerAttributeEvent $e): void
  {
    if (!$e->kernelEvent->isMainRequest()) {
      return;
    }

    if ($e->attribute instanceof Cap) {
      if ($e->kernelEvent instanceof ControllerArgumentsEvent) {
        $token = $e->kernelEvent->getRequest()->query->get('token', null);
        if (!$token || !$this->verifyToken($token, $e->attribute->getSiteKey(), $e->attribute->getSiteSecret())) {
          $siteKey = $e->attribute->getSiteKey();
          $e->kernelEvent->setController(function () use ($siteKey) {
            return new Response($this->twig->render('@CapBundle/widget.html.twig', ['siteKey' => $siteKey]));
          });
        }
      }
    }
  }

  private function verifyToken(string $token, string $siteKey, string $siteSecret): bool
  {
    $url = 'https://cap.vocugi.han-solo.net/' . $siteKey . '/siteverify';

    try {
      $response = $this->client->request('POST', $url, [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => [
          'secret' => $siteSecret,
          'response' => $token,
        ],
      ]);

      $data = json_decode($response->getContent(), true);

      return $data['success'] ?? false;
    } catch (Exception $e) {
      return false;
    }
  }
}
