<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Recette;
use App\Form\FavorisType;
use App\Repository\FavorisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favoris')]
final class FavorisController extends AbstractController
{
    #[Route('/add', name: 'app_favoris_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {   
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        $recetteId = $request->query->get('recetteId');
        $userId = $this->getUser();

        $recette = $entityManager->getRepository(Recette::class)->find($recetteId);

        if (!$recette) {
            throw $this->createNotFoundException('Recette non trouvée');
        }

        $existingFavori = $entityManager->getRepository(Favoris::class)->findOneBy([
            'recetteId' => $recette,
            'userId' => $userId,
        ]);

        if ($existingFavori) {
            $entityManager->remove($existingFavori);
        } else {
            $favori = new Favoris();
            $favori->setRecetteId($recette);
            $favori->setUserId($this->getUser());
            $entityManager->persist($favori);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(name: 'app_favoris_index', methods: ['GET'])]
    public function index(FavorisRepository $favorisRepository): Response
    {
        return $this->render('favoris/index.html.twig', [
            'favoris' => $favorisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_favoris_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $favori = new Favoris();
        $form = $this->createForm(FavorisType::class, $favori);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($favori);
            $entityManager->flush();
             return $this->redirectToRoute('app_favoris_index', [], Response::HTTP_SEE_OTHER);
         }

        return $this->render('favoris/new.html.twig', [
            'favori' => $favori,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_favoris_show', methods: ['GET'])]
    public function show(Favoris $favori): Response
    {
        return $this->render('favoris/show.html.twig', [
            'favori' => $favori,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_favoris_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Favoris $favori, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FavorisType::class, $favori);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_favoris_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('favoris/edit.html.twig', [
            'favori' => $favori,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_favoris_delete', methods: ['POST'])]
    public function delete(Request $request, Favoris $favori, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$favori->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_favoris_index', [], Response::HTTP_SEE_OTHER);
    }
}
