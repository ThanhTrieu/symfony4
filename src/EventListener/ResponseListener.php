<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 12/3/2017
 * Time: 6:07 PM
 */

namespace NewsBundle\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -256))
        );
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        //return;

        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $response = $event->getResponse();
        if($response->getStatusCode() != 200) return;
        if($response->headers->get('Content-Encoding') === 'gzip') return;
        if($response->headers->get('Content-Type') === 'image/gif') return;

        //$request = $event->getRequest();
        //$encodings = $request->headers->get('Accept-Encoding');
        //if (strpos($encodings, 'gzip') !== false) {
            $content = gzencode($response->getContent());
            $response->headers->set('Content-Encoding', 'gzip');
            $response->setContent($content);
        //}
        /*elseif (in_array('deflate', $encodings) && function_exists('gzdeflate')) {
            $content = gzdeflate($response->getContent());
            $response->headers->set('Content-encoding', 'deflate');
            $response->setContent($content);
        }*/
    }
}