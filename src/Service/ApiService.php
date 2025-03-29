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
     * 
     * @param int $productId ID du produit
     * @param int $quantity Changement de quantité
     * @param string $operation Type d'opération (ADD, REMOVE, SET)
     * @return array Réponse de l'API
     */
    public function updateStock(int $productId, int $quantity, string $operation)
    {
        // D'abord obtenir les données actuelles du produit
        $productData = $this->getProduct($productId);
        
        if (isset($productData['success']) && $productData['success'] === false) {
            return $productData; // Retourner l'erreur
        }
        
        // Calculer la nouvelle quantité en fonction de l'opération
        $newQuantity = $productData['quantity'];
        $operation = strtoupper($operation);
        
        switch ($operation) {
            case 'ADD':
                $newQuantity += $quantity;
                break;
            case 'REMOVE':
                $newQuantity -= $quantity;
                if ($newQuantity < 0) {
                    return [
                        'success' => false,
                        'error' => 'Invalid Operation',
                        'message' => 'Impossible de retirer plus d\'items que disponible en stock. Stock actuel: ' . $productData['quantity']
                    ];
                }
                break;
            case 'SET':
                $newQuantity = $quantity;
                break;
            default:
                return [
                    'success' => false,
                    'error' => 'Invalid Operation',
                    'message' => 'Type d\'opération non reconnu: ' . $operation
                ];
        }
        
        // Mettre à jour les données du produit avec la nouvelle quantité
        $productData['quantity'] = $newQuantity;
        
        // Journaliser les données pour débogage
        error_log('[Stock Update] Opération: ' . $operation . ', Quantité: ' . $quantity . ', Nouvelle quantité: ' . $newQuantity);
        error_log('[Stock Update] Données envoyées à l\'API: ' . json_encode($productData));
        
        // Utiliser updateProduct pour mettre à jour le produit
        $response = $this->updateProduct($productId, $productData);
        
        // Journaliser la réponse pour débogage
        error_log('[Stock Update] Réponse de l\'API: ' . json_encode($response));
        
        return $response;
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
                
                // Journaliser la requête pour le débogage
                if ($method !== 'GET') {
                    error_log("Requête $method sur $endpoint: " . json_encode($data));
                }
            }
            
            // Journaliser l'URL complète pour le débogage
            error_log("URL API: {$this->apiUrl}$endpoint");

            $response = $this->httpClient->request(
                $method,
                $this->apiUrl . $endpoint,
                $options
            );

            $responseData = $response->toArray();
            
            // Journaliser pour les opérations importantes
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
                $statusCode = $response->getStatusCode();
                $content = $response->getContent(false);
                
                error_log("Réponse d'erreur (code $statusCode): $content");
                
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