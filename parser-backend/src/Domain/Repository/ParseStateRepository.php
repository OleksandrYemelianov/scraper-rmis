<?php
namespace App\Domain\Repository;

use App\Domain\Model\ParseState;

class ParseStateRepository extends AppRepository
{
    public function __construct(private readonly \PDO $pdo)
    {
        parent::__construct($this->pdo, '`parse_state`');
    }
    public function getByProfileId(int $profile_id): ParseState
    {
        $input_state = ParseState::getInstance(['profile_id' => $profile_id]);
        $data = $this->getByField($input_state, 'profile_id') ?? [];
        if (empty($data)) {
            $this->create($input_state);
            $this->getByProfileId($profile_id);
        }
        return ParseState::getInstance($data);
    }

    public function updateState(ParseState $state): void
    {
        $this->updateByField($state, 'profile_id');
    }

}
