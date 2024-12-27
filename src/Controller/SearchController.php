<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\RecetteRepository;
use App\Repository\FavorisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, RecetteRepository $recetteRepository, FavorisRepository $favorisRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        $recettesTriees = [];
        $favoris = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $selectedIngredients = $data['ingredients'];

            $ingredientIds = $selectedIngredients->map(function ($ingredient) {
                return $ingredient->getId();
            })->toArray();

            $recettes = $recetteRepository->findByIngredients($ingredientIds);

            $favoris = $favorisRepository->findAll();
            $favorisIds = array_map(fn($favori) => $favori->getRecetteId()->getId(), $favoris);

            $recettesFavoris = array_filter($recettes, fn($recette) => in_array($recette->getId(), $favorisIds));
            $recettesNonFavoris = array_filter($recettes, fn($recette) => !in_array($recette->getId(), $favorisIds));

            $recettesTriees = array_merge($recettesFavoris, $recettesNonFavoris);
        }

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'form' => $form->createView(),
            'recettes' => $recettesTriees,
            'favoris' => $favoris,
        ]);
    }
}
