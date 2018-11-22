<?php

namespace Crm\ApiModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\ApiModule\Repository\ApiLogsRepository;
use Crm\ApplicationModule\Components\Graphs\GoogleLineGraphGroupControlFactoryInterface;
use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\GraphDataItem;

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
            ->setName($this->translator->translate('api.admin.api_logs.graph.api_calls.total'));

        $graphDataItem2 = new GraphDataItem();
        $graphDataItem2->setCriteria((new Criteria())
            ->setTableName('api_logs')
            ->setTimeField('created_at')
            ->setValueField('COUNT(*)')
            ->setWhere('AND response_code != 200')
            ->setStart('-1 month'))
            ->setName($this->translator->translate('api.admin.api_logs.graph.api_calls.not_200_response'));

        $control = $factory->create()
            ->setGraphTitle($this->translator->translate('api.admin.api_logs.graph.api_calls.title'))
            ->setGraphHelp($this->translator->translate('api.admin.api_logs.graph.api_calls.tooltip'))
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
            ->setName($this->translator->translate('api.admin.api_logs.graph.response_times.response_times'));

        $control = $factory->create()
            ->setGraphTitle($this->translator->translate('api.admin.api_logs.graph.response_times.title'))
            ->setGraphHelp($this->translator->translate('api.admin.api_logs.graph.response_times.tooltip'))
            ->addGraphDataItem($graphDataItem1);

        return $control;
    }
}
