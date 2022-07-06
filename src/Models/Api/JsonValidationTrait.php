<?php

namespace Crm\ApiModule\Api;

use JsonSchema\Validator;
use Nette\Http\Response;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tracy\Debugger;

trait JsonValidationTrait
{
    public function validateInput(string $jsonSchemaPath, ?string $request = null): JsonValidationResult
    {
        if (empty($request)) {
            $request = file_get_contents('php://input');
        }
        if (empty($request)) {
            $response = new JsonApiResponse(Response::S400_BAD_REQUEST, ['status' => 'error', 'message' => 'Empty request']);
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
                    $data['errors'][] = [
                        $error['property'] => sprintf("%s: %s", $error['message'], $error['constraint']),
                    ];
                }
                Debugger::log('Cannot parse request. Errors: ' . print_r($data['errors'], true) . '. Request: [' . $request . ']');
                $response = new JsonApiResponse(Response::S400_BAD_REQUEST, $data);
                return JsonValidationResult::error($response);
            }

            return JsonValidationResult::json($json);
        } catch (JsonException $e) {
            $response = new JsonApiResponse(Response::S400_BAD_REQUEST, ['status' => 'error', 'message' => 'Malformed JSON', 'errors' => [$e->getMessage()]]);
            return JsonValidationResult::error($response);
        }
    }
}
