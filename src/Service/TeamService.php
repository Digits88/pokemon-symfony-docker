<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Repository\TeamRepository;

class TeamService
{

    private FilesystemAdapter $cache;
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->cache = new FilesystemAdapter();
        $this->teamRepository = $teamRepository;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTeamsCache()
    {
        $value = $this->cache->get('team_key', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            $teams = $this->teamRepository->findBy(
                array(),
                array('created_at' => 'ASC')
            );
            foreach ($teams as $team) {
                $team->typeList = $team->getAllPokemonType();
            }
            return $teams;
        });
        $this->cache->delete('team_key');
        return $value;
    }
}
