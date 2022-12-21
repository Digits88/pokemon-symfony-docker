<?php

namespace App\Service;

use App\Entity\Ability;
use App\Entity\Pokemon;
use App\Entity\Type;
use App\Repository\AbilityRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonService
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private TypeRepository $typeRepository;
    private AbilityRepository $abilityRepository;
    private FilesystemAdapter $cache;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager, TypeRepository $typeRepository ,AbilityRepository $abilityRepository)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->typeRepository = $typeRepository;
        $this->abilityRepository = $abilityRepository;
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function newPokemon(): Pokemon
    {
        $randomPokemon = $this->getRandomPokemon();
        $pokemon = new Pokemon();
        $pokemon->setName($randomPokemon['name']);
        $pokemon->setBaseExperience($randomPokemon['base_experience']);
        $pokemon->setSprite($randomPokemon['sprites']['front_default']);
        foreach ($randomPokemon['abilities'] as $abilityArray) {
            $ability = new Ability();
            $ability->setName($abilityArray['ability']['name']);
            $ability->addPokemon($pokemon);
            $pokemon->addAbility($ability);
        }
        foreach ($randomPokemon['types'] as $typeArray) {
            $type = new Type();
            $type->setName($typeArray['type']['name']);
            $type->addPokemon($pokemon);
            $pokemon->addType($type);
        }
        return $pokemon;
    }

    /**
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    private function getRandomPokemon(): array
    {
        $randId = rand(1, 905);
        $response = $this->client->request(
            'GET',
            'https://pokeapi.co/api/v2/pokemon/' . $randId . '/'
        );
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $response->toArray();
        } else {
            throw new Exception('Error with pokemon API ' . $statusCode);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addPokemonList($team, $pokemonJsonList): void
    {
        //$serializer->deserialize($serializedEntity, Pokemon::class, 'json');
        //can't use because I didn't pass "abilities" value for class "App\Entity\Pokemon": Expected argument of type "App\Entity\Ability", "array" given at property path "abilities".
        $pokemonJsonArray = json_decode("[".$pokemonJsonList."]");
        foreach ($pokemonJsonArray as $pokemonArray) {
            $pokemon = new Pokemon();
            $pokemon->setName($pokemonArray->name);
            $pokemon->setBaseExperience($pokemonArray->baseExperience);
            $pokemon->setSprite($pokemonArray->sprite);
            $this->entityManager->persist($pokemon);
            foreach ($pokemonArray->abilities as $abilityArray) {
                $ability = $this->abilityRepository->findOneBy(['name' => $abilityArray->name]);
                if ($ability == null) {
                    $ability = new Ability();
                    $ability->setName($abilityArray->name);
                }
                $ability->addPokemon($pokemon);
                $this->entityManager->persist($ability);
                $this->entityManager->flush();
                $pokemon->addAbility($ability);
            }
            foreach ($pokemonArray->types as $typeArray) {
                $type = $this->typeRepository->findOneBy(['name' => $typeArray->name]);
                if ($type == null) {
                    $type = new Type();
                    $type->setName( $typeArray->name);
                }
                $type->addPokemon($pokemon);
                $this->entityManager->persist($type);
                $this->entityManager->flush();
                $pokemon->addType($type);
            }
            $this->entityManager->persist($pokemon);
            $this->entityManager->flush();
            $team->addPokemon($pokemon);
        }
        $this->entityManager->persist($team);
        $this->entityManager->flush();
        $this->cache->delete('team_key');
        $this->cache->delete('type_key');
    }

}
