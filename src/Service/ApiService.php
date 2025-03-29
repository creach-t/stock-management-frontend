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
        // Construire les données spécifiques pour l'endpoint PATCH /products/stock
        $data = [
            'productId' => $productId,
            'quantityChange' => $quantity,
            'operationType' => strtoupper($operation),
            'notes' => 'Mise à jour depuis l\'interface' // Optionnel, comme indiqué dans votre exemple
        ];
        
        // Journaliser les données pour débogage
        error_log('[Stock Update] Données envoyées à l\'API: ' . json_encode($data));
        
        $response = $this->makeRequest('PATCH', '/products/stock', $data);
        
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
     * 
     * @param int $categoryId L'ID de la catégorie
     * @param int $page La page à afficher (par défaut: 0)
     * @param int $size Le nombre d'éléments par page (par défaut: 20)
     * @param string $sort Le tri (par défaut: ASC)
     * @return array Les produits de la catégorie
     */
    public function getProductsByCategory(int $categoryId, int $page = 0, int $size = 20, string $sort = 'ASC')
    {
        return $this->makeRequest('GET', '/products/category/' . $categoryId . '?page=' . $page . '&size=' . $size . '&sort=' . $sort);
    }

    /**
     * Méthode générique pour effectuer des requêtes API
     */
    private function makeRequest(string $method, string $endpoint, array $data = [])
    {
        try {
            $options = [
                'headers' => [
                    'accept' => '*/*', 
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
            $fullUrl = $this->apiUrl . $endpoint;
            error_log("URL API: $fullUrl");

            $response = $this->httpClient->request(
                $method,
                $fullUrl,
                $options
            );

            // Vérifier si la réponse a un contenu
            $statusCode = $response->getStatusCode();
            
            // Traiter spécifiquement les réponses de suppression réussies (204 No Content)
            if ($statusCode === 204 || ($method === 'DELETE' && $statusCode >= 200 && $statusCode < 300)) {
                error_log("Opération de suppression réussie avec code HTTP: " . $statusCode);
                return [
                    'success' => true,
                    'message' => 'Opération réussie'
                ];
            }
            
            // Pour les autres types de réponses, vérifier s'il y a du contenu
            try {
                $responseData = $response->toArray(false);
                
                // Journaliser pour les opérations importantes
                if ($method !== 'GET') {
                    error_log("Réponse HTTP: " . $statusCode);
                    error_log("Réponse de $endpoint: " . json_encode($responseData));
                }
                
                return $responseData;
            } catch (\Exception $e) {
                // Si on ne peut pas parser la réponse en JSON (par exemple, si elle est vide)
                // mais que le code HTTP est un succès, on considère l'opération comme réussie
                if ($statusCode >= 200 && $statusCode < 300) {
                    error_log("Réponse non-JSON mais succès (code $statusCode): " . $e->getMessage());
                    return [
                        'success' => true,
                        'message' => 'Opération réussie'
                    ];
                }
                
                // Sinon, c'est une erreur
                throw $e;
            }
            
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
            // Si le message d'erreur contient "empty response" et que c'est une méthode DELETE,
            // c'est probablement un succès avec un corps vide
            if ($method === 'DELETE' && strpos($e->getMessage(), 'empty response') !== false) {
                error_log("Réponse vide pour une suppression, considérée comme un succès");
                return [
                    'success' => true,
                    'message' => 'Suppression réussie'
                ];
            }
            
            // Toute autre erreur
            error_log("Exception générale: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
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
        
        // Si c'est une erreur de "réponse vide" et qu'elle concerne une opération de suppression,
        // on peut supposer que c'est un succès avec un corps vide
        if (strpos($message, 'empty response') !== false || strpos($message, 'Response body is empty') !== false) {
            return [
                'success' => true,
                'message' => 'Opération réussie (réponse vide)'
            ];
        }
        
        // Tentative de récupération des détails de l'erreur si disponible
        $details = '';
        if (method_exists($exception, 'getResponse')) {
            try {
                $response = $exception->getResponse();
                $statusCode = $response->getStatusCode();
                
                // Vérifier si c'est un code de succès pour DELETE (204 No Content est courant)
                if ($statusCode >= 200 && $statusCode < 300) {
                    return [
                        'success' => true,
                        'message' => 'Opération réussie'
                    ];
                }
                
                try {
                    $content = $response->getContent(false);
                    error_log("Réponse d'erreur (code $statusCode): $content");
                    
                    // S'assurer que le contenu n'est pas vide avant de tenter de le décoder
                    if (!empty($content)) {
                        $data = json_decode($content, true);
                        if (isset($data['message'])) {
                            $details = $data['message'];
                        }
                    }
                } catch (\Exception $contentEx) {
                    // Si on ne peut pas obtenir le contenu (par exemple, s'il est vide),
                    // mais que c'est un code de succès, on considère l'opération comme réussie
                    if ($statusCode >= 200 && $statusCode < 300) {
                        return [
                            'success' => true,
                            'message' => 'Opération réussie (pas de contenu)'
                        ];
                    }
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