<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\AbilityRepository;
use App\Repository\TeamRepository;
use App\Repository\TypeRepository;
use App\Service\PokemonService;
use App\Service\TeamService;
use App\Service\TypeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class TeamController extends AbstractController
{
    #[Route('/team', name: 'homepage_team')]
    public function index(Request $request): Response
    {
        return $this->redirectToRoute('app_default');
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface|InvalidArgumentException
     */
    #[Route('/team/create', name: 'create_team')]
    public function createTeam(
        Request $request,
        EntityManagerInterface $entityManager,
        TypeRepository $typeRepository,
        AbilityRepository $abilityRepository
    ): Response {
        $team = new Team();
        $team->setName('');
        $team->setCreatedAt(new \DateTime());
        $team->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();
            $pokemonList = $form->get('ajaxString')->getData();
            $pokemonService = new PokemonService(HttpClient::create(), $entityManager, $typeRepository,
                $abilityRepository);
            $pokemonService->addPokemonList($team, $pokemonList);
            $this->addFlash(
                'success',
                'Your changes were saved!'
            );
            return $this->redirectToRoute('list_teams');
        }
        return $this->render('team/create.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @throws InvalidArgumentException
     */
    #[Route('/team/list', name: 'list_teams')]
    public function listTeams(TeamService $teamService, TypeService $typeService): Response
    {
        $teams = $teamService->getTeamsCache();
        $types = $typeService->getTypeCached();

        return $this->render('team/list.html.twig', [
            'teams' => $teams,
            'types' => $types
        ]);
    }

    #[Route('/team/{teamId}/edit', name: 'edit_team')]
    public function editTeam(
        int $teamId,
        Request $request,
        EntityManagerInterface $entityManager,
        TeamRepository $teamRepository
    ): Response {
        $team = $teamRepository->find($teamId);
        $form = $this->createForm(TeamType::class, $team);
        $form->remove('created_at');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();
            $entityManager->persist($team);
            $entityManager->flush();
            return $this->redirectToRoute('list_teams');
        }
        return $this->render('team/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/team/{teamId}/delete', name: 'delete_team')]
    public function deleteTeam(
        int $teamId,
        Request $request,
        EntityManagerInterface $entityManager,
        TeamRepository $teamRepository
    ): Response {
        $team = $teamRepository->find($teamId);
        if ($team == null)
            return $this->redirectToRoute('list_teams');

        $pokemons = $team->getPokemon();
        foreach ( $pokemons as $pokemon ){
            if($pokemon->getTeam() == $team){
                $pokemon->setTeam(null);
                $team->removePokemon($pokemon);
            }
        }
        $teamRepository->remove($team, true);
        $entityManager->persist($team);
        $entityManager->flush();

        return $this->redirectToRoute('list_teams');
    }

}
