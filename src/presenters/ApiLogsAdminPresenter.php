<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Repository\ApiLogsRepository;
use Crm\ApplicationModule\Components\Graphs\GoogleLineGraphGroupControlFactoryInterface;
use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\GraphDataItem;
use Crm\AdminModule\Presenters\AdminPresenter;

class ApiLogsAdminPresenter extends AdminPresenter
{
    /** @var ApiLogsRepository @inject */
    public $apiLogsRepository;

    public function renderDefault()
    {
        $this->template->apiLogs = $this->apiLogsRepository->getLast();
    }

    protected function createComponentApiCallsGraph(GoogleLineGraphGroupControlFactoryInterface $factory)
    {
        $graphDataItem1 = new GraphDataItem();
        $graphDataItem1->setCriteria((new Criteria())
            ->setTableName('api_logs')
            ->setTimeField('created_at')
            ->setValueField('COUNT(*)')
            ->setStart('-1 month'))
            ->setName('Api calls');

        $graphDataItem2 = new GraphDataItem();
        $graphDataItem2->setCriteria((new Criteria())
            ->setTableName('api_logs')
            ->setTimeField('created_at')
            ->setValueField('COUNT(*)')
            ->setWhere('AND response_code != 200')
            ->setStart('-1 month'))
            ->setName('Not 200 response');

        $control = $factory->create()
            ->setGraphTitle('Api calls')
            ->setGraphHelp('All api calls')
            ->addGraphDataItem($graphDataItem1)
            ->addGraphDataItem($graphDataItem2);

        return $control;
    }

    protected function createComponentResponseTimesGraph(GoogleLineGraphGroupControlFactoryInterface $factory)
    {
        $graphDataItem1 = new GraphDataItem();
        $graphDataItem1->setCriteria((new Criteria())
            ->setTableName('api_logs')
            ->setTimeField('created_at')
            ->setValueField('AVG(response_time)')
            ->setStart('-1 month'))
            ->setName('Response times');

        $control = $factory->create()
            ->setGraphTitle('Api response times')
            ->setGraphHelp('Api response times over time')
            ->addGraphDataItem($graphDataItem1);

        return $control;
    }
}
