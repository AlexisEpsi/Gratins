<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\Commentaire;
use App\Entity\RecetteIngredient;
use App\Form\RecetteType;
use App\Form\CommentaireType;
use App\Repository\RecetteRepository;
use App\Repository\IngredientRepository;
use App\Repository\FavorisRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recette')]
final class RecetteController extends AbstractController
{
    #[Route(name: 'app_recette_index', methods: ['GET'])]
    public function index(RecetteRepository $recetteRepository, FavorisRepository $favorisRepository): Response
    {
        $favoris = $favorisRepository->findAll();
        $favorisIds = array_map(fn($favori) => $favori->getRecetteId()->getId(), $favoris);
        
        $recettes = $recetteRepository->findAll();
        
        $recettesFavoris = array_filter($recettes, fn($recette) => in_array($recette->getId(), $favorisIds));
        $recettesNonFavoris = array_filter($recettes, fn($recette) => !in_array($recette->getId(), $favorisIds));
        
        $recettesTriees = array_merge($recettesFavoris, $recettesNonFavoris);

        return $this->render('recette/index.html.twig', [
            'recettes' => $recettesTriees,
            'favoris' => $favoris,
        ]);
    }

    #[Route('/new', name: 'app_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_recette_index');
        }
    
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('images_directory'), $filename);
                $recette->setImage($filename);
            }

            $recette->setUserId($this->getUser());
            $entityManager->persist($recette);
            $entityManager->flush();

            $quantites = $request->request->all('quantites');
            

            foreach ($quantites as $idIngredient => $quantite) {
                if ($quantite > 0) {

                    $recetteIngredient = new RecetteIngredient();
                    $recetteIngredient->setRecetteId($recette);
                    $ingredient = $ingredientRepository->find($idIngredient);
                    $recetteIngredient->setIngredientId($ingredient);
                    $recetteIngredient->setQuantite($quantite);

                    $entityManager->persist($recetteIngredient);
                }
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form,
            'ingredients' => $ingredientRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_recette_show', methods: ['GET', 'POST'])]
    public function show(Recette $recette, EntityManagerInterface $entityManager, CommentaireRepository $commentaireRepository, $id, Request $request): Response
    {
        $ingredients = $recette->getIngredients();
        $commentaires = $commentaireRepository->findBy(['recetteId' => $id]);

        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setDate(new \DateTime('now'));
            $commentaire->setUserId($this->getUser());
            $commentaire->setRecetteId($recette);

            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_recette_show', ['id' => $recette->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/show.html.twig', [
            'recette' => $recette,
            'ingredients' => $ingredients,
            'commentaires' => $commentaires,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository): Response
    {
        if (!$this->getUser() || $recette->getUserId() !== $this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('images_directory'), $filename);
                $recette->setImage($filename);
            }

            $quantites = $request->request->all('newQuantites');

            $quantitesBDD = [];
            foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                $ingredientId = $recetteIngredient->getIngredientId()->getId();
                $quantitesBDD[$ingredientId] = $recetteIngredient->getQuantite();
            }

            foreach ($quantites as $ingredientId => $quantite) {
                if (!isset($quantitesBDD[$ingredientId]) && $quantite > 0) {
                    $recetteIngredient = new RecetteIngredient();
                    $recetteIngredient->setRecetteId($recette);
                    $recetteIngredient->setIngredientId($entityManager->getRepository(Ingredient::class)->find($ingredientId));
                    $recetteIngredient->setQuantite((int)$quantite);
                    $entityManager->persist($recetteIngredient);
                } elseif (isset($quantitesBDD[$ingredientId]) && $quantitesBDD[$ingredientId] != $quantite) {
                    $recetteIngredient = $entityManager->getRepository(RecetteIngredient::class)->findOneBy([
                        'recetteId' => $recette,
                        'ingredientId' => $ingredientId,
                    ]);
                    if ($recetteIngredient) {
                        $recetteIngredient->setQuantite((int)$quantite);
                        $entityManager->persist($recetteIngredient);
                    }
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        $ingredients = $recette->getIngredients();

        $quantites = [];
        foreach ($ingredients as $ingredient) {
            $recetteIngredient = $recette->getRecetteIngredients()->filter(function($ri) use ($ingredient) {
                return $ri->getIngredientId() === $ingredient;
            })->first();
            if ($recetteIngredient) {
                $quantites[] = $recetteIngredient->getQuantite();
            }
        }

        return $this->render('recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form,
            'ingredients' => $ingredientRepository->findAll(),
            'quantites' => $quantites,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->getPayload()->getString('_token'))) {
            
            $recetteIngredients = $entityManager->getRepository(RecetteIngredient::class)->findBy(['recetteId' => $recette]);
            foreach ($recetteIngredients as $recetteIngredient) {
                $entityManager->remove($recetteIngredient);
            }
            $entityManager->remove($recette);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
    }
}
