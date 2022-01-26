<?php

namespace Crm\ApiModule\Populator;

use Crm\ApplicationModule\Populator\AbstractPopulator;
use Crm\UsersModule\Auth\Access\TokenGenerator;

class ApiTokensPopulator extends AbstractPopulator
{
    /**
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     */
    public function seed($progressBar)
    {
        $apiLogs = $this->database->table('api_logs');
        $apiStats = $this->database->table('api_token_stats');
        $apiTokens = $this->database->table('api_tokens');

        for ($i = 0; $i < $this->count; $i++) {
            $data = [
                'name' => $this->faker->name,
                'token' => TokenGenerator::generate(),
                'ip_restrictions' => $this->getIpRestriction(),
                'created_at' => $this->faker->dateTimeBetween('-1 years'),
                'updated_at' => $this->faker->dateTimeBetween('-1 years'),
                'active' => $this->faker->boolean(80),
            ];

            $apiToken = $apiTokens->insert($data);

            if ($this->faker->boolean(80)) {
                $apiStats->insert([
                    'token_id' => $apiToken->id,
                    'calls' => $this->faker->numberBetween(0, 10000),
                    'last_call' => $this->faker->dateTimeBetween('-1 years'),
                ]);
            }

            $logsCount = rand(0, 100);
            for ($i = 0; $i < $logsCount; $i++) {
                $apiLogs->insert([
                    'token' => $data['token'],
                    'path' => '/' . $this->faker->slug,
                    'input' => '{some: jsonvalue, with: [1, 2, 3]}',
                    'response_code' => $this->getResponseCode(),
                    'response_time' => $this->faker->numberBetween(0, 1000),
                    'created_at' => $this->faker->dateTimeBetween('-1 years'),
                    'ip' => $this->faker->ipv4,
                    'user_agent' => $this->faker->userAgent,
                ]);
            }

            $progressBar->advance();
        }
    }

    private function getIpRestriction()
    {
        $restrictions = ['*', '10, 27.0.0.1', $this->faker->ipv4, "{$this->faker->ipv4},{$this->faker->ipv4}", "{$this->faker->ipv4},{$this->faker->ipv4},{$this->faker->ipv4}"];
        return $restrictions[ rand(0, count($restrictions) - 1) ];
    }

    private function getResponseCode()
    {
        $codes = [200, 403, 500];
        return $codes[ rand(0, count($codes) - 1) ];
    }
}
