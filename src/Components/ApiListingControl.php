<?php

declare(strict_types=1);

namespace Crm\ApiModule\Components;

use Crm\ApiModule\Api\LazyApiDecider;
use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * @method void onClick(string $method, int $version, string $package, ?string $apiAction)
 */
class ApiListingControl extends Control
{
    public $onClick = [];

    public function __construct(private LazyApiDecider $apiDecider)
    {
    }

    public function render(): void
    {
        $apis = $this->apiDecider->getApis();

        /** @var Template $template */
        $template = $this->getTemplate();
        $template->add('apis', $this->groupApis($apis));
        $template->setFile($this->getTemplateFilePath());
        $template->render();
    }

    private function groupApis(array $handlers): array
    {
        $versionHandlers = [];
        foreach ($handlers as $handler) {
            $endPoint = $handler->getEndpoint();
            if (!isset($versionHandlers[$endPoint->getVersion()])) {
                $versionHandlers[$endPoint->getVersion()] = [];
            }
            $versionHandlers[$endPoint->getVersion()][] = $handler;
        }
        return $versionHandlers;
    }

    public function handleSelect(string $method, int $version, string $package, ?string $apiAction = null): void
    {
        $this->onClick($method, $version, $package, $apiAction);
    }


    private function getTemplateFilePath(): string
    {
        return __DIR__ . '/api_listing.latte';
    }
}
