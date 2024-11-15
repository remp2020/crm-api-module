<?php

namespace Crm\ApiModule\Repositories;

use Crm\ApiModule\Events\BeforeRemoveApiTokenEvent;
use Crm\ApiModule\Models\Exception\CantDeleteActiveTokenException;
use Crm\ApplicationModule\Models\Database\Repository;
use Crm\UsersModule\Models\Auth\Access\TokenGenerator;
use DateTime;
use League\Event\Emitter;
use Nette\Caching\Storage;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Tomaj\NetteApi\Misc\TokenRepositoryInterface;

class ApiTokensRepository extends Repository implements TokenRepositoryInterface
{
    protected $tableName = 'api_tokens';

    public function __construct(
        Explorer $database,
        Storage $cacheStorage = null,
        private readonly Emitter $emitter
    ) {
        parent::__construct($database, $cacheStorage);
    }

    final public function all()
    {
        return $this->getTable()->order('created_at DESC');
    }

    final public function generate($name, $ipRestrictions = '*', $active = true)
    {
        $token = TokenGenerator::generate();
        return $this->insert([
            'name' => $name,
            'token' => $token,
            'ip_restrictions' => $ipRestrictions,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'active' => $active,
        ]);
    }

    final public function update(ActiveRow &$row, $data)
    {
        $data['updated_at'] = new DateTime();
        return parent::update($row, $data);
    }

    final public function findToken($token)
    {
        $tokenRow = $this->getTable()->where('token', $token)->fetch();
        if (!$tokenRow) {
            return false;
        }
        return $tokenRow;
    }

    final public function validToken(string $token): bool
    {
        return true;
    }

    final public function ipRestrictions(string $token): ?string
    {
        return '*';
    }

    final public function delete(ActiveRow &$row): bool
    {
        if ($row->active) {
            throw new CantDeleteActiveTokenException();
        }

        return $this->getTransaction()->wrap(function () use ($row) {
            $this->emitter->emit(new BeforeRemoveApiTokenEvent($row));

            $row->related('api_token_meta')->delete();
            $row->related('api_access_tokens')->delete();
            $row->related('api_token_stats')->delete();
            return parent::delete($row);
        });
    }
}
