<?php

namespace App\Controller;

use App\Model\Product;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        // Récupérer les statistiques depuis l'API
        $productsData = $this->apiService->getProducts();
        $categoriesData = $this->apiService->getCategories();
        $lowStockProductsData = $this->apiService->getLowStockProducts();
        
        // Vérifier si une erreur s'est produite lors de la récupération des données
        $error = null;
        if (isset($productsData['success']) && $productsData['success'] === false) {
            $error = 'Erreur lors de la récupération des produits: ' . $productsData['message'];
        }
        
        // Convertir les données brutes en objets
        $products = [];
        $lowStockProducts = [];
        $productCount = 0;
        $categoryCount = 0;
        $totalStock = 0;
        
        if (!$error) {
            // Compter les produits
            $productCount = count($productsData);
            
            // Compter les catégories
            $categoryCount = count($categoriesData);
            
            // Calculer le stock total
            foreach ($productsData as $productData) {
                $product = Product::fromArray($productData);
                $products[] = $product;
                $totalStock += $product->getQuantity();
            }
            
            // Obtenir les produits à faible stock
            foreach ($lowStockProductsData as $productData) {
                $lowStockProducts[] = Product::fromArray($productData);
            }
        }
        
        return $this->render('dashboard/index.html.twig', [
            'products' => $products,
            'productCount' => $productCount,
            'categoryCount' => $categoryCount,
            'totalStock' => $totalStock,
            'lowStockProducts' => $lowStockProducts,
            'error' => $error,
        ]);
    }
}