// Import des styles
import './styles/app.css';
import './styles/form.css'; // Import de notre nouvelle feuille de style pour les formulaires

// Import de Bootstrap
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import d'Axios pour les requêtes API
import axios from 'axios';

// Rendre axios disponible globalement (optionnel)
window.axios = axios;

// Code spécifique à l'application
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser les tooltips Bootstrap
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Initialiser les popovers Bootstrap
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Fermeture automatique des alertes après 5 secondes
    const autoCloseAlerts = document.querySelectorAll('.alert:not(.alert-danger)');
    autoCloseAlerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
});