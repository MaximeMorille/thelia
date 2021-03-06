<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\Action;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Brand\BrandCreateEvent;
use Thelia\Core\Event\Brand\BrandDeleteEvent;
use Thelia\Core\Event\Brand\BrandToggleVisibilityEvent;
use Thelia\Core\Event\Brand\BrandUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\UpdatePositionEvent;
use Thelia\Core\Event\UpdateSeoEvent;
use Thelia\Model\Brand as BrandModel;
use Thelia\Model\BrandQuery;

/**
 * Class Brand
 *
 * @package Thelia\Action
 * @author  Franck Allimant <franck@cqfdev.fr>
 */
class Brand extends BaseAction implements EventSubscriberInterface
{
    public function create(BrandCreateEvent $event)
    {
        $brand = new BrandModel();

        $brand
            ->setVisible($event->getVisible())
            ->setLocale($event->getLocale())
            ->setTitle($event->getTitle())
            ->save()
        ;

        $event->setBrand($brand);
    }

    /**
     * process update brand
     *
     * @param BrandUpdateEvent $event
     */
    public function update(BrandUpdateEvent $event)
    {
        if (null !== $brand = BrandQuery::create()->findPk($event->getBrandId())) {
            $brand->setDispatcher($event->getDispatcher());

            $brand
                ->setVisible($event->getVisible())
                ->setLogoImageId(intval($event->getLogoImageId()) == 0 ? null : $event->getLogoImageId())
                ->setLocale($event->getLocale())
                ->setTitle($event->getTitle())
                ->setDescription($event->getDescription())
                ->setChapo($event->getChapo())
                ->setPostscriptum($event->getPostscriptum())
                ->save()
            ;

            $event->setBrand($brand);
        }
    }

    /**
     * Toggle Brand visibility
     *
     * @param BrandToggleVisibilityEvent $event
     */
    public function toggleVisibility(BrandToggleVisibilityEvent $event)
    {
        $brand = $event->getBrand();

        $brand
            ->setDispatcher($event->getDispatcher())
            ->setVisible(!$brand->getVisible())
            ->save();

        $event->setBrand($brand);
    }

    /**
     * Change Brand SEO
     *
     * @param \Thelia\Core\Event\UpdateSeoEvent $event
     *
     * @return mixed
     */
    public function updateSeo(UpdateSeoEvent $event)
    {
        return $this->genericUpdateSeo(BrandQuery::create(), $event);
    }

    public function delete(BrandDeleteEvent $event)
    {
        if (null !== $brand = BrandQuery::create()->findPk($event->getBrandId())) {
            $brand->setDispatcher($event->getDispatcher())->delete();

            $event->setBrand($brand);
        }
    }

    public function updatePosition(UpdatePositionEvent $event)
    {
        $this->genericUpdatePosition(BrandQuery::create(), $event);
    }
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::BRAND_CREATE     => array('create', 128),
            TheliaEvents::BRAND_UPDATE     => array('update', 128),
            TheliaEvents::BRAND_DELETE     => array('delete', 128),

            TheliaEvents::BRAND_UPDATE_SEO => array('updateSeo', 128),

            TheliaEvents::BRAND_UPDATE_POSITION   => array('updatePosition', 128),
            TheliaEvents::BRAND_TOGGLE_VISIBILITY => array('toggleVisibility', 128),
        );
    }
}
