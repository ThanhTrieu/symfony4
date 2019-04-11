<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/17/2018
 * Time: 4:19 PM
 */

namespace App\Controller\Post;

use App\Controller\BaseController;
use App\Entity\GroupBoxes;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Entity\PostPublishes;

class WidgetController extends BaseController
{
    /**
     * Get trending data
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function trending(DataExchange $dataExchange)
    {
        $optionData = $this->getGroupBox(Constants::OPTION_TRENDING_DATA, $dataExchange);

        return $this->render('common/trending.html.twig', array(
            'optionData' => $optionData
        ));
    }

    /**
     * Get groupbox
     * author: ThanhDT
     * date:   2018-10-21 09:14 PM
     * @param $groupBoxKey
     * @param DataExchange $dataExchange
     * @return mixed
     * @throws \Exception
     */
    protected function getGroupBox($groupBoxKey, DataExchange $dataExchange)
    {
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $data = $cacheService->get($groupBoxKey);
        if ($data === false) {
            $em = $this->getDoctrine()->getManager();
            $optionData = $em->getRepository(GroupBoxes::class)->getBoxByKey($groupBoxKey);
            $data = $dataExchange->exchangeGroupBox($optionData);
            $cacheService->set($groupBoxKey, $data, $this->getParameter('cache_time')['hour']);
        }

        return $data;
    }
}
