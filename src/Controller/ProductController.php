<?php

namespace App\Controller;

use App\Form\ProductType;
use App\Form\StockUpdateType;
use App\Model\Product;
use App\Model\StockUpdate;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    private $apiService;
    
    // Nombre fixe d'éléments par page
    private const ITEMS_PER_PAGE = 10;
    // Ordre de tri par défaut
    private const DEFAULT_SORT = 'ASC';
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    #[Route('/', name: 'product_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Paramètres de pagination (fixés à 10 éléments par page et ordre ascendant)
        $page = max(0, (int)$request->query->get('page', 0));
        $size = self::ITEMS_PER_PAGE;
        $sort = self::DEFAULT_SORT;
        
        // Récupération des paramètres de filtrage
        $searchTerm = $request->query->get('search');
        $categoryId = $request->query->get('category');
        
        // Par défaut, on récupère tous les produits
        $productsData = null;
        $totalElements = 0;
        $totalPages = 0;
        
        // Si on a un terme de recherche (même vide) 
        if ($searchTerm !== null) {
            // Si le terme est vide mais qu'on a une catégorie, on utilise getProductsByCategory avec pagination
            if (empty($searchTerm) && $categoryId) {
                $response = $this->apiService->getProductsByCategory($categoryId, $page, $size, $sort, true);
                if (is_array($response) && isset($response['content'])) {
                    $productsData = $response['content'];
                    $totalElements = $response['totalElements'] ?? 0;
                    $totalPages = $response['totalPages'] ?? 0;
                } else {
                    $productsData = $response;
                }
            } else {
                // Sinon on utilise la recherche standard
                $response = $this->apiService->searchProducts($searchTerm, $page, $size, $sort, true);
                if (is_array($response) && isset($response['content'])) {
                    $productsData = $response['content'];
                    $totalElements = $response['totalElements'] ?? 0;
                    $totalPages = $response['totalPages'] ?? 0;
                } else {
                    $productsData = $response;
                }
            }
        } 
        // Si on a seulement une catégorie
        elseif ($categoryId) {
            $response = $this->apiService->getProductsByCategory($categoryId, $page, $size, $sort, true);
            if (is_array($response) && isset($response['content'])) {
                $productsData = $response['content'];
                $totalElements = $response['totalElements'] ?? 0;
                $totalPages = $response['totalPages'] ?? 0;
            } else {
                $productsData = $response;
            }
        } 
        // Sinon on récupère tous les produits
        else {
            $productsData = $this->apiService->getProducts();
        }
        
        // Vérifier si une erreur s'est produite
        $error = null;
        if (isset($productsData['success']) && $productsData['success'] === false) {
            $error = 'Erreur lors de la récupération des produits: ' . $productsData['message'];
            $productsData = [];
        }
        
        // Convertir les données brutes en objets
        $products = [];
        foreach ($productsData as $productData) {
            $products[] = Product::fromArray($productData);
        }
        
        // Récupérer les catégories pour le filtre
        $categoriesData = $this->apiService->getCategories();
        $categories = [];
        
        if (!(isset($categoriesData['success']) && $categoriesData['success'] === false)) {
            foreach ($categoriesData as $categoryData) {
                $categories[$categoryData['name']] = $categoryData['id'];
            }
        }
        
        // Déterminer si la pagination doit être affichée (seulement s'il y a plus d'une page)
        $showPagination = $totalPages > 1;
        
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'searchTerm' => $searchTerm,
            'selectedCategory' => $categoryId,
            'currentPage' => $page,
            'pageSize' => $size,
            'sort' => $sort,
            'isPaginationActive' => ($searchTerm !== null || $categoryId),
            'showPagination' => $showPagination,
            'totalPages' => $totalPages,
            'error' => $error,
        ]);
    }
    
    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->apiService->createProduct($product->toArray());
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la création du produit: ' . $result['message']);
            } else {
                $this->addFlash('success', 'Le produit a été créé avec succès.');
                return $this->redirectToRoute('product_index');
            }
        }
        
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $productData = $this->apiService->getProduct($id);
        
        if (isset($productData['success']) && $productData['success'] === false) {
            $this->addFlash('error', 'Erreur lors de la récupération du produit: ' . $productData['message']);
            return $this->redirectToRoute('product_index');
        }
        
        $product = Product::fromArray($productData);
        
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $productData = $this->apiService->getProduct($id);
        
        if (isset($productData['success']) && $productData['success'] === false) {
            $this->addFlash('error', 'Erreur lors de la récupération du produit: ' . $productData['message']);
            return $this->redirectToRoute('product_index');
        }
        
        $product = Product::fromArray($productData);
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->apiService->updateProduct($id, $product->toArray());
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la mise à jour du produit: ' . $result['message']);
            } else {
                $this->addFlash('success', 'Le produit a été mis à jour avec succès.');
                return $this->redirectToRoute('product_index');
            }
        }
        
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'product_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $result = $this->apiService->deleteProduct($id);
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la suppression du produit: ' . $result['message']);
            } else {
                $this->addFlash('success', 'Le produit a été supprimé avec succès.');
            }
        }
        
        return $this->redirectToRoute('product_index');
    }
    
    #[Route('/{id}/stock', name: 'product_stock', methods: ['GET', 'POST'])]
    public function stock(Request $request, int $id): Response
    {
        $productData = $this->apiService->getProduct($id);
        
        if (isset($productData['success']) && $productData['success'] === false) {
            $this->addFlash('error', 'Erreur lors de la récupération du produit: ' . $productData['message']);
            return $this->redirectToRoute('product_index');
        }
        
        $product = Product::fromArray($productData);
        
        $stockUpdate = new StockUpdate();
        $stockUpdate->setProductId($id);
        
        $form = $this->createForm(StockUpdateType::class, $stockUpdate);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->apiService->updateStock(
                $stockUpdate->getProductId(),
                $stockUpdate->getQuantityChange(),
                $stockUpdate->getOperationType()
            );
            
            if (isset($result['success']) && $result['success'] === false) {
                $this->addFlash('error', 'Erreur lors de la mise à jour du stock: ' . $result['message']);
            } else {
                $this->addFlash('success', 'Le stock a été mis à jour avec succès.');
                return $this->redirectToRoute('product_show', ['id' => $id]);
            }
        }
        
        return $this->render('product/stock.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
}