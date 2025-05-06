<?php
namespace App\Application\Service;

use App\Application\Parser\ParsingContext;
use App\Domain\Model\Profile;
use App\Domain\Repository\ProfileRepository;
use App\Application\Parser\ProfileParsingContext;

class ProfileService {
    public function __construct(
        private ProfileRepository $profileRepository
    ) {}

    public function getAllProfiles(): array
    {
        return $this->profileRepository->getShortProfiles();
    }

    public function getProfile(int $id): ?ParsingContext
    {
        $res = $this->profileRepository->findById($id);
        return $res == null ? null : new ProfileParsingContext($res);
    }

    public function createProfile(array $data): ParsingContext
    {
        $profile = Profile::getInstance($data);
        $res = $this->profileRepository->save($profile);
        return new ProfileParsingContext($res);
    }

    public function updateProfile(int $id, array $data): ParsingContext
    {
        $profile = $this->profileRepository->findById($id);
        if (!$profile) {
            throw new \RuntimeException("Profile not found");
        }
        $data['id'] = $id;
        $profile = Profile::getInstance($data);
        $this->profileRepository->update($profile);
        return new ProfileParsingContext($profile);
    }

    public function deleteProfile(string $id): void {
        $this->profileRepository->delete($id);
    }
}
