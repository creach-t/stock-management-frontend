<?php

namespace App\Controller;

use App\Form\CategoryType;
use App\Model\Category;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    private $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(): Response
    {
        $categoriesData = $this->apiService->getCategories();
        
        // Vérifier si une erreur s'est produite
        $error = null;
        if (isset($categoriesData['success']) && $categoriesData['success'] === false) {
            $error = 'Erreur lors de la récupération des catégories: ' . $categoriesData['message'];
            $categoriesData = [];
        }
        
        // Convertir les données brutes en objets
        $categories = [];
        foreach ($categoriesData as $categoryData) {
            $categories[] = Category::fromArray($categoryData);
        }
        
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'error' => $error,
        ]);
    }
    
    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->apiService->createCategory($category->toArray());
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la création de la catégorie: ' . $result['message']);
            } else {
                $this->addFlash('success', 'La catégorie a été créée avec succès.');
                return $this->redirectToRoute('category_index');
            }
        }
        
        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'category_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $categoryData = $this->apiService->getCategory($id);
        
        if (isset($categoryData['success']) && $categoryData['success'] === false) {
            $this->addFlash('error', 'Erreur lors de la récupération de la catégorie: ' . $categoryData['message']);
            return $this->redirectToRoute('category_index');
        }
        
        $category = Category::fromArray($categoryData);
        
        // Récupérer les produits de cette catégorie
        $productsData = $this->apiService->getProductsByCategory($id);
        $products = [];
        
        if (!(isset($productsData['success']) && $productsData['success'] === false)) {
            foreach ($productsData as $productData) {
                $products[] = $productData;
            }
        }
        
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $categoryData = $this->apiService->getCategory($id);
        
        if (isset($categoryData['success']) && $categoryData['success'] === false) {
            $this->addFlash('error', 'Erreur lors de la récupération de la catégorie: ' . $categoryData['message']);
            return $this->redirectToRoute('category_index');
        }
        
        $category = Category::fromArray($categoryData);
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->apiService->updateCategory($id, $category->toArray());
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la mise à jour de la catégorie: ' . $result['message']);
            } else {
                $this->addFlash('success', 'La catégorie a été mise à jour avec succès.');
                return $this->redirectToRoute('category_index');
            }
        }
        
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'category_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $result = $this->apiService->deleteCategory($id);
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la suppression de la catégorie: ' . $result['message']);
            } else {
                $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
            }
        }
        
        return $this->redirectToRoute('category_index');
    }
}