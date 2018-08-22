<?php

namespace Crm\ApiModule\Forms;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Authorization\CsrfAuthorization;
use Crm\ApiModule\Authorization\NoAuthorization;
use Crm\ApiModule\Params\InputParam;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Crm\ApplicationModule\Api\ApiHandlerInterface;
use Crm\ApplicationModule\Api\ApiRouteInterface;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\Localization\ITranslator;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Tracy\Debugger;

class ApiTestCallFormFactory
{
    protected $apiRoutesContainer;

    /** @var ApiRouteInterface */
    private $router;

    /** @var ApiHandlerInterface */
    private $handler;

    /** @var ApiAuthorizationInterface */
    private $authorization;

    private $request;

    private $translator;

    public $onSubmit;

    public function __construct(
        ApiRoutesContainer $apiRoutesContainer,
        Request $request,
        ITranslator $translator
    ) {
        $this->apiRoutesContainer = $apiRoutesContainer;
        $this->request = $request;
        $this->translator = $translator;
    }

    /**
     * @param ApiIdentifier $identifier
     * @return Form
     */
    public function create(ApiIdentifier $identifier)
    {
        $this->router = $this->apiRoutesContainer->getRouter($identifier);
        $this->handler = $this->apiRoutesContainer->getHandler($identifier);
        $this->authorization = $this->apiRoutesContainer->getAuthorization($identifier);

        $form = new Form;

        $defaults = [];

        $form->setRenderer(new BootstrapRenderer());
        if (!isset($_POST['token_csfr'])) {
            $form->addProtection();
        }

        $url = $identifier->getApiPath();
        $form->addText('api_url', $this->translator->translate('api.admin.api_test_call_form.api_url.title'))
            ->setDefaultValue($url)
            ->setDisabled(true);

        $defaults['api_url'] = $url;

        if ($this->authorization instanceof BearerTokenAuthorization) {
            $form->addText('token', $this->translator->translate('api.admin.api_test_call_form.token.title'))
                ->setAttribute('placeholder', $this->translator->translate('api.admin.api_test_call_form.token.placeholder'));
        } elseif ($this->authorization instanceof CsrfAuthorization) {
            $form->addText('token_csfr', $this->translator->translate('api.admin.api_test_call_form.token_csfr.title'))
                ->setAttribute('placeholder', $this->translator->translate('api.admin.api_test_call_form.token_csfr.placeholder'));
        } elseif ($this->authorization instanceof NoAuthorization) {
            $form->addText('authorization', $this->translator->translate('api.admin.api_test_call_form.authorization.title'))
                ->setDisabled(true);
            $defaults['authorization'] = $this->translator->translate('api.admin.api_test_call_form.authorization.value');
        }

        $params = $this->handler->params();
        foreach ($params as $param) {
            $count = $param->isMulti() ? 5 : 1;
            for ($i = 0; $i < $count; $i++) {
                $key = $param->getKey();
                if ($param->isMulti()) {
                    $key = $key . '___' . $i;
                }
                $c = $form->addText($key, $param->getKey());
                if ($param->getAvailableValues()) {
                    $c->setOption('description', $this->translator->translate('api.admin.api_test_call_form.available_values') . ': ' . implode(' | ', $param->getAvailableValues()));
                }
            }
        }

        $form->addSubmit('send', $this->translator->translate('api.admin.api_test_call_form.submit'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-cloud-upload"></i> ' . $this->translator->translate('api.admin.api_test_call_form.submit'));

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $identifier = $this->router->getApiIdentifier();
        $uri = $this->request->getUrl();
        $scheme = $uri->scheme;
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        $url = $scheme . '://' . $uri->host . '/api' . $identifier->getApiPath();

        $token = false;
        if (isset($values['token'])) {
            $token = $values['token'];
            unset($values['token']);
        }

        $postFields = [];
        $getFields = [];

        if (isset($values['token_csfr'])) {
            $postFields[] = 'token=' . urlencode($values['token_csfr']);
            unset($values['token_csfr']);
        }

        $params = $this->handler->params();

        foreach ($values as $key => $value) {
            if (strstr($key, '___') !== false) {
                $parts = explode('___', $key);
                $key = $parts[0];
            }
            foreach ($params as $param) {
                if ($param->getKey() == $key) {
                    if (!$value) {
                        continue;
                    }
                    if ($param->isMulti()) {
                        $valueKey = '';
                        if (strstr($value, '=') !== false) {
                            $parts = explode('=', $value);
                            $valueKey = $parts[0];
                            $value = $parts[1];
                        }
                        $valueData = $key . "[$valueKey]=$value";
                    } else {
                        $valueData = "$key=$value";
                    }

                    if ($param->getType() == InputParam::TYPE_POST) {
                        $postFields[] = $valueData;
                    } else {
                        $getFields[] = $valueData;
                    }
                }
            }
        }

        if (count($getFields)) {
            $url = $url . '?' . implode('&', $getFields);
        }

        Debugger::timer();

        $result = 'Requesting url: ' . $url . "\n";
        $result .= "POST Params:\n\t";
        $result .= implode("\n\t", $postFields);
        $result .= "\n";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($curl, CURLOPT_POST, count($values));
        curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $postFields));
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        if ($token) {
            $headers = ['Authorization: Bearer ' . $token];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result .= implode("\n", $headers);
        }

        $result .= "\n\n--------------------------------------------------------\n\n";

        $responseBody = curl_exec($curl);

        $result .= "\n";

        $elapsed = intval(Debugger::timer() * 1000);
        $result .= "Took: {$elapsed}ms\n";

        $curlErrorNumber = curl_errno($curl);
        $curlError = curl_error($curl);
        if ($curlErrorNumber > 0) {
            $result .= 'HTTP Error: ' . $curlError . "\n";
        } else {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $result .= "HTTP code: $httpcode\n\n";

            $body = $responseBody;
            $decoded = json_decode($body);
            if ($decoded) {
                $body = json_encode($decoded, JSON_PRETTY_PRINT);
            }

            $result .= "Result:\n\n{$body}\n\n";
        }

        $this->onSubmit->__invoke($form, $result);
    }
}
