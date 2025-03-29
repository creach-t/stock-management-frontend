<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

class ApiService
{
    private $httpClient;
    private $apiUrl;

    public function __construct(HttpClientInterface $httpClient, string $apiUrl)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Récupère tous les produits
     */
    public function getProducts()
    {
        return $this->makeRequest('GET', '/products/all');
    }

    /**
     * Récupère les produits à faible stock
     */
    public function getLowStockProducts()
    {
        return $this->makeRequest('GET', '/products/low-stock');
    }

    /**
     * Récupère un produit par son ID
     */
    public function getProduct(int $id)
    {
        return $this->makeRequest('GET', '/products/' . $id);
    }

    /**
     * Crée un nouveau produit
     */
    public function createProduct(array $productData)
    {
        return $this->makeRequest('POST', '/products', $productData);
    }

    /**
     * Met à jour un produit existant
     */
    public function updateProduct(int $id, array $productData)
    {
        return $this->makeRequest('PUT', '/products/' . $id, $productData);
    }

    /**
     * Supprime un produit
     */
    public function deleteProduct(int $id)
    {
        return $this->makeRequest('DELETE', '/products/' . $id);
    }

    /**
     * Met à jour le stock d'un produit
     */
    public function updateStock(int $productId, int $quantity, string $operation)
    {
        // Enregistrer les données pour débogage
        $logData = [
            'productId' => $productId,
            'quantityChange' => $quantity,
            'operationType' => $operation
        ];
        error_log('Données envoyées à l\'API: ' . json_encode($logData));

        // S'assurer que l'opération est en majuscule
        $data = [
            'productId' => $productId,
            'quantityChange' => $quantity,
            'operationType' => strtoupper($operation)
        ];
        
        return $this->makeRequest('PATCH', '/products/stock', $data);
    }

    /**
     * Récupère toutes les catégories
     */
    public function getCategories()
    {
        return $this->makeRequest('GET', '/categories');
    }

    /**
     * Récupère une catégorie par son ID
     */
    public function getCategory(int $id)
    {
        return $this->makeRequest('GET', '/categories/' . $id);
    }

    /**
     * Crée une nouvelle catégorie
     */
    public function createCategory(array $categoryData)
    {
        return $this->makeRequest('POST', '/categories', $categoryData);
    }

    /**
     * Met à jour une catégorie existante
     */
    public function updateCategory(int $id, array $categoryData)
    {
        return $this->makeRequest('PUT', '/categories/' . $id, $categoryData);
    }

    /**
     * Supprime une catégorie
     */
    public function deleteCategory(int $id)
    {
        return $this->makeRequest('DELETE', '/categories/' . $id);
    }

    /**
     * Recherche des produits
     */
    public function searchProducts(string $term)
    {
        return $this->makeRequest('GET', '/products/search?term=' . urlencode($term));
    }

    /**
     * Récupère les produits d'une catégorie
     */
    public function getProductsByCategory(int $categoryId)
    {
        return $this->makeRequest('GET', '/products/category/' . $categoryId);
    }

    /**
     * Méthode générique pour effectuer des requêtes API
     */
    private function makeRequest(string $method, string $endpoint, array $data = [])
    {
        try {
            $options = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ];
            
            if (!empty($data)) {
                $options['json'] = $data;
                // Log des données envoyées pour les requêtes autres que GET
                if ($method !== 'GET') {
                    error_log("Requête $method sur $endpoint: " . json_encode($data));
                }
            }

            $response = $this->httpClient->request(
                $method,
                $this->apiUrl . $endpoint,
                $options
            );

            $responseData = $response->toArray();
            
            // Log de la réponse pour débogage
            if ($method !== 'GET') {
                error_log("Réponse de $endpoint: " . json_encode($responseData));
            }
            
            return $responseData;
        } catch (ClientExceptionInterface $e) {
            // Erreur 4xx
            return $this->handleError($e, 'Client Error');
        } catch (ServerExceptionInterface $e) {
            // Erreur 5xx
            return $this->handleError($e, 'Server Error');
        } catch (RedirectionExceptionInterface $e) {
            // Redirection 3xx
            return $this->handleError($e, 'Redirection Error');
        } catch (TransportExceptionInterface $e) {
            // Erreur de transport (connexion impossible, etc.)
            return $this->handleError($e, 'Transport Error');
        } catch (\Exception $e) {
            // Toute autre erreur
            return [
                'success' => false,
                'error' => 'General Error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Gère les erreurs de l'API
     */
    private function handleError($exception, $type)
    {
        $message = $exception->getMessage();
        error_log("Erreur API ($type): $message");
        
        // Tentative de récupération des détails de l'erreur si disponible
        $details = '';
        if (method_exists($exception, 'getResponse')) {
            try {
                $response = $exception->getResponse();
                $content = $response->getContent(false);
                error_log("Contenu de la réponse d'erreur: $content");
                $data = json_decode($content, true);
                if (isset($data['message'])) {
                    $details = $data['message'];
                }
            } catch (\Exception $e) {
                // Si on ne peut pas récupérer les détails, on continue sans
                error_log("Impossible de récupérer les détails de l'erreur: " . $e->getMessage());
            }
        }
        
        return [
            'success' => false,
            'error' => $type,
            'message' => $details ?: $message
        ];
    }
}