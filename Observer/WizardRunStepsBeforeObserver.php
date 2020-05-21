<?php
namespace CustomerParadigm\AmazonPersonalize\Observer;

use Magento\Framework\Event\ObserverInterface;

class WizardRunStepsBeforeObserver implements ObserverInterface
{
    protected $awsEvents;
    protected $request;


    public function __construct(
        \CustomerParadigm\AmazonPersonalize\Model\Events $awsEvents,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->awsEvents = $awsEvents;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
file_put_contents('/home/demo/public_html/hoopologie/var/log/obs.log',"\n Wizard before", FILE_APPEND);
    }
}
