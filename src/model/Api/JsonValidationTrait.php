<?php

namespace Crm\ApiModule\Api;

use JsonSchema\Validator;
use Nette\Http\Response;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;

trait JsonValidationTrait
{
    public function validateInput($jsonSchemaPath): JsonValidationResult
    {
        $request = file_get_contents('php://input');

        if (empty($request)) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Empty request']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return JsonValidationResult::error($response);
        }

        try {
            $json = Json::decode($request);
            $schema = Json::decode(file_get_contents($jsonSchemaPath));

            $validator = new Validator();
            $validator->validate($json, (object) $schema);

            if (!$validator->isValid()) {
                $data = ['status' => 'error', 'message' => 'Payload error', 'errors' => []];
                foreach ($validator->getErrors() as $error) {
                    $data['errors'][] = $error['message'];
                }
                Debugger::log('Cannot parse - ' . $request . ' -> ' . implode(', ', $data['errors']));
                $response = new JsonResponse($data);
                $response->setHttpCode(Response::S400_BAD_REQUEST);
                return JsonValidationResult::error($response);
            }

            return JsonValidationResult::json($json);
        } catch (JsonException $e) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Malformed JSON. (error: ' . $e->getMessage() . ')']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return JsonValidationResult::error($response);
        }
    }
}
