<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DeleteOldCartsCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    protected $cartFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(CartFacade $cartFacade)
    {
        $this->cartFacade = $cartFacade;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->cartFacade->deleteOldCarts();
    }
}
